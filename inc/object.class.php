<?php


/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

 LICENSE

   This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
class PluginGenericobjectObject extends CommonDBTM {

   //Object type configuration
   private $type_infos = array ();

   //Internal field counter
   private $cpt = 0;


   function canCreate() {
      //return true;
      $type = strtolower(str_replace("PluginGenericobject", "", $this->type));
      return PluginGenericobjectProfile::haveRight($type, 'w');
   }

   function canView() {
      //return true;
      $type = strtolower(str_replace("PluginGenericobject", "", $this->type));
      return PluginGenericobjectProfile::haveRight($type, 'r');
   }
   
   function __construct($itemtype = 0) {
      if ($itemtype) {
         $this->setType($itemtype);
         $_SESSION["glpi_plugin_genericobject_itemtype"] = $itemtype;
      }
      else
         $this->setType($_SESSION["glpi_plugin_genericobject_itemtype"]);
   }

   function setType($itemtype) {
      $this->type = $itemtype;
      $this->type = PluginGenericobjectType::getTypeByName($itemtype);
      $this->table = PluginGenericobjectType::getTableNameByID($itemtype);
      $this->type_infos = PluginGenericobjectType::getObjectTypeConfiguration($itemtype);
      $this->entity_assign = $this->type_infos['use_entity'];
      $this->may_be_recursive = $this->type_infos['use_recursivity'];
      $this->dohistory = $this->type_infos['use_history'];
   }

   function defineTabs($options=array()) {
      global $LANG;
      $ong = array ();

      $ong[1] = $LANG['title'][26];

      if ($this->fields['id'] > 0) {

         if ($this->canUseDirectConnections() || $this->canUseNetworkPorts())
            $ong[3] = $LANG['title'][27];

         if ($this->canUseInfocoms()) {
            $ong[4] = $LANG['Menu'][26];
         }

         if ($this->canUseDocuments()) {
            $ong[5] = $LANG['Menu'][27];
         }

         if ($this->canUseTickets()) {
            $ong[6] = $LANG['title'][28];
         }

         $linked_types = PluginGenericobjectLink::plugin_genericobject_getLinksByType($this->type);
         if (!empty ($linked_types)) {
            $ong[7] = $LANG['setup'][620];
         }

         if ($this->type_infos["use_notes"] && haveRight("notes", "r")) {
            $ong[10] = $LANG['title'][37];
         }

         if ($this->canUseLoans()) {
            $ong[11] = $LANG['Menu'][17];
         }

         if ($this->canUseHistory())
            $ong[12] = $LANG['title'][38];

      }
      return $ong;
   }

   function canUseInfocoms() {
      return ($this->type_infos["use_infocoms"] 
               && (haveRight("contract", "r") || haveRight("infocom", "r")));
   }

   function canUseDocuments() {
      return ($this->type_infos["use_documents"] && haveRight("document", "r"));

   }

   function canUseTickets() {
      return ($this->type_infos["use_tickets"] && haveRight("show_all_ticket", "1"));
   }

   function canUseNotes() {
      return ($this->type_infos["use_notes"] && haveRight("notes", "r"));
   }

   function canUseLoans() {
      return ($this->type_infos["use_loans"] && haveRight("reservation_central", "r"));
   }

   function canUseHistory() {
      return ($this->type_infos["use_history"]);
   }

   function canUsePluginDataInjection() {
      return ($this->type_infos["use_plugin_datainjection"]);
   }

   function canUsePluginPDF() {
      return ($this->type_infos["use_plugin_pdf"]);
   }

   function canUsePluginOrder() {
      return ($this->type_infos["use_plugin_order"]);
   }

   function canUseNetworkPorts() {
      return ($this->type_infos["use_network_ports"]);
   }

   function canUseDirectConnections() {
      return ($this->type_infos["use_direct_connections"]);
   }

   function title($name) {
      displayTitle('', PluginGenericobjectObject::getLabel($name), 
                   PluginGenericobjectObject::getLabel($name));
   }

   function showForm($ID, $options=array(), $previsualisation = false) {
      global $LANG;

      if ($previsualisation) {
         $canedit = true;
         $this->getEmpty();
      } else {
         if ($ID > 0) {
            $this->check($ID, 'r');
         } else {
            // Create item 
            $this->check(-1, 'w');
            $use_cache = false;
            $this->getEmpty();
         }

         $this->showTabs($options);
         
         $canedit = $this->can($ID, 'w');
      }
      
      $this->fields['id'] = $ID;
      

      $this->showFormHeader($options);
      echo "<input type='hidden' name='itemtype' value='" . 
         strtolower(str_replace("PluginGenericobject", "", $this->type)) . "'>";

      if ($this->type_infos["use_entity"]) {
         echo "<input type='hidden' name='entities_id' value='" . 
            $this->fields["entities_id"] . "'>";
      }

      if (!$previsualisation) {
         echo "<div class='center' id='tabsbody'>";
      }
      else {
         echo "<div class='center'>";
      }

      echo "<table class='tab_cadre_fixe' >";


      foreach (PluginGenericobjectField::plugin_genericobject_getFieldsByType($this->type) as $field => $tmp) {
         $value = $this->fields[$field];
         $this->displayField($canedit, $field, $value);
      }
      $this->closeColumn();

      if (!$previsualisation)
         $this->displayActionButtons($ID, $_GET['withtemplate'], $canedit);

      echo "</table></div></form>";
      if (!$previsualisation) {
         echo "<div id='tabcontent'></div>";
         echo "<script type='text/javascript'>loadDefaultTab();</script>";
      }
   }

   function displayActionButtons($ID, $withtemplate, $canedit) {
      global $LANG;
      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2' colspan='4' align='center'>";

         if (empty ($ID) || $ID < 0 || $withtemplate == 2) {
            echo "<input type='submit' name='add' value=\"" . $LANG['buttons'][8] . 
                     "\" class='submit'>";
         } else {
            echo "<input type='hidden' name='id' value=\"$ID\">\n";
            echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . 
                     "\" class='submit'>";

            if (!$this->fields["is_deleted"]) {
               echo "&nbsp<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . 
                  "\" class='submit'>";
            } else {
               if ($this->type_infos["use_deleted"]) {
                  echo "&nbsp<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . 
                     "\" class='submit'>";
                  echo "&nbsp<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . 
                     "\" class='submit'>";
               }
            }
         }
         echo "</td>";
         echo "</tr>";
      }
   }

   function getAllTabs() {
      global $LANG;
      foreach (getAllDatasFromTable($this->table) as $ID => $value)
         $tabs[$value["itemtype"]] = $LANG["genericobject"][$value["name"]][1];

      return $tabs;
   }

   function displayField($canedit, $name, $value) {
      global $GENERICOBJECT_AVAILABLE_FIELDS, $GENERICOBJECT_BLACKLISTED_FIELDS;

      if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$name]) 
         && !in_array($name, $GENERICOBJECT_BLACKLISTED_FIELDS)) {

         $this->startColumn();
         echo $GENERICOBJECT_AVAILABLE_FIELDS[$name]['name'];
         $this->endColumn();
         $this->startColumn();
         switch ($GENERICOBJECT_AVAILABLE_FIELDS[$name]['input_type']) {
            case 'multitext' :
               if ($canedit) {
                  echo "<textarea cols='40' rows='4' name='" . $name . "'>" . $value . 
                     "</textarea>";
               }
               else {
                  echo $value;
               }
               break;
            case 'text' :
               if ($canedit) {
                  $table = PluginGenericobjectType::getTableByName($name);
                  autocompletionTextField($this, $name);
               } else {
                  echo $value;
               }
               break;
            case 'date' :
               if ($canedit) {
                  showDateFormItem($name, $value, false, true);
               }
               else {
                  echo convDate($value);
               }
               break;
            case 'integer' :
               if ($canedit) {
                  echo "<input type='text' name='".$name."' value=\"".formatNumber($value, true, 0)."\" size='6'>";
               } else
                  echo $value;
               break;
            case 'dropdown_global' :
               Dropdown::showGlobalSwitch($_SERVER['PHP_SELF'],'',$this->fields['id'],
                                          $this->fields['is_global'],2);
               
               break;
            case 'dropdown' :
               if (PluginGenericobjectType::plugin_genericobject_isDropdownTypeSpecific($name)) {
                  $type = strtolower(str_replace("PluginGenericobject", "", $this->type));
                  $device_name = PluginGenericobjectType::getNameByID($type);
                  $table = PluginGenericobjectType::getDropdownTableName($device_name, $name);
               } else
                  $table = $GENERICOBJECT_AVAILABLE_FIELDS[$name]['table'];

               if ($canedit) {
                  $entity_restrict = $this->fields["entities_id"];
                  switch ($table) {
                     default :
                        if (isset($device_name)) {
                           $object_name = "PluginGenericobject".ucfirst($device_name).ucfirst($name);
                        }
                        else $object_name = ucfirst($name);
                  
                        //dropdownValue($table, $name, $value, 1, $entity_restrict);
                        Dropdown::show($object_name, array('value' => $value, 'name' => $name,
                                                           'entity' => $entity_restrict));
                        break;
                     case 'glpi_users' :
                        User::dropdown(array('name'   => $name, 'value'  => $value, 
                                             'right'  => 'all', 'entity' => $entity_restrict));
                        break;   
                  }
                  
               } else {
                  echo getDropdownName($table, $value);
               }
               break;
            case 'dropdown_yesno' :
               if ($canedit) {
                  //dropdownYesNo($name, $value);
                  Alert::dropdownYesNo(array("name" => $name, 
                                                "value" => $value));
               }
               else {
                  echo getYesNo($value);
               }
               break;
         }
         $this->endColumn();
      }
   }

   /**
   * Add a new column
   **/
   function startColumn() {
      if ($this->cpt == 0) {
         echo "<tr class='tab_bg_1'>";
      }

      echo "<td>";
      $this->cpt++;
   }

   /**
   * End a column
   **/
   function endColumn() {
      echo "</td>";

      if ($this->cpt == 4) {
         echo "</tr>";
         $this->cpt = 0;
      }

   }

   /**
   * Close a column
   **/
   function closeColumn() {
      if ($this->cpt > 0) {
         while ($this->cpt < 4) {
            echo "<td></td>";
            $this->cpt++;
         }
         echo "</tr>";
      }
   }

   function prepareInputForAdd($input) {

      if (isset ($input["ID"]) && $input["ID"] > 0) {
         $input["_oldID"] = $input["ID"];
      }
      unset ($input['ID']);
      unset ($input['withtemplate']);

      return $input;
   }

   function post_addItem() {
      global $DB;
      // Manage add from template
      if (isset ($this->input["_oldID"])) {
         // ADD Infocoms
         $ic = new Infocom();
         if ($ic->getFromDBforDevice($this->type, $this->input["_oldID"])) {
            $ic->fields["items_id"] = $this->fields['id'];
            unset ($ic->fields["id"]);
            if (isset ($ic->fields["immo_number"])) {
               $ic->fields["immo_number"] = autoName($ic->fields["immo_number"], "immo_number", 1, 
                                                     'Infocom', $this->input['entities_id']);
            }
            if (empty ($ic->fields['use_date'])) {
               unset ($ic->fields['use_date']);
            }
            if (empty ($ic->fields['buy_date'])) {
               unset ($ic->fields['buy_date']);
            }
            $ic->addToDB();
         }

         // ADD Contract
         $query = "SELECT contracts_id 
                     FROM glpi_contracts_items 
                     WHERE items_id='" . $this->input["_oldID"] . "' 
                        AND itemtype='" . $this->type . "';";
         $result = $DB->query($query);
         if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetch_array($result))
               addDeviceContract($data["contracts_id"], $this->type, $this->input['newID']);
         }

         // ADD Documents
         $query = "SELECT documents_id 
                     FROM glpi_documents_items 
                     WHERE items_id='" . $this->input["_oldID"] . "' AND itemtype='" . 
                        $this->type . "';";
         $result = $DB->query($query);
         if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetch_array($result))
               addDeviceDocument($data["documents_id"], $this->type, $this->input['newID']);
         }
      }
   }

   function cleanDBonPurge() {
      global $DB, $CFG_GLPI;
      
      $ID = $this->fields['id'];

      //$job = new Job();
      $query = "SELECT * 
               FROM glpi_tickets 
               WHERE items_id = '".$this->fields['id']."'  AND itemtype='" . $this->type . "'";
      $result = $DB->query($query);

      if ($DB->numrows($result))
         while ($data = $DB->fetch_array($result)) {
            if ($CFG_GLPI["keep_tracking_on_delete"] == 1) {
               $query = "UPDATE glpi_tickets SET items_id = '0', itemtype='0' " .
                        "WHERE id='" . $data["id"] . "';";
               $DB->query($query);
            } /*else
               $job->delete(array (
                  "id" => $data["id"]
               ));*/
         }

      $query = "SELECT id 
               FROM `glpi_networkports` 
               WHERE items_id = '".$this->fields['id']."' AND itemtype = '" . $this->type . "'";
      $result = $DB->query($query);
      while ($data = $DB->fetch_array($result)) {
         $q = "DELETE FROM `glpi_networkports_networkports` " .
              "WHERE networkports_id_1 = '" . $data["id"] . "' " .
                  "OR   networkports_id_2 = '" . $data["id"] . "'";
         $DB->query($q);
      }


      $query2 = "DELETE FROM `glpi_networkports` " .
                "WHERE items_id = '$ID' AND itemtype = '" . $this->type . "'";
      $DB->query($query2);

      $query = "SELECT * FROM `glpi_computers_items` " .
               "WHERE itemtype='" . $this->type . "' AND items_id ='$ID'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetch_array($result)) {
               // Disconnect without auto actions
               Disconnect($data["id"], 1, false);
            }
         }
      }

      $query = "SELECT ID FROM `glpi_reservationsitems` " .
               "WHERE itemtype='" . $this->type . "' AND items_id='$ID'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) > 0) {
            $rr = new ReservationItem();
            $rr->delete(array (
               "id" => $DB->result($result, 0, "id")
            ));
         }
      }

      $query = "DELETE FROM `glpi_infocoms` " .
               "WHERE items_id = '$ID' AND itemtype='" . $this->type . "'";
      $DB->query($query);

      $query = "DELETE FROM `glpi_contracts_items` " .
               "WHERE items_id = '$ID' AND itemtype='" . $this->type . "'";
      $DB->query($query);

      $query = "DELETE FROM `glpi_documents_items` " .
               "WHERE (items_id = '$ID' AND itemtype='" . $this->type . "')";
      $DB->query($query);

   }
   
   /**
    * Display object preview form
    * @param type the object type
    */
   public static function plugin_genericobject_showPrevisualisationForm($type) {
      global $LANG;
      
      if (plugin_genericobject_haveTypeRight($type,'r'))
      {
         $name = PluginGenericobjectType::getNameByID($type);
         echo "<br><strong>" . $LANG['genericobject']['config'][8] . "</strong><br>";
      
         //$object = new $type();
         //$_SESSION["glpi_plugin_genericobject_itemtype"] = $type;
         $object = new PluginGenericobjectObject($type);
         //$object->setType($type, true);
         $object->showForm('', null, true);
      }
      else
         echo "<br><strong>" . $LANG['genericobject']['fields'][9] . "</strong><br>";
   }
   
   
   
   public static function plugin_genericobject_showDevice($target,$itemtype,$item_id) {
      global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE,$GENERICOBJECT_LINK_TYPES;
      
      $name = PluginGenericobjectType::getNameByID($itemtype);
      
      if (!haveRight($name,"r")) return false;
      //if (!haveTypeRight($name,"r")) return false;
      
      $rand=mt_rand();
      
      $commonitem = new PluginGenericobjectObject($itemtype);
      
      
      if ($commonitem->getFromDB($item_id)){
         $obj = $commonitem;
         
         $canedit=$obj->can($item_id,'w'); 

         $query = "SELECT DISTINCT itemtype 
               FROM `".PluginGenericobjectType::getLinkDeviceTableName($name)."` 
               WHERE source_id = '$item_id' 
               ORDER BY itemtype";
         
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         $i = 0;
         if (isMultiEntitiesMode()) {
            $colsup=1;
         }else {
            $colsup=0;
         }
         echo "<form method='post' name='link_type_form$rand' " .
              " id='link_type_form$rand'  action=\"$target\">";
      
         echo "<div align='center'><table class='tab_cadrehov'>";
         echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".
               $LANG['genericobject']['links'][2].":</th></tr><tr>";
         if ($canedit) {
            echo "<th>&nbsp;</th>";
         }
         echo "<th>".$LANG['common'][17]."</th>";
         echo "<th>".$LANG['common'][16]."</th>";
         if (isMultiEntitiesMode())
            echo "<th>".$LANG['entity'][0]."</th>";
         echo "<th>".$LANG['common'][19]."</th>";
         echo "<th>".$LANG['common'][20]."</th>";
         echo "</tr>";
      
         $ci=new CommonItem();
         while ($i < $number) {
            $type=$DB->result($result, $i, "itemtype");
            //if (haveTypeRight($type,"r")){
            if (haveRight($type,"r")){
               $column="name";
               if ($type==TRACKING_TYPE) $column="ID";
               if ($type==KNOWBASE_TYPE) $column="question";

               $query = "SELECT ".$LINK_ID_TABLE[$type].".*, ".
                           PluginGenericobjectType::getLinkDeviceTableName($name).".id AS IDD "
                       ." FROM `".PluginGenericobjectType::getLinkDeviceTableName($name)."`, `".
                           $LINK_ID_TABLE[$type]."`, `".$obj->table."`"
                       ." WHERE ".$LINK_ID_TABLE[$type].".id = ".
                     PluginGenericobjectType::getLinkDeviceTableName($name).".items_id 
                  AND ".PluginGenericobjectType::getLinkDeviceTableName($name).".itemtype='$type' 
                  AND ".PluginGenericobjectType::getLinkDeviceTableName($name).".source_id = '$item_id' ";
                  $query.=getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',
                           isset($CFG_GLPI["recursive_type"][$type])); 

                  if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
                     $query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
               }
               $query.=" ORDER BY ".$obj->table.".entities_id, ".$LINK_ID_TABLE[$type].".$column";

               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)){
                     $ci->setType($type);
                     initNavigateListItems($type,PluginGenericobjectObject::getLabel($name)." = ".
                        $obj->fields['name']);
                     while ($data=$DB->fetch_assoc($result_linked)){
                        addToNavigateListItems($type,$data["id"]);
                        $ID="";
                        if ($type==TRACKING_TYPE) $data["name"]=$LANG['job'][38]." ".$data["id"];
                        if ($type==KNOWBASE_TYPE) $data["name"]=$data["question"];
                        
                        if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["id"].")";
                        $item_name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type].
                           "?id=".$data["id"]."&itemtype=$type\">".$data["name"]."$ID</a>";
      
                        echo "<tr class='tab_bg_1'>";

                        if ($canedit){
                           echo "<td width='10'>";
                           $sel="";
                           if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                           echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                           echo "</td>";
                        }
                        echo "<td class='center'>".$ci->getType()."</td>";
                        
                        echo "<td class='center' ".(isset($data['is_deleted'])
                                                      && $data['is_deleted']?"class='tab_bg_2_2'":"").">".
                                                      $item_name."</td>";

                        if (isMultiEntitiesMode())
                           echo "<td class='center'>".getDropdownName("glpi_entities",
                                                                      $data['entities_id'])."</td>";
                        
                        echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-").
                           "</td>";
                        echo "<td class='center'>".(isset($data["otherserial"])? "".
                                                      $data["otherserial"]."" :"-")."</td>";
                        
                        echo "</tr>";
                     }
                  }
            }
            $i++;
         }
      
         if ($canedit)  {
            echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
      
            echo "<input type='hidden' name='source_id' value='$itemtype'>";
            dropdownAllItems("items_id",0,0,($obj->fields['recursive']?-1:$obj->fields['entities_id']),
                             PluginGenericobjectLink::plugin_genericobject_getLinksByType($itemtype));     
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='add_type_link' value=\"".$LANG['buttons'][8]."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;
            
            echo "<div class='center'>";
            echo "<table width='80%' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td>"; 
            echo "<td class='center'>"; 
            echo "<a onclick= \"if ( markCheckboxes('link_type_form$rand') ) return false;\" href='".
               $_SERVER['PHP_SELF']."?id=$item_id&amp;select=all'>".$LANG['buttons'][18]."</a></td>";
            
            echo "<td>/</td><td class='center'>"; 
            echo "<a onclick= \"if ( unMarkCheckboxes('link_type_form$rand') ) return false;\" href='".
               $_SERVER['PHP_SELF']."?id=$item_id&amp;select=none'>".$LANG['buttons'][19]."</a>";
            echo "</td>";
            echo "<td align='left' width='80%'>";
            echo "<input type='submit' name='delete_type_link' value=\"".$LANG['buttons'][6].
                    "\" class='submit'>";
            echo "</td>";
            echo "</table>";
         
            echo "</div>";

         }else{
      
            echo "</table></div>";
         }
         echo "</form>";
      }
   }
   
   /**
    * Reorder all fields for a type
    * @param itemtype the object type
    */
   public static function plugin_genericobject_reorderFields($itemtype)
   {
      global $DB;
      $query = "SELECT id FROM `glpi_plugin_genericobject_type_fields` " .
               "WHERE itemtype='$itemtype' ORDER BY rank ASC";
      $result = $DB->query($query);
      $i = 0;
      while ($datas = $DB->fetch_array($result))
      {
         $query = "UPDATE `glpi_plugin_genericobject_type_fields` SET rank=$i " .
                  "WHERE itemtype='$itemtype' AND id=".$datas["id"];
         $DB->query($query);
         $i++; 
      }  
   }
   
   
   /**
    * Change object field's order
    * @param field the field to move up/down
    * @param itemtype object item type
    * @param action up/down
    */
   public static function plugin_genericobject_changeFieldOrder($field,$itemtype,$action){
         global $DB;

         $sql ="SELECT id, rank " .
               "FROM glpi_plugin_genericobject_type_fields " .
               "WHERE itemtype='$itemtype' AND name='$field'";

         if ($result = $DB->query($sql)){
            if ($DB->numrows($result)==1){
               
               $current_rank=$DB->result($result,0,"rank");
               $ID = $DB->result($result,0,"ID");
               // Search rules to switch
               $sql2="";
               switch ($action){
                  case "up":
                     $sql2 ="SELECT id, rank FROM `glpi_plugin_genericobject_type_fields` " .
                            "WHERE itemtype='$itemtype' " .
                            "   AND rank < '$current_rank' ORDER BY rank DESC LIMIT 1";
                  break;
                  case "down":
                     $sql2 ="SELECT id, rank FROM `glpi_plugin_genericobject_type_fields` " .
                            "WHERE itemtype='$itemtype' " .
                            "   AND rank > '$current_rank' ORDER BY rank ASC LIMIT 1";
                  break;
                  default :
                     return false;
                  break;
               }
               
               if ($result2 = $DB->query($sql2)){
                  if ($DB->numrows($result2)==1){
                     list($other_ID,$new_rank)=$DB->fetch_array($result2);
                     $query="UPDATE `glpi_plugin_genericobject_type_fields` " .
                            "SET rank='$new_rank' WHERE id ='$ID'";
                     $query2="UPDATE `glpi_plugin_genericobject_type_fields` " .
                             "SET rank='$current_rank' WHERE id ='$other_ID'";
                     return ($DB->query($query)&&$DB->query($query2));
                  }
               }
            }
            return false;
         }
      }


   public static function plugin_genericobject_showTemplateByDeviceType($target, $itemtype, $entity, 
                                                                        $add=0) {
      global $LANG,$DB,$GENERICOBJECT_LINK_TYPES;
      $name = PluginGenericobjectType::getNameByID($itemtype);
      $commonitem = new PluginGenericobjectObject($itemtype);
      //$commonitem->setType($itemtype,true);
      $title = PluginGenericobjectObject::getLabel($name);
      $query = "SELECT * FROM `".$commonitem->getTable()."` " .
               "WHERE `is_template` = '1' AND `entities_id`='" . 
                  $_SESSION["glpiactive_entity"] . "' ORDER BY `template_name`";
      if ($result = $DB->query($query)) {

         echo "<div class='center'><table class='tab_cadre' width='50%'>";
         if ($add) {
            echo "<tr><th>" . $LANG['common'][7] . " - $title:</th></tr>";
         } else {
            echo "<tr><th colspan='2'>" . $LANG['common'][14] . " - $title:</th></tr>";
         }

         if ($add) {

            echo "<tr>";
            echo "<td align='center' class='tab_bg_1'>";
            echo "<a href=\"$target?itemtype=$itemtype&id=-1&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;" . 
               $LANG['common'][31] . "&nbsp;&nbsp;&nbsp;</a></td>";
            echo "</tr>";
         }
      
         while ($data = $DB->fetch_array($result)) {

            $templname = $data["template_name"];
            if ($_SESSION["glpiview_ID"]||empty($data["template_name"])){
                        $templname.= "(".$data["id"].")";
            }
            echo "<tr>";
            echo "<td align='center' class='tab_bg_1'>";
            
            if (haveTypeRight($itemtype, "w") && !$add) {
               echo "<a href=\"$target?itemtype=$itemtype&id=" . $data["id"] . 
                  "&amp;withtemplate=1\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";

               echo "<td align='center' class='tab_bg_2'>";
               echo "<strong><a href=\"$target?itemtype=$itemtype&id=" . $data["id"] . 
                  "&amp;purge=purge&amp;withtemplate=1\">" . $LANG['buttons'][6] . "</a></strong>";
               echo "</td>";
            } else {
               echo "<a href=\"$target?itemtype=$itemtype&id=" . $data["id"] . 
                  "&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
            }

            echo "</tr>";

         }

         //if (haveTypeRight($itemtype, "w") &&!$add) {
         if (haveRight($itemtype, "w") &&!$add) {
            echo "<tr>";
            echo "<td colspan='2' align='center' class='tab_bg_2'>";
            echo "<strong><a href=\"$target?itemtype=$itemtype&withtemplate=1\">" . 
               $LANG['common'][9] . "</a></strong>";
            echo "</td>";
            echo "</tr>";
         }

         echo "</table></div>";
      }
      
   }


   
   static function getLabel($name) {
      global $LANG;
      if (isset ($LANG['genericobject'][$name][1])) {
         return $LANG['genericobject'][$name][1];
      } else {
         return $name;
      }
   }
   
   static function uninstall() {
      global $DB;
      
      $tables = array ("glpi_displaypreferences", "glpi_documents_items", "glpi_bookmarks",
                       "glpi_logs");
      foreach ($tables as $table) {
         $query = "DELETE FROM `$table` WHERE `itemtype`='".__CLASS__."'";
         $DB->query($query);
      }

   }
}
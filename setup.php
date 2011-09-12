<?php
/*
 * @version $Id: HEADER 7762 2009-01-06 18:30:32Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined("GENERICOBJECT_DIR")) {
   define("GENERICOBJECT_DIR",GLPI_ROOT . "/plugins/genericobject");
}
if (!defined("GENERICOBJECT_FRONT_PATH")) {
   define("GENERICOBJECT_FRONT_PATH", GENERICOBJECT_DIR."/front");
}
if (!defined("GENERICOBJECT_AJAX_PATH")) {
   define("GENERICOBJECT_AJAX_PATH", GENERICOBJECT_DIR . "/ajax");
}
if (!defined("GENERICOBJECT_CLASS_PATH")) {
   define("GENERICOBJECT_CLASS_PATH", GENERICOBJECT_DIR . "/inc");
}
if (!defined("GENERICOBJECT_LOCALES_PATH")) {
   define("GENERICOBJECT_LOCALES_PATH", GENERICOBJECT_DIR . "/locales");
}

// Init the hooks of the plugins -Needed
function plugin_init_genericobject() {
   global $PLUGIN_HOOKS, $LANG, $CFG_GLPI, $GO_BLACKLIST_FIELDS, $GO_FIELDS, 
          $GENERICOBJECT_PDF_TYPES;
          
   $GO_BLACKLIST_FIELDS = array ("itemtype", "table", "is_deleted", "id", "entities_id", 
                                 "is_recursive", "is_template", "notepad", "template_name", 
                                 "is_helpdesk_visible", "comment", "name", "date_mod");

   $GENERICOBJECT_PDF_TYPES = array ();
   $plugin = new Plugin();

   if ($plugin->isInstalled("genericobject") && $plugin->isActivated("genericobject")) {  
      /*
      //Include all fields constants files
      foreach (glob(GLPI_ROOT . '/plugins/genericobject/fields/constants/*.php') as $file) {
         include_once ($file);
      }*/
      
      //Load genericobject default constants
      include_once (GLPI_ROOT . "/plugins/genericobject/inc/field.constant.php");
      
      //Include user constants, that must be accessible for all itemtypes
      if (file_exists(GLPI_ROOT . "/plugins/genericobject/inc/myconstant.php")) {
         include_once (GLPI_ROOT . "/plugins/genericobject/inc/myconstant.php");
      }
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      /* load changeprofile function */
      $PLUGIN_HOOKS['change_profile']['genericobject'] = array('PluginGenericobjectProfile', 
                                                               'changeProfile');

      // Display a menu entry ?
      $PLUGIN_HOOKS['menu_entry']['genericobject']              = true;
      $PLUGIN_HOOKS['submenu_entry']['genericobject']['config'] = 'front/type.php';

      // Config page
      if (haveRight('config', 'w')) {
         $PLUGIN_HOOKS['config_page']['genericobject']                     = 'front/type.php';
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['add']['type']    = 'front/type.form.php';
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['search']['type'] = 'front/type.php';
      }

      $PLUGIN_HOOKS['assign_to_ticket']['genericobject']   = true;
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      // Onglets management
      $PLUGIN_HOOKS['headings']['genericobject']         = 'plugin_get_headings_genericobject';
      $PLUGIN_HOOKS['headings_action']['genericobject']  = 'plugin_headings_actions_genericobject';
      $PLUGIN_HOOKS['post_init']['genericobject']        = 'plugin_post_init_genericobject';
      $PLUGIN_HOOKS['plugin_datainjection_populate']['genericobject'] = "plugin_datainjection_populate_genericobject";
   }
}

function plugin_post_init_genericobject() {
   foreach (PluginGenericobjectType::getTypes() as $id => $objecttype) {
      call_user_func(array($objecttype['itemtype'], 'registerType'));
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_genericobject() {
   global $LANG;
   return array ('name'           => $LANG["genericobject"]["title"][1], 
                 'version'        => '2.0',
                 'author'         => 'Alexandre Delaunay & Walid Nouh',
                 'homepage'       => 'https://forge.indepnet.net/projects/show/genericobject',
                 'minGlpiVersion' => '0.80');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_genericobject_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.80','lt') || version_compare(GLPI_VERSION,'0.81','ge')) {
      echo "This plugin requires GLPI 0.80";
   }
   return true;
}

// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_genericobject_check_config($verbose = false) {
   global $LANG;

   if (true) { // Your configuration check
      return true;
   }
   if ($verbose) {
      echo $LANG['plugins'][2];
   }
   return false;
}

function plugin_genericobject_haveTypeRight($itemtype, $right) {
   switch ($itemtype) {
      case 'PluginGenericobjectType' :
         return haveRight("config", $right);
      default :
         return haveRight($itemtype, $right);
   }

}
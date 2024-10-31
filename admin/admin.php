<?php
if(!defined('ABSPATH')){
    exit; //Exit if accessed directly
}
//Defines
global $wpdb;
define('PLM_TBL_LICENSE_KEYS', $wpdb->prefix . "plm_manager_keys");
define('PLM_TBL_REGISTER_DOMAIN', $wpdb->prefix . "plm_manager_domains");
define('PLM_MANAGEMENT_PERMISSION', 'manage_options');

require_once( PLM_PATH.'admin/classes/class-manager.php');
require_once( PLM_PATH.'admin/classes/class-woointeg.php');
//Includes plugin files
require_once( PLM_PATH.'admin/includes/db-generate.php');
include_once( PLM_PATH.'admin/classes/class-script.php');
include_once( PLM_PATH.'admin/classes/class-log-debug.php');
include_once( PLM_PATH.'admin/classes/class-utility.php');
include_once( PLM_PATH.'admin/classes/class-listener.php');
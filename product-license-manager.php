<?php
/*
Plugin Name: Product License Manager
Version: 4.0.1
Plugin URI: http://www.wplicensemanager.com/
Author: Product License Manager
Author URI: http://www.wplicensemanager.com
Text Domain: product-license-manager
Domain Path: /languages
Description: Woocommerce Product License Manager help you to create your own license  server to  helps the software developer and programmer to  secure and  track who is using thier software, system, plugin and theme. It has built in API to track status, deactivate, activate and block  users who is using your web application.
*/

if(!defined('ABSPATH')){
    exit; //Exit if accessed directly
}

define('PLM_VERSION', "4.0.1");
define('PLM_TEXT_DOMAIN', 'product-license-manager');
define('PLM_DB_VERSION', '4.0.1');
define('PLM_URL', plugins_url('',__FILE__));
define('PLM_PATH', plugin_dir_path(__FILE__));
define('PLM_FILE', __FILE__ );
define('PLM_HOME_URL', home_url());
define('PLM_BASENAME', plugin_basename( __FILE__ ) );

add_action( 'plugins_loaded', 'wplm_woo_load_textdomain' );

function wplm_woo_load_textdomain() {
	load_plugin_textdomain( PLM_TEXT_DOMAIN, false, '/product-license-manager/languages' );
}

require_once( PLM_PATH.'admin/admin.php');
<?php
if(!defined('ABSPATH')){
    exit; //Exit if accessed directly
}
class PLM_Manager{

	private $update_message_hook 	= '';
	private $update_notice_hook 	= '';
	private $action_links_hook 		= '';

	function __construct(){
		$this->set_hookname();
		add_action('admin_menu', array( $this, 'dashboard_menu_callback' ) );
		add_action( 'wp_ajax_activate_license', array( $this, 'activate_license_callback' ) );
		add_action( 'wp_ajax_deactivate_license', array( $this, 'deactivate_license_callback' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta_callback' ), 10, 2 );
		add_filter( $this->action_links_hook, array( $this, 'action_links_calllback' ), 10, 2 );

		//** Removed registered Domain from License
		add_action( 'wp_ajax_remove_registered_domain', array( $this, 'remove_admin_registered_domain_callback' ) );

		//** Load Plugin text domain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	function dashboard_menu_callback(){
		add_menu_page(
			__( "Product License Manager", PLM_TEXT_DOMAIN ), 
			__( "Product License Manager", PLM_TEXT_DOMAIN ),
			'manage_options',
			'plmmanager',
			array( $this, 'plm_manage_licenses' ), 
			'dashicons-welcome-view-site'
		);
	    add_submenu_page(
	    	'plmmanager', 
	    	__( "Licenses", PLM_TEXT_DOMAIN ), 
	    	__( "Licenses", PLM_TEXT_DOMAIN ), 
	    	'manage_options',
	    	'plmmanager', 
	    	array( $this, 'plm_manage_licenses' )
	    );
	    add_submenu_page(
	    	'plmmanager', 
	    	__( "Add/Edit Licenses", PLM_TEXT_DOMAIN ), 
	    	__( "Add/Edit Licenses", PLM_TEXT_DOMAIN ), 
	    	'manage_options',
	    	'plmlicense', 
	    	array( $this, 'licenses_menu' )
	    );
	    add_submenu_page(
	    	'plmmanager', 
	    	__( "Settings", PLM_TEXT_DOMAIN ),
	    	__( "Settings", PLM_TEXT_DOMAIN ),
	    	'manage_options',
	    	'plmsettings', 
	    	array( $this, 'general_settings_menu' )
	    );
	    add_submenu_page(
	    	'plmmanager', 
	    	__( "API Help", PLM_TEXT_DOMAIN ),
	    	__( "API Help", PLM_TEXT_DOMAIN ),
	    	'manage_options',
	    	'plmintegration-helper', 
	    	array( $this, 'integration_helper_menu' )
	    );
	}
	function plm_manage_licenses() {
	    require_once( PLM_PATH .'admin/templates/manage-license.tpl.php');
	}
	function licenses_menu() {
	    global $wpdb;
		$license_table 	= PLM_TBL_LICENSE_KEYS;
		$domain_table 	= PLM_TBL_REGISTER_DOMAIN;
	    //initialise some variables
	    $id = '';
	    $license_key = '';
	    $max_domains = 1;
	    $license_status = '';
	    $first_name = '';
	    $last_name = '';
	    $email = '';
	    $company_name = '';
		
		$product_name = '';
	    $txn_id = '';
	    $reset_count = '';
	    $created_date = '';
	    $renewed_date = '';
	    $expiry_date = '';
	    $current_date = (date ("Y-m-d"));
	    $plm_options = get_option('plm_options');
	    
	    
	    //If product is being edited, grab current product info
	    if (isset($_GET['edit_record'])) {
	        $errors = '';
	        $id = $_GET['edit_record'];
	        $sql_prep = $wpdb->prepare("SELECT * FROM $license_table WHERE id = %s", $id);
	        $record = $wpdb->get_row($sql_prep, OBJECT);
	        $license_key = $record->license_key;
	        $max_domains = $record->max_allowed_domains;
	        $license_status = $record->license_status;
	        $first_name = $record->first_name;
	        $last_name = $record->last_name;
	        $email = $record->email;
			
			$product_name = $record->product_name;
	        $company_name = $record->company_name;
	        $txn_id = $record->txn_id;
	        $reset_count = $record->manual_reset_count;
	        $created_date = $record->date_created;
	        $renewed_date = $record->date_renewed;
	        $expiry_date = $record->date_expiry;
	    }
	    
	    
	    if (isset($_POST['save_record'])) {
	        
	        //TODO - do some validation
	        $license_key 	= sanitize_text_field($_POST['license_key']);
	        $max_domains 	= sanitize_text_field($_POST['max_allowed_domains']);
	        $license_status = sanitize_text_field($_POST['license_status']);
	        $first_name 	= sanitize_text_field($_POST['first_name']);
	        $last_name 		= sanitize_text_field($_POST['last_name']);
	        $email 			= sanitize_email($_POST['email']);
			
			$product_name 	= sanitize_text_field($_POST['product_name']);
	        $company_name 	= sanitize_text_field($_POST['company_name']);
	        $txn_id 		= sanitize_text_field($_POST['txn_id']);
	        $reset_count 	= sanitize_text_field($_POST['manual_reset_count']);
	        $created_date 	= sanitize_text_field($_POST['date_created']);
	        $renewed_date 	= sanitize_text_field($_POST['date_renewed']);
	        $expiry_date 	= sanitize_text_field($_POST['date_expiry']);
	        
	        if(empty($created_date)){
	            $created_date = $current_date;
	        }
	        if(empty($renewed_date)){
	            $renewed_date = $current_date;
	        }
	        if(empty($expiry_date)){
	            $expiry_date = $current_date;
	        }
	        
	        //Save the entry to the database
	        $fields = array();
	        $fields['license_key'] 			= $license_key;
	        $fields['max_allowed_domains'] 	= $max_domains;
	        $fields['license_status'] 		= $license_status;
	        $fields['first_name'] 			= $first_name;
	        $fields['last_name'] 			= $last_name;
	        $fields['email'] 				= $email;
			
			$fields['product_name'] 		= $product_name;
	        $fields['company_name'] 		= $company_name;
	        $fields['txn_id'] 				= $txn_id;
	        $fields['manual_reset_count'] 	= $reset_count;
	        $fields['date_created'] 		= $created_date;
	        $fields['date_renewed'] 		= $renewed_date;
	        $fields['date_expiry'] 			= $expiry_date;
	        $id = isset($_POST['edit_record']) ? sanitize_text_field ( $_POST['edit_record'] ) : '' ;
	        if (empty($id)) {//Insert into database
	            $result = $wpdb->insert( $license_table, $fields);
	            $id = $wpdb->insert_id;
	            if($result === false){
	                $errors .= __('Record could not be inserted into the database!',PLM_TEXT_DOMAIN);
	            }
	        } else { //Update record
	            $where = array('id'=>$id);
	            $updated = $wpdb->update($license_table, $fields, $where);
	            if($updated === false){
	                //TODO - log error
	                $errors .= __('Update of the license key table failed!',PLM_TEXT_DOMAIN);
	            }
	        }
	        if(empty($errors)){
	            $message = "Record successfully saved!";
	            echo '<div id="message" class="updated fade"><p>';
	            echo $message;
	            echo '</p></div>';
	        }else{
	            echo '<div id="message" class="error">' . $errors . '</div>';        
	        }
	    }
	    require_once( PLM_PATH .'admin/templates/license.tpl.php');
	}
	function general_settings_menu() {
		global $wpdb;
		$license_args = array(
			'post_type' => 'product',
			'tax_query' => array( 
				array(
			        'taxonomy' => 'product_type',
			        'field' => 'slug',
			        'terms' => 'license_renewal',
			     )
			)	
		);
		require_once( PLM_PATH .'admin/templates/settings.tpl.php');
	}
	function integration_helper_menu() {
	    $options 					= get_option('plm_options');
		$creation_secret_key 		= $options['plm_creation_secret'];
		$secret_verification_key 	= $options['plm_verification_secret'];
		$host_url 					= PLM_HOME_URL;
		require_once( PLM_PATH .'admin/templates/integration.tpl.php');
	}	
	function set_hookname(){
		$this->action_links_hook = "plugin_action_links_".PLM_BASENAME;
	}
	function action_links_calllback( $links ){
		$links[] = '<a href="'. admin_url('admin.php?page=plmsettings') .'">'.__('Settings', PLM_TEXT_DOMAIN ).'</a>';
		return $links;
	}
	function plugin_row_meta_callback( $links, $file ) {
		if ( strpos( $file, 'product-license-manager.php' ) !== false ) {
			$new_links = array(
            	'help' 	 => '<a href="'.admin_url('admin.php?page=plmintegration-helper').'" target="_blank">Help</a>'
			);		
			$links = array_merge( $links, $new_links );
		}		
		return $links;
	}
	function remove_admin_registered_domain_callback(){
		global $wpdb;
		$result 		= NULL;
		$regDomainID 	= sanitize_text_field ( $_POST['regDomainID'] );
		$result  	= $wpdb->delete( PLM_TBL_REGISTER_DOMAIN, array( 'id' => $regDomainID ) );
		echo $result;
		die();
	}
	function load_textdomain() {
		load_plugin_textdomain( PLM_TEXT_DOMAIN, false, '/product-license-manager/languages' );
	}
}
new PLM_Manager;
<?php
/*
 * This class listens for API query and executes the API requests
 * Available API Actions
 * 1) plm_create_new
 * 2) plm_activate
 * 3) plm_deactivate
 * 4) plm_check
 */
class PLM_API_Listener {
    function __construct() {
        if (isset($_REQUEST['plm_action']) && isset($_REQUEST['secret_key'])) {
            //This is an API query for the license manager. Handle the query.
            $this->creation_api_listener();
            $this->activation_api_listener();
            $this->deactivation_api_listener();
            $this->check_api_listener();
        }
    }
    function creation_api_listener() {
        if ( isset( $_REQUEST['plm_action'] ) && sanitize_text_field($_REQUEST['plm_action'] ) == 'plm_create_new') {
            //Handle the licene creation API query
            global $PLM_Log_Debugger;
            $options = get_option('plm_options');
            $license_key_prefix = $options['plm_prefix'];
            PLM_API_Utility::verify_secret_key_for_creation(); //Verify the secret key first.
            $PLM_Log_Debugger->log_debug("API - license creation (plm_create_new) request received.");
            
            //Action hook
            do_action('PLM_API_Listener_create_new');            
            $fields = array();
            if (isset($_REQUEST['license_key']) && !empty($_REQUEST['license_key'])){
                $fields['license_key'] = sanitize_text_field ($_REQUEST['license_key']);//Use the key you pass via the request
            }else{
                $fields['license_key'] = uniqid($license_key_prefix);//Use random generated key
            }
            $fields['license_status']   = 'active';
            $fields['first_name']       = sanitize_text_field ($_REQUEST['first_name']);
            $fields['last_name']        = sanitize_text_field ($_REQUEST['last_name']);
            $fields['email']            = sanitize_email ($_REQUEST['email']);
            $fields['company_name']     = sanitize_text_field ($_REQUEST['company_name']);
            $fields['txn_id']           = sanitize_text_field ($_REQUEST['txn_id']);
            if (empty($_REQUEST['max_allowed_domains'])) {
                $fields['max_allowed_domains'] = $options['default_max_domains'];
            } else {
                $fields['max_allowed_domains'] = sanitize_text_field ($_REQUEST['max_allowed_domains']);
            }
            $fields['date_created']     = isset($_REQUEST['date_created'])?sanitize_text_field ($_REQUEST['date_created']):date("Y-m-d");
            $fields['date_expiry']      = isset($_REQUEST['date_expiry'])?sanitize_text_field ($_REQUEST['date_expiry']):'';
            $fields['product_name']     = isset($_REQUEST['product_name'])?sanitize_text_field ($_REQUEST['product_name']):'';
            global $wpdb;
            $tbl_name = PLM_TBL_LICENSE_KEYS;
            $result = $wpdb->insert( $tbl_name, $fields );
            if ($result === false) {
                //error inserting
                $args = (array('result' => 'error', 'message' => 'License creation failed'));
                PLM_API_Utility::output_api_response($args);
            } else {
                $args = (array('result' => 'success', 'message' => 'License successfully created', 'key' => $fields['license_key']));
                PLM_API_Utility::output_api_response($args);
            }
        }
    }
    /*
     * Query Parameters
     * 1) plm_action = plm_create_new
     * 2) secret_key
     * 3) license_key
     * 4) registered_domain (optional)
     */
    function activation_api_listener() {
        if ( isset($_REQUEST['plm_action'] ) && trim( $_REQUEST['plm_action'] ) == 'plm_activate') {
            //Handle the license activation API query
            global $PLM_Log_Debugger;
            PLM_API_Utility::verify_secret_key(); //Verify the secret key first.
            $PLM_Log_Debugger->log_debug("API - license activation (plm_activate) request received.");
            //Action hook
            do_action('PLM_API_Listener_activate');             
            $fields                         = array();
            $fields['license_key']          = trim(sanitize_text_field (isset($_REQUEST['license_key'])) ? $_REQUEST['license_key'] : '');
            $fields['registered_domain']    = trim(sanitize_text_field ($_REQUEST['registered_domain']));
            $fields['scheme_domain']        = ( isset( $_REQUEST['scheme_domain'] ) )? trim(sanitize_text_field ($_REQUEST['scheme_domain'])) : '';
            $fields['item_reference']       = ( isset( $_REQUEST['item_reference'] ) ) ? trim(sanitize_text_field ($_REQUEST['item_reference'])) : '' ;
            
            $registered_domain = '';
            if( !empty( $fields['scheme_domain'] ) ){
                $registered_domain = $fields['scheme_domain'].'://'.$fields['registered_domain'];
            }else{
                $registered_domain = $fields['registered_domain'];
            }
            $fields['registered_domain'] = $registered_domain;
            $PLM_Log_Debugger->log_debug("License key: " . $fields['license_key'] . " Domain: " . $registered_domain );
            global $wpdb;
            $tbl_name   = PLM_TBL_LICENSE_KEYS;
            $reg_table  = PLM_TBL_REGISTER_DOMAIN;
            $key        = $fields['license_key'];
            $product    = isset( $_REQUEST['product'] ) ? str_replace("*"," ", trim(sanitize_text_field ($_REQUEST['product'])) ) : '' ;

            $sql_prep1      = apply_filters( 'PLM_API_Listener_retrieve_license', $this->PLM_API_Listener_retrieve_license_callback( $key, $product ), $key, $product );
            $invalid_message = apply_filters( 'PLM_API_Listener_invalid_message', $this->PLM_API_Listener_invalid_message_callback( $key, $product ), $key, $product );
            
            $retLic         = $wpdb->get_row($sql_prep1, OBJECT);
            $sql_prep2      = $wpdb->prepare("SELECT * FROM $reg_table WHERE license_key = %s", $key);
            $reg_domains    = $wpdb->get_results($sql_prep2, OBJECT);
            if ($retLic) {
                if ($retLic->license_status == 'blocked') {
                    $args = (array('result' => 'error', 'message' => 'Your License key is blocked'));
                    PLM_API_Utility::output_api_response($args);
                } elseif ($retLic->license_status == 'expired') {
                    $args = (array('result' => 'error', 'message' => 'Your License key has expired'));
                    PLM_API_Utility::output_api_response($args);
                } elseif ($retLic->license_status == 'pending') {
                    $args = (array('result' => 'error', 'message' => 'Your License key is pending'));
                    PLM_API_Utility::output_api_response($args);
                }
                if (count($reg_domains) < floor($retLic->max_allowed_domains)) {
                    
                    foreach ($reg_domains as $reg_domain) {

                        if (isset($_REQUEST['migrate_from']) && (trim( sanitize_text_field ( $_REQUEST['migrate_from'] ) ) == $reg_domain->registered_domain)) {

                            $wpdb->update($reg_table, array('registered_domain' => $registered_domain ), array('registered_domain' => trim(sanitize_text_field ($_REQUEST['migrate_from']))));
                            $args = (array('result' => 'success', 'message' => 'Registered domain has been updated'));
                            PLM_API_Utility::output_api_response($args);
                        }
                        if ( $registered_domain == $reg_domain->registered_domain) {
                            $args = (array('result' => 'error', 'message' => 'License key already in use on ' . $reg_domain->registered_domain));
                            PLM_API_Utility::output_api_response($args);
                        }
                    }

                    unset( $fields['scheme_domain'] );

                    $fields['license_key_id'] = $retLic->id;
                    $wpdb->insert($reg_table, $fields);
                    
                    $PLM_Log_Debugger->log_debug("Updating license key status to active.");
                    $data = array('license_status' => 'active');
                    $where = array('id' => $retLic->id);
                    $updated = $wpdb->update($tbl_name, $data, $where);
                    
                    $args = (array('result' => 'success', 'message' => 'License key activated' ));
                    PLM_API_Utility::output_api_response($args);
                } else {
                    $args = (array('result' => 'error', 'message' => 'Reached maximum allowable domains', 'plm_checker' => 'true'));
                    PLM_API_Utility::output_api_response($args);
                }
            } else {
                $args = (array('result' => 'error', 'message' => $invalid_message, 'plm_checker' => 'false'));
                PLM_API_Utility::output_api_response($args);
            }
            
        }
    }
    function deactivation_api_listener() {
        if (isset($_REQUEST['plm_action']) && trim( sanitize_text_field ( $_REQUEST['plm_action'] ) ) == 'plm_deactivate') {
            //Handle the license deactivation API query
            global $PLM_Log_Debugger;
            PLM_API_Utility::verify_secret_key(); //Verify the secret key first.
            $PLM_Log_Debugger->log_debug("API - license deactivation (plm_deactivate) request received.");
            
            //Action hook
            do_action('PLM_API_Listener_deactivate');            
            if (empty($_REQUEST['registered_domain'])) {
                $args = (array('result' => 'error', 'message' => 'Registered domain information is missing'));
                PLM_API_Utility::output_api_response($args);
            }

            $registered_domain    = trim(sanitize_text_field ($_REQUEST['registered_domain'])); //gethostbyaddr($_SERVER['REMOTE_ADDR']);
            $scheme_domain        = ( isset( $_REQUEST['scheme_domain'] ) ) ? trim(sanitize_text_field ($_REQUEST['scheme_domain'])) : '';
            if( !empty( $scheme_domain ) ){
                $registered_domain = $scheme_domain.'://'.$registered_domain;
            }else{
                $registered_domain = $registered_domain;
            }

            //$registered_domain = trim(sanitize_text_field ($_REQUEST['registered_domain']));
            $license_key = trim(sanitize_text_field ($_REQUEST['license_key']));
            $PLM_Log_Debugger->log_debug("License key: " . $license_key . " Domain: " . $registered_domain);
            global $wpdb;
            $registered_dom_table = PLM_TBL_REGISTER_DOMAIN;
            $sql_prep = $wpdb->prepare("DELETE FROM $registered_dom_table WHERE license_key=%s AND registered_domain=%s", $license_key, $registered_domain);
            $delete = $wpdb->query($sql_prep);
            if ($delete === false) {
                $PLM_Log_Debugger->log_debug("Error - failed to delete the registered domain from the database.");
            } else if ($delete == 0) {
                $args = (array('result' => 'error', 'message' => 'The license key on this domain is already inactive'));
                PLM_API_Utility::output_api_response($args);
            } else {
                $args = (array('result' => 'success', 'message' => 'The license key has been deactivated for this domain'));
                PLM_API_Utility::output_api_response($args);
            }
        }
    }
    function check_api_listener() {
        if (isset($_REQUEST['plm_action']) && trim( sanitize_text_field ( $_REQUEST['plm_action'] ) ) == 'plm_check') {
            //Handle the license check API query
            global $PLM_Log_Debugger;
            PLM_API_Utility::verify_secret_key(); //Verify the secret key first.
            $PLM_Log_Debugger->log_debug("API - license check (plm_check) request received.");
            
            $fields = array();
            $fields['license_key'] = trim(sanitize_text_field ($_REQUEST['license_key']));
            $PLM_Log_Debugger->log_debug("License key: " . $fields['license_key']);
            //Action hook
            do_action('PLM_API_Listener_check');
            
            global $wpdb;
            $tbl_name = PLM_TBL_LICENSE_KEYS;
            $reg_table = PLM_TBL_REGISTER_DOMAIN;
            $key = $fields['license_key'];
            $sql_prep1 = $wpdb->prepare("SELECT * FROM $tbl_name WHERE license_key = %s", $key);
            $retLic = $wpdb->get_row($sql_prep1, OBJECT);
            $sql_prep2 = $wpdb->prepare("SELECT * FROM $reg_table WHERE license_key = %s", $key);
            $reg_domains = $wpdb->get_results($sql_prep2, OBJECT);
            if ($retLic) {//A license key exists
                $args = (array(
                    'result' => 'success', 
                    'message' => 'License key details retrieved.', 
                    'status' => $retLic->license_status, 
                    'max_allowed_domains' => $retLic->max_allowed_domains,
                    'email' => $retLic->email,
                    'registered_domains' => $reg_domains,
                    'date_created' => $retLic->date_created,
                    'date_renewed' => $retLic->date_renewed,
                    'date_expiry' => $retLic->date_expiry,
                ));
                //Output the license details
                PLM_API_Utility::output_api_response($args);
            } else {
                $args = (array('result' => 'error', 'message' => 'Invalid license key'));
                PLM_API_Utility::output_api_response($args);
            }            
        }
    }
    function PLM_API_Listener_retrieve_license_callback( $key, $product ){
        global $wpdb;
        $result = $wpdb->prepare("SELECT * FROM ".PLM_TBL_LICENSE_KEYS." WHERE license_key = %s", $key );
        return $result;
    }
    function PLM_API_Listener_invalid_message_callback( $key, $product ){
        return 'Invalid license key '.$key;
    }
}
add_action( 'wp',function(){
    new PLM_API_Listener();
},10);
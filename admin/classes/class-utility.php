<?php
class PLM_API_Utility {
    static function output_api_response($args) {
        //Log to debug file (if enabled)
        global $PLM_Log_Debugger;
        $PLM_Log_Debugger->log_debug('API Response - Result: ' . $args['result'] . ' Message: ' . $args['message']);
        //Send response
        echo json_encode($args);
        exit(0);
    }
    static function verify_secret_key() {
        $plm_options            = get_option('plm_options');
        $right_secret_key       = $plm_options['plm_verification_secret'];
        $received_secret_key    = sanitize_text_field ($_REQUEST['secret_key']);
        if ($received_secret_key != $right_secret_key) {
            $args = (array('result' => 'error', 'message' => 'Verification API secret key is invalid'));
            PLM_API_Utility::output_api_response($args);
        }
    }
    static function verify_secret_key_for_creation() {
        $plm_options        = get_option('plm_options');
        $right_secret_key   = $plm_options['plm_creation_secret'];
        $received_secret_key = sanitize_text_field ($_REQUEST['secret_key']);
        if ($received_secret_key != $right_secret_key) {
            $args = (array('result' => 'error', 'message' => 'License Creation API secret key is invalid'));
            PLM_API_Utility::output_api_response($args);
        }
    }    
    static function insert_license_data_internal($fields) {
        global $wpdb;
        $tbl_name = PLM_TBL_LICENSE_KEYS;
        $fields = array_filter($fields);//Remove any null values.
        $result = $wpdb->insert($tbl_name, $fields);
    }
}
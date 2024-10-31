<?php
function plm_generate_database(){
  //***** Installer *****
  global $wpdb;
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');	

  //***Installer variables***
  $charset_collate  = '';

  if (!empty($wpdb->charset)){
      $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
  }else{
      $charset_collate = "DEFAULT CHARSET=utf8";
  }
  if (!empty($wpdb->collate)){
      $charset_collate .= " COLLATE $wpdb->collate";
  }
          
  $key_table_sql = "CREATE TABLE " . PLM_TBL_LICENSE_KEYS . " (
        id int(12) NOT NULL auto_increment,
        license_key varchar(255) NOT NULL,
        max_allowed_domains int(12) NOT NULL,
        license_status ENUM('pending', 'active', 'blocked', 'expired') NOT NULL DEFAULT 'pending',         
        first_name varchar(32) NOT NULL default '',
        last_name varchar(32) NOT NULL default '',
        email varchar(64) NOT NULL,
        product_name varchar(100) NOT NULL default '',
        company_name varchar(100) NOT NULL default '',
        txn_id varchar(64) NOT NULL default '',
        manual_reset_count varchar(128) NOT NULL default '',
        date_created date NOT NULL DEFAULT '0000-00-00',
        date_renewed date NOT NULL DEFAULT '0000-00-00',
        date_expiry date NOT NULL DEFAULT '0000-00-00',
        PRIMARY KEY (id)
        )" . $charset_collate . ";";
  dbDelta($key_table_sql);

  $domain_tbl_sql = "CREATE TABLE " .PLM_TBL_REGISTER_DOMAIN. " (
        id INT NOT NULL AUTO_INCREMENT ,
        license_key_id INT NOT NULL ,
        license_key varchar(255) NOT NULL ,
        registered_domain text NOT NULL ,
        item_reference varchar(255) NOT NULL,
        PRIMARY KEY ( id )
        )" . $charset_collate . ";";
  dbDelta($domain_tbl_sql);

  update_option("plm_db_version", PLM_DB_VERSION );

  // Add default options
  $options = array(
      'plm_creation_secret'      => uniqid('', true),
      'plm_prefix'               => '',
      'default_max_domains'       => '1',
      'plm_verification_secret'  => uniqid('', true),
      'enable_debug'              => '',
  );
  add_option('plm_options', $options);

}
register_activation_hook( PLM_FILE, 'plm_generate_database' );
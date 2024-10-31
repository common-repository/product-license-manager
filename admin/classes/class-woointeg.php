<?php
if(!defined('ABSPATH')){
    exit; //Exit if accessed directly
}
class PLM_Woo_Integration{
	function __construct(){

		// Plugin Dependencies
		register_activation_hook( PLM_FILE , array( $this, 'check_woocommrece') );
		add_action('admin_init', array( $this, 'on_init_check_woocommrece' ) );

		//**  Woocommerce Email notification
		add_action('woocommerce_email_before_order_table', array( $this,  'woo_completed_order' ) );

		//** Woocommerce Cart Integration		
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'wplm_add_license_item_data' ), 1, 10 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'wplm_get_cart_license_items_from_session' ), 1, 3 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'wplm_add_license_manager_metadata_session' ), 1, 3 );
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'wplm_add_license_manager_metadata_to_order_item_meta' ), 1, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'wplm_update_license_manager_product_price' ), 1, 1 );

		// Woocommerce Product and Filters
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'license_woo_product_data_tab' ), 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'license_woo_product_data_fields' ) );


		//** Save product custom data
		add_action( 'woocommerce_process_product_meta', array( $this, 'license_woo_save_product_data' ) );
		
	}

	// Woocommerce integration callbacks method
	function woo_completed_order( $order ){
	    global $wpdb, $flag, $order_qty, $product_serial_key, $serial_key;
	    $options 			= get_option('plm_options');
	    $product_renewal 	= get_option('product_renewal');
	    if ( array_key_exists( "plm_creation_secret", $options ) ){
	        $wplm_creation_secret = $options['plm_creation_secret'];
	    }else{
	        $wplm_creation_secret = '';
	    }
	    /*** Mandatory data ***/
	    // Post URL 
	    $siteURL   = get_bloginfo( 'url' ).'/';
	    // The Secret key 
	    $secretKey = $wplm_creation_secret;
	    $order     = wc_get_order($order);
	    $orderID   = trim( str_replace('#', '', $order->get_order_number() ) );
	    $order_status = $order->get_status();
	    $items     = $order->get_items();
	    $firstname = $order->get_billing_first_name();
	    $lastname  = $order->get_billing_last_name();
	    $email     = $order->get_billing_email();
	    $orderID   = $order->get_id();

	    $data      = array();
	    $prod_name = array();
	    $prod_id   = array();
	    $license_renew = array(); 
	    $num_of_domain = array();
	    $purchase_products = array();
	    $product_serial_key = array();
	    $array_counter = 0 ;

	    if( $order_status == 'completed' ){

	        foreach ( $items as $item ) :

	        	if( !empty( $product_renewal ) && $item['product_id'] == $product_renewal ){
	        		$item_meta_data = $item->get_meta_data();
	        		$update_result = $wpdb->update( 
						PLM_TBL_LICENSE_KEYS, 
						array( 
							'date_renewed' 		=> date('Y-m-d'),
							'date_expiry' 		=> date('Y-m-d', strtotime( apply_filters( 'wplm_after_complete_renewal_date', '+1 year' ) ) ),
							'license_status' 	=> 'active'
						), 
						array( 'license_key' => $item_meta_data[1]->value ), 
						array( '%s', '%s', '%s' ), 
						array( '%s' ) 
					);
					if( $update_result ){
						$license_renew[] = array( 
							'license_key' => $item_meta_data[1]->value, 
							'expiry_date' => date('Y-m-d', strtotime( apply_filters( 'wplm_after_complete_renewal_date', '+1 year' ) ) ) 
						);
					}
		        	continue;
	        	}
	        	
	            $item_id              = $item['product_id'];
	            $license_product_data = get_post_meta( $item_id, '_license_product_data',  TRUE );
	            $excluded_file        = maybe_unserialize( get_post_meta( $item_id, '_exclude_file', true ) );

	            $num_domain           = apply_filters( 'wplm_after_complete_order_domain_number', 1, $item_id );
	            $expiry_date 		  = apply_filters( 'wplm_after_complete_order_expiry_date', '', $item_id );
	            //** Check if the product is Integrated with license Manager
	            if( $license_product_data == 'no' ){ 
	                continue;
	            }
	            $downloads  = $item->get_item_downloads();
	            if( !empty( $downloads ) ){ 
	                foreach ( $downloads as $item_download ) {

	                    //**Check if the dowloadable file is excluded in generating license
	                    if( in_array( $item_download['name'] , array_map( 'trim', array_column( $excluded_file, 'excluded_file' ) ) ) ) { 
	                        continue;
	                    }

	                    $data['secret_key']          = $secretKey;
	                    $data['plm_action']      	 = 'plm_create_new';
	                    $data['first_name']          = $firstname;
	                    $data['last_name']           = $lastname;
	                    $data['email']               = $email;
	                    $data['license_status']      = 'active';
	                    $data['max_allowed_domains'] = $num_domain;
	                    $data['product_name']        = $item_download['name'];
	                    $data['txn_id']              = $orderID;
	                    $data['date_expiry']      	 = $expiry_date;
	                    // Optional Data
	                    // send data to post URL 
	                    $ch                          = curl_init($siteURL);
	                    curl_setopt($ch, CURLOPT_POST, true);
	                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
	                    curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
	                    $returnValue        = curl_exec($ch);
	                    $exploded           = explode(',', $returnValue);
	                    $exploded           = explode(':', $exploded[2]);
	                    $serial_key         = preg_replace( "/[^A-Z0-9a-z\w ]/u", '', $exploded[1] );
	                    $product_serial_key[] = $item_download['name'] . ': ' . $serial_key;
	                }
	            }
	        endforeach;
	        if( !empty( $license_renew ) ){
	        	echo '<h2>'.__( 'License Renewal(s)', PLM_TEXT_DOMAIN).'</h2>';
	        	foreach ( $license_renew as $license ) {
	        		echo "<strong>".__('License:', PLM_TEXT_DOMAIN)." ".$license['license_key']." ".__(' Expiry Date:', PLM_TEXT_DOMAIN)." ".$license['expiry_date']."</strong><br />";
	        	}
	        }
	        if( !empty( $product_serial_key ) ){
	        	echo '<h2>'.__( 'License key(s)', PLM_TEXT_DOMAIN).'</h2>';
	            foreach ($product_serial_key as $product_details ) {
	                echo "<strong>$product_details</strong><br />";
	            }
	            echo '<br />';
	        }             
	    }  
	} 
	/*
	*  Add woocommerce Cart meta data from License Manager
	*/
	function wplm_add_license_item_data($cart_item_data, $product_id) {
	    global $woocommerce;
	    $new_value = array();
	    if( !empty( get_option( 'product_renewal' ) ) && $product_id == get_option( 'product_renewal' )  ){
	            $new_value['_license_key'] = $_REQUEST['license'];
	            $new_value['_license_product'] = $_REQUEST['prd'];
	    }    
	    if(empty($cart_item_data)) {
	        return $new_value;
	    } else {
	        return array_merge($cart_item_data, $new_value);
	    }
	}
	/*
	*   Get woocommerce Cart meta data from License Manager from Session
	*/
	function wplm_get_cart_license_items_from_session($item,$values,$key) {
	    if (array_key_exists( '_license_key', $values ) ) {
	        $item['_license_key'] = $values['_license_key'];
	        $item['_license_product'] = $values['_license_product'];
	    }
	    return $item;
	}
	/*  
	*   Display License Manager meta data in Product cart
	*/
	function wplm_add_license_manager_metadata_session($product_name, $values, $cart_item_key ) {
	    if( !empty( get_option( 'product_renewal' ) )  ){
	        if( get_option( 'product_renewal' ) == $values['product_id'] ){
	            $product_name = __('License Renewal', PLM_TEXT_DOMAIN) . '<br />'.__('Product Name:', PLM_TEXT_DOMAIN).' '.$values['_license_product'].'<br />'. __('License Key:', PLM_TEXT_DOMAIN).' '.$values['_license_key'];
	            
	        }
	    }
	    return $product_name;
	}
	/*
	*   Display License Manager meta data in checkout
	*/
	function wplm_add_license_manager_metadata_to_order_item_meta($item_id, $values) {
	    global $woocommerce,$wpdb;
	    wc_add_order_item_meta( $item_id, __('Product Name', PLM_TEXT_DOMAIN), $values['_license_product'] );
	    wc_add_order_item_meta( $item_id, __('License Key', PLM_TEXT_DOMAIN), $values['_license_key'] );
	}
	/*
	*   Customize Product price from License renewal product price
	*/
	function wplm_update_license_manager_product_price( $cart_object ) {
	    foreach ( $cart_object->cart_contents as $cart_item_key => $value ) {  
	        if( $value['product_id'] == get_option( 'product_renewal' ) && !empty( get_option( 'product_renewal' ) ) ){
	            $_license_renew = maybe_unserialize( get_post_meta( $value['product_id'], '_license_renew', TRUE ) );
	            $license_product = array_map( 'strtolower', array_column($_license_renew, 'product') );
	            $found_key = array_search( strtolower( trim( $value['_license_product'] ) ), array_map( 'trim', $license_product ) );
	            if( $found_key !== FALSE ){
	                $value['data']->set_price( $_license_renew[$found_key]['price'] );
	            }
	        }    
	    }
	}
	
	// Produdct Hooks and filters
	function license_woo_product_data_tab( $product_data_tabs ) {
	    $product_data_tabs['wordpress-license-manager'] = array(
	        'label'     => __( 'License Manager', PLM_TEXT_DOMAIN ),
	        'target'    => 'wplm_license_manager_product_data',
	        'class'     => 'license_manager_options license_manager_tab show_if_variable show_if_simple'
	    );
	    return $product_data_tabs;
	}
	function license_woo_product_data_fields() {
	    global $woocommerce, $post;
	    $_product_attributes =  maybe_unserialize( get_post_meta( $post->ID, '_product_attributes',  TRUE ) );
	    $attr_option         = array( '' => 'Select one' );
	    if( !empty( $_product_attributes) ){
	        foreach ($_product_attributes as $key => $value) {
	            $attr_option[$key] = $key;
	        }
	    }
	    ?>
	    <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
	    <div id="wplm_license_manager_product_data" class="panel woocommerce_options_panel">
	        <h2><?php _e('Note: License Manager can be use only for the Downloadable product.'); ?></h2>
	        <div class="options_group">           
	            <?php
	            woocommerce_wp_checkbox( array( 
	                'id'            => '_license_product_data', 
	                'label'         => __( 'Integrate with License Manager?', PLM_TEXT_DOMAIN ),
	                'description'   => __( 'After product Order Complete, Email notification with display license key for each product variation.', PLM_TEXT_DOMAIN ),
	            ) );
	            ?>
	        </div>
	        <div class="options_group excluded_file_group" style="padding: 9px;">
	            <h4><?php _e('Exclude Downloadable files in generating License'); ?></h4>
	            <?php $excluded_file = maybe_unserialize( get_post_meta( $post->ID, '_exclude_file', true ) ); ?>
	            <table id="excluded-file-table" style="width:100%;"> 
	                <thead>
	                    <tr>
	                        <th><?php _e( 'Exclude Downloadable files', PLM_TEXT_DOMAIN ); ?></th>
	                        <th><?php _e( 'Delete', PLM_TEXT_DOMAIN ); ?></th>
	                    </tr>
	                </thead>
	                <tbody data-repeater-list="_exclude_file">
	                    <?php if( !empty( $excluded_file  ) ): ?>
	                        <?php foreach ( $excluded_file as $filename ): ?>
	                            <tr data-repeater-item>
	                                <td><input type="text" name="excluded_file" value="<?php echo $filename['excluded_file']; ?>" /></td>
	                                <td><button type="button" class="button button-secondary" data-repeater-delete ><span class="dashicons dashicons-trash"></span></button></td>
	                            </tr>
	                        <?php endforeach; ?>
	                    <?php else: ?>
	                        <tr data-repeater-item>
	                            <td><input type="text" name="excluded_file" /></td>
	                            <td style="text-align: center;"><button type="button" class="button button-secondary" data-repeater-delete ><span class="dashicons dashicons-trash"></span></button></td>
	                        </tr>
	                    <?php endif; ?>           
	                </tbody>
	                <tfoot>
	                    <tr>
	                        <td colspan="2">
	                            <button type="button" class="button primary" data-repeater-create ><?php _e( 'Add Filename', PLM_TEXT_DOMAIN ); ?></button>
	                        </td>
	                    </tr>
	                </tfoot>
	            </table>
	        </div>
	    </div>
	    <?php
	}
	
	function license_woo_save_product_data( $post_id ){
	    $_license_product_data = isset( $_POST['_license_product_data'] ) ? 'yes' : 'no';
	    update_post_meta( $post_id, '_license_product_data', $_license_product_data );

	    if( isset( $_POST['_exclude_file'] ) ){
	        $excluded_file = $_POST['_exclude_file'];
	        update_post_meta( $post_id, '_exclude_file', serialize( $excluded_file ) );
	    }else{
	        update_post_meta( $post_id, '_exclude_file', serialize( array() ) );
	    }
	}
	// Check Woocommerce if installed
	//** Plugin dependency
	static function check_woocommrece() {		
		if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$send_error_message = __( 'This plugin requires <strong><a href="https://wordpress.org/plugins/woocommerce/">Woocommerce</a></strong> plugin to be active!', PLM_TEXT_DOMAIN );
			die($send_error_message);
		}
	}
	Static function on_init_check_woocommrece(){
		if( !class_exists( 'WooCommerce' ) ){
			deactivate_plugins( ABSPATH.'wp-content\plugins\product-license-manager\product-license-manager.php' );
			return false;
		}
	}
}
new PLM_Woo_Integration;
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Product License Manager API Help', PLM_TEXT_DOMAIN ); ?></h1>
	<a href="admin.php?page=plmmanager" class="page-title-action"><?php _e( 'Manage Licenses', PLM_TEXT_DOMAIN ); ?></a>
	<div id="poststuff">
		<div id="post-body">
			<div id="plmhelp" class="three-fourths first">  
				<div class="postbox">
					<h3 class="hndle"><label for="title"><?php _e('Product License Manager API', PLM_TEXT_DOMAIN ); ?></label></h3>
					<div class="inside">
						<p><strong><?php _e('API Host URL:', PLM_TEXT_DOMAIN ); ?></strong> <?php echo $host_url; ?></p>
						<p><strong><?php _e('Secret Key for License Verification Requests:', PLM_TEXT_DOMAIN ); ?></strong> <?php echo $secret_verification_key; ?></p>
						<p><strong><?php _e('Secret Key for License:', PLM_TEXT_DOMAIN ); ?> Creation</strong> <?php echo $creation_secret_key ; ?></p>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle"><?php _e('Sample Product License Helper plugin code', PLM_TEXT_DOMAIN ); ?></h3>
					<div class="inside">
						<p class="description"><?php _e('Below is a sample plugin that activate/deactivate license via the API from remote site.', PLM_TEXT_DOMAIN ); ?></p>
						<div style="background-color: #f1f1f1;padding: 12px;color: #0073aa;">
						<code style="background-color: transparent;">
							&lt;?php <br/>
							/** <br/>
							 * Plugin Name:       My License Helper <br/>
							 * Plugin URI:        <?php echo $host_url; ?> <br/>
							 * Description:       My Lincese Helper Description <br/>
							 * Version:           1.0.0 <br/>
							 * Author:            mysite <br/>
							 * Author URI:        <?php echo $host_url; ?> <br/>
							 * License:           GPL-2.0+ <br/>
							 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt <br/>
							 * Text Domain:       my-license-helper <br/>
							 * Domain Path:       /languages <br/>
							 */ <br/>

							// If this file is called directly, abort. <br/>
							if ( ! defined( 'WPINC' ) ) { <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;die; <br/>
							} <br/>
							define('MY_VERIFICATION_KEY', '<?php echo $secret_verification_key; ?>' ); <br/>
							define('MY_HOST_SERVER', '<?php echo $host_url; ?>'); <br/>
							<br/>
							add_action('admin_menu', 'my_license_helper_menu' ); <br/>
							function my_license_helper_menu(){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;add_menu_page( <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;__( "My License Helper", 'my-license-helper' ), <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;__( "My License Helper", 'my-license-helper' ), <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'manage_options', <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'my-license-helper', <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'my_license_helper_callback' <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;); <br/>
							} <br/>
							<br/>
							function my_license_helper_callback(){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;div class="wrap"&gt;';<br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;h1 class="wp-heading-inline"&gt;My License Helper&lt;/h1&gt;'; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if( isset( $_POST['submit'] ) && sanitize_text_field ( $_POST['action'] ) == "activate" ){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;activate_license_callback(); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}elseif( isset( $_POST['submit'] ) && sanitize_text_field ( $_POST['action'] ) == "deactivate" ){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;deactivate_license_callback(); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;} <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;?&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;form method="post"&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;label for="my-license"&gt;My License&lt;/label&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;input id="my-license" type="text" name="my-license" value="" required="required"&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;select name="action" required="required"&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option value=""&gt;Select Action&lt;/option&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option value="activate"&gt;Activate&lt;/option&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;option value="deactivate"&gt;Deactivate&lt;/option&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/select&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;input type="submit" name="submit" value="Submit License"&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/form&gt; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;?php <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;/div&gt;'; <br/>
							} <br/>
							<br/>
							function activate_license_callback(){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$license_key = $license_key = ( isset( $_POST['my-license'] ) ) ? sanitize_text_field ( $_POST['my-license']) : '' ; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$domain   	 = $_SERVER['SERVER_NAME']; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$api_params = array( <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'plm_action' 		=> 'plm_activate', <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'secret_key' 		=> MY_VERIFICATION_KEY, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'license_key' 		=> $license_key, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'registered_domain' => $domain, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$response   = wp_remote_get( add_query_arg( $api_params, MY_HOST_SERVER ), array( <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'timeout' => 20, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'sslverify' => false <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)); <br/>
							<br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( is_wp_error( $response ) ){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$license_data = array( <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'result' => 'error', <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'message' => "Unexpected Error! The query returned with an error." <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}else{ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$license_data = json_decode( wp_remote_retrieve_body( $response ) ); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;} <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo 'activate_license_callback'; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;pre&gt;'; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;print_r( $license_data ); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;/pre&gt;'; <br/>
							} <br/>
							function deactivate_license_callback(){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$license_key = ( isset( $_POST['my-license'] ) ) ? sanitize_text_field ( $_POST['my-license']) : '' ; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$domain   	 = $_SERVER['SERVER_NAME']; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$api_params = array( <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'plm_action' 		=> 'plm_deactivate', <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'secret_key' 		=> MY_VERIFICATION_KEY, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'license_key' 		=> $license_key, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'registered_domain' => $domain, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$response   = wp_remote_get( add_query_arg( $api_params, MY_HOST_SERVER ), array( <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'timeout' => 20, <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'sslverify' => false <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if ( is_wp_error( $response ) ){ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$license_data = array( <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'result' => 'error', <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'message' => "Unexpected Error! The query returned with an error." <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}else{ <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$license_data = json_decode( wp_remote_retrieve_body( $response ) ); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;} <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo 'deactivate_license_callback'; <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;pre&gt;'; <br/>  
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;print_r( $license_data ); <br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;/pre&gt;'; <br/>
							} <br/>
						</code>
						</div>
					</div>
				</div>
			</div><!-- #plm-help -->
			<div id="plm-ads" class="one-fourth">
				<?php require_once(PLM_PATH.'admin/templates/ads.tpl.php'); ?>
			</div><!-- #plm-ads -->
		</div>
	</div>
</div>
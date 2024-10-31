<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Product License Manager Settings', PLM_TEXT_DOMAIN ); ?></h1>
	<a href="admin.php?page=plm-manager" class="page-title-action"><?php _e( 'Manage Licenses', PLM_TEXT_DOMAIN ); ?></a>
	<div id="poststuff">
		<div id="post-body">
			<div id="plm-setting-form" class="three-fourths first">
			<?php
				if (isset($_REQUEST['reset-log'])){
			        global $PLM_Log_Debugger;
			        $PLM_Log_Debugger->reset_log_file("log.txt");
			        $PLM_Log_Debugger->reset_log_file("log-cron-job.txt");
			        echo '<div id="message" class="updated fade"><p>'. __( 'Debug log files have been reset!', PLM_TEXT_DOMAIN ) .'</p></div>';
			    }
			    if (isset($_POST['save_plm_settings'])) {
			        if (!is_numeric($_POST["default_max_domains"])) {//Set it to one by default if incorrect value is entered
			            $_POST["default_max_domains"] = '1';
			        }
			        $options = array(
			            'plm_creation_secret'       		=> sanitize_text_field( $_POST["plm_creation_secret"] ),
			            'plm_prefix'                		=> sanitize_text_field( $_POST["plm_prefix"] ),
			            'default_max_domains'       		=> sanitize_text_field( $_POST["default_max_domains"] ),
			            'plm_verification_secret'   		=> sanitize_text_field( $_POST["plm_verification_secret"] ),
			            'enable_debug'              		=> ( sanitize_text_field($_POST['enable_debug'])!=null ) ? '1':'',
			        );
			        update_option('plm_options', $options);
			        
			        echo '<div id="message" class="updated fade"><p>';        
			        _e( 'Options Updated!', PLM_TEXT_DOMAIN );
			        echo '</p></div>';
			    }
			    $options = get_option('plm_options');
			    $secret_key = $options['plm_creation_secret'];
			    if (empty($secret_key)) {
			        $secret_key = uniqid('', true);
			    }
			    $secret_verification_key = $options['plm_verification_secret'];
			    if (empty($secret_verification_key)) {
			        $secret_verification_key = uniqid('', true);
			    }
			    ?>
			    <form method="post" action="">
			        <div class="postbox">
			            <h3 class="hndle"><label for="title"><?php _e( 'General License Manager Settings', PLM_TEXT_DOMAIN ); ?></label></h3>
			            <div class="inside">
			                <table class="form-table">
			                    <tr valign="top">
			                        <th scope="row"><?php _e( 'Secret Key for License Creation', PLM_TEXT_DOMAIN ); ?></th>
			                        <td><input type="text" name="plm_creation_secret" value="<?php echo $secret_key; ?>" size="40" />
			                            <br /><span class="description"><?php _e( 'This secret key will be used to authenticate any license creation request. You can change it with something random.', PLM_TEXT_DOMAIN ); ?></span></td>
			                    </tr>
			                    <tr valign="top">
			                        <th scope="row"><?php _e( 'Secret Key for License Verification Requests', PLM_TEXT_DOMAIN ); ?></th>
			                        <td><input type="text" name="plm_verification_secret" value="<?php echo $secret_verification_key; ?>" size="40" />
			                            <br /><span class="description"><?php _e( 'This secret key will be used to authenticate any license verification request from customer\'s site. Important! Do not change this value once your customers start to use your product(s)!', PLM_TEXT_DOMAIN ); ?></span></td>
			                    </tr>
			                    <tr valign="top">
			                        <th scope="row"><?php _e( 'License Key Prefix', PLM_TEXT_DOMAIN ); ?></th>
			                        <td><input type="text" name="plm_prefix" value="<?php echo $options['plm_prefix']; ?>" size="40" />
			                            <br /><span class="description"><?php _e( 'You can optionaly specify a prefix for the license keys. This prefix will be added to the uniquely generated license keys.', PLM_TEXT_DOMAIN ); ?></span></td>
			                    </tr>
			                    <tr valign="top">
			                        <th scope="row"><?php _e( 'Maximum Allowed Domains', PLM_TEXT_DOMAIN ); ?></th>
			                        <td><input type="text" name="default_max_domains" value="<?php echo $options['default_max_domains']; ?>" size="6" />
			                            <br /><span class="description"><?php _e( 'Maximum number of domains which each license is valid for (default value).', PLM_TEXT_DOMAIN ); ?></span></td>
			                    </tr>
			                </table>
			            </div>
			        </div>
			        <div class="postbox">
			            <h3 class="hndle"><label for="title"><?php _e( 'Debugging and Testing Settings', PLM_TEXT_DOMAIN ); ?></label></h3>
			            <div class="inside">
			                <table class="form-table">
			                    <tr valign="top">
			                        <th scope="row"><?php _e( 'Enable Debug Logging', PLM_TEXT_DOMAIN ); ?></th>
			                        <td><input name="enable_debug" type="checkbox"<?php if ($options['enable_debug'] != '') echo ' checked="checked"'; ?> value="1"/>                            
			                            <p class="description"><?php _e( 'If checked, debug output will be written to log files (keep it disabled unless you are troubleshooting).', PLM_TEXT_DOMAIN ); ?></p>                            
			                            <br /><?php _e( '- View debug log file by clicking', PLM_TEXT_DOMAIN ); ?> <a href="<?php echo PLM_URL. '/logs/log.txt'; ?>" target="_blank"><?php _e( 'here', PLM_TEXT_DOMAIN ); ?></a>.
			                            <br /><?php _e( '- Reset debug log file by clicking', PLM_TEXT_DOMAIN ); ?> <a href="admin.php?page=plm-settings&reset-log=1" target="_blank"><?php _e( 'here', PLM_TEXT_DOMAIN ); ?></a>.
			                        </td>
			                    </tr>
			                </table>
			            </div>
			        </div>
			        <div class="submit">
			            <input type="submit" class="button-primary" name="save_plm_settings" value=" <?php _e('Save Settings', PLM_TEXT_DOMAIN ); ?>" />
			        </div>
			    </form>
			</div><!-- #plm-setting-form -->
			<div id="plm-ads" class="one-fourth">
				<?php require_once(PLM_PATH.'admin/templates/ads.tpl.php'); ?>
			</div><!-- #plm-ads -->
		</div>
	</div>
</div>
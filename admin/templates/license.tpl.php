<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Add/Edit Licenses', PLM_TEXT_DOMAIN ); ?></h1>
	<a href="admin.php?page=plmmanager" class="page-title-action"><?php _e( 'Manage Licenses', PLM_TEXT_DOMAIN ); ?></a>
	<div id="poststuff">
		<div id="post-body">
			<div class="postbox">
		        <h3 class="hndle"><label for="title"><?php _e( 'License Details', PLM_TEXT_DOMAIN ); ?></label></h3>
		        <div class="inside">
		            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		                <table class="form-table">
		                    <?php
		                    if ($id != '') {
		                        echo '<input name="edit_record" type="hidden" value="' . $id . '" />';
		                    } else {
		                        if(!isset($editing_record)){//Create an empty object
		                            $editing_record = new stdClass();
		                        }
		                        //Auto generate unique key
		                        $license_key_prefix = $plm_options['plm_prefix'];
		                        if (!empty($license_key_prefix)) {
		                            $license_key = uniqid($license_key_prefix);
		                        } else {
		                            $license_key = uniqid();
		                        }
		                    }
		                    ?>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'License Key', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="license_key" type="text" id="license_key" value="<?php echo $license_key; ?>" size="30" />
		                            <br/><span class="description"><?php _e( 'The unique license key. When adding a new record it automatically generates a unique key in this field for you. You can change this value to customize the key if you like.', PLM_TEXT_DOMAIN ); ?></span></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Maximum Allowed Domains', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="max_allowed_domains" type="text" id="max_allowed_domains" value="<?php echo $max_domains; ?>" size="5" /><br/><span class="description"><?php _e( 'Number of domains in which this license can be used.', PLM_TEXT_DOMAIN ); ?></span></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'License Status', PLM_TEXT_DOMAIN ); ?></th>
		                        <td>
		                            <select name="license_status">    
		                                <option value="pending" <?php if ($license_status == 'pending') echo 'selected="selected"'; ?> ><?php _e( 'Pending', PLM_TEXT_DOMAIN ); ?></option>
		                                <option value="active" <?php if ($license_status == 'active') echo 'selected="selected"'; ?> ><?php _e( 'Active', PLM_TEXT_DOMAIN ); ?></option>
		                                <option value="blocked" <?php if ($license_status == 'blocked') echo 'selected="selected"'; ?> ><?php _e( 'Blocked', PLM_TEXT_DOMAIN ); ?></option>
		                                <option value="expired" <?php if ($license_status == 'expired') echo 'selected="selected"'; ?> ><?php _e( 'Expired', PLM_TEXT_DOMAIN ); ?></option>
		                            </select>
		                        </td></tr>
		                    <?php
		                    if ($id != '') {
		                        $sql_prep = $wpdb->prepare("SELECT * FROM $domain_table WHERE `license_key_id` = %s", $id);
		                        $reg_domains = $wpdb->get_results($sql_prep, OBJECT);
		                        ?>
		                        <tr valign="top">
		                            <th scope="row"><?php _e( 'Registered Domains', PLM_TEXT_DOMAIN ); ?></th>
		                            <td><?php
		                                if (count($reg_domains) > 0) {
		                                    ?>
		                                    <div id="registered-domain-wrapper" >
		                                        <table cellpadding="0" cellspacing="0" style="width:100%;">
		                                            <?php
		                                            $count = 0;
		                                            foreach ($reg_domains as $reg_domain) {
		                                                ?>
		                                                <tr <?php echo ($count % 2) ? 'class="alternate"' : ''; ?>>
		                                                    <td height="5"><?php echo $reg_domain->registered_domain; ?></td> 
		                                                    <td height="5"><span class="del" id=<?php echo $reg_domain->id ?>><span class="dashicons dashicons-trash"></span></span></td>
		                                                </tr>
		                                                <?php
		                                                $count++;
		                                            }
		                                            ?>
		                                        </table>         
		                                    </div>
		                                    <?php
		                                } else {
		                                    _e( 'Not Registered Yet.', PLM_TEXT_DOMAIN );
		                                }
		                                ?>
		                            </td>
		                        </tr>
		                    <?php } ?>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'First Name', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="first_name" type="text" id="first_name" value="<?php echo $first_name; ?>" size="20" /></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Last Name', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="last_name" type="text" id="last_name" value="<?php echo $last_name; ?>" size="20" /></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Email Address', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="email" type="text" id="email" value="<?php echo $email; ?>" size="30" /></td>
		                    </tr>
							<tr valign="top">
		                        <th scope="row"><?php _e( 'Product Name', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="product_name" type="text" id="product_name" value="<?php echo $product_name; ?>" size="30" /></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Company Name', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="company_name" type="text" id="company_name" value="<?php echo $company_name; ?>" size="30" /></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Unique Transaction ID', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="txn_id" type="text" id="txn_id" value="<?php echo $txn_id; ?>" size="30" /><br/><span class="description"><?php _e( 'The unique transaction ID associated with this license key', PLM_TEXT_DOMAIN ); ?></span></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Manual Reset Count', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="manual_reset_count" type="text" id="manual_reset_count" value="<?php echo $reset_count; ?>" size="6" />
		                            <br/><span class="description"><?php _e( 'The number of times this license has been manually reset by the admin (use it if you want to keep track of it). It can be helpful for the admin to keep track of manual reset counts.', PLM_TEXT_DOMAIN ); ?></span></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Date Created', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="date_created" type="text" id="date_created" class="plm_pick_date" value="<?php echo $created_date; ?>" size="10" /></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Date Renewed', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="date_renewed" type="text" id="date_renewed" class="plm_pick_date" value="<?php echo $renewed_date; ?>" size="10" /></td>
		                    </tr>
		                    <tr valign="top">
		                        <th scope="row"><?php _e( 'Date of Expiry', PLM_TEXT_DOMAIN ); ?></th>
		                        <td><input name="date_expiry" type="text" id="date_expiry" class="plm_pick_date" value="<?php echo $expiry_date; ?>" size="10" /></td>
		                    </tr>
		                </table>
		                <div class="submit">
		                    <input type="submit" class="button-primary" name="save_record" value="<?php _e( 'Save License', PLM_TEXT_DOMAIN ); ?>" />
		                </div>
		            </form>
		        </div>
		    </div>
	    </div>
	</div>
</div>
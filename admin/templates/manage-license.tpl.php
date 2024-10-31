<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Manage Licenses', PLM_TEXT_DOMAIN ); ?></h1> 
	<a href="admin.php?page=plmlicense" class="page-title-action"><?php _e( 'Add License', PLM_TEXT_DOMAIN ); ?></a>
	<div id="poststuff">
		<div id="post-body">
			<div class="postbox">
			    <h3 class="hndle"><label for="title"><?php _e( 'License Search', PLM_TEXT_DOMAIN ); ?></label></h3>
			    <div class="inside">
			        <?php _e( 'Search for a license by using email, name, key or transaction ID', PLM_TEXT_DOMAIN ); ?>
			        <br /><br />
			        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			            <input name="plm_search" type="text" size="40" value=""/>
			            <input type="submit" name="plm-submit-search" class="button" value="<?php _e( 'Search', PLM_TEXT_DOMAIN ); ?>" />
			        </form>
			    </div>
			</div>
			<div class="postbox">
			    <h3 class="hndle"><label for="title"><?php _e( 'Licenses', PLM_TEXT_DOMAIN ); ?></label></h3>
			    <div class="inside">
			        <?php
			        include_once( PLM_PATH.'admin/classes/class-license-list-table.php' );
			        include_once( PLM_PATH.'admin/classes/class-license-table.php' );
			        $license_list = new plm_Licenses_Table();
			        if (isset($_REQUEST['action'])) { 
			            if (isset($_REQUEST['action']) && sanitize_text_field ( $_REQUEST['action'] ) == 'delete_license') { 
			                $license_list->delete_licenses(sanitize_text_field ($_REQUEST['id']));
			            }
			        }
			        $license_list->prepare_items();
			        ?>
			        <form id="tables-filter" method="get" onSubmit="return confirm('<?php _e( 'Are you sure you want to perform this bulk operation on the selected entries?', PLM_TEXT_DOMAIN ); ?>');">
			            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
			            <input type="hidden" name="page" value="<?php echo sanitize_text_field ( $_REQUEST['page'] ); ?>" />
			            <!-- Now we can render the completed list table -->
			            <?php $license_list->display(); ?>
			        </form>
			    </div>
			</div>
		</div>
	</div>
</div>
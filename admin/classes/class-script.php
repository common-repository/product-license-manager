<?php
class PLM_Script{
	function __construct(){
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
	}
	function admin_script(){
		$screen = get_current_screen();
		//** Styles
		wp_enqueue_style( 'plm-license-style', PLM_URL . '/admin/assets/css/plm-admin-style.css', array(), PLM_VERSION );
		//** Scripts
		wp_enqueue_script( 'jquery' );

        if ( $screen->post_type == "product" || $screen->id == 'toplevel_page_plmmanager' || $screen->id == 'product-license-manager_page_plmlicense' ) {
        	//** Styles
        	wp_enqueue_style('jquery-ui-style',  PLM_URL . '/admin/assets/css/jquery-ui.css', array(), PLM_VERSION );
        	//** Scripts		
			wp_enqueue_script( 'jquery-repeater-script', PLM_URL . '/admin/assets/js/jquery.repeater.min.js', array('jquery'), PLM_VERSION, true );
        	//** Scripts
        	wp_enqueue_script('jquery-ui-datepicker');
        	wp_enqueue_script('plm-admin-ajax-js', PLM_URL . '/admin/assets/js/plm-admin-ajax.js', array( 'jquery-ui-dialog' ), PLM_VERSION);
            $translation_array = array(
            	'deleteMessage' 	=> __('Are you sure you want to delete this?', PLM_TEXT_DOMAIN ),
            	'deactivateLabel' 	=> __( 'Deactivate', PLM_TEXT_DOMAIN ),
				'activateLabel' 	=> __( 'Activate', PLM_TEXT_DOMAIN ),
			    'deactivateMessage' => __( 'Are you sure you want to deactivate license?', PLM_TEXT_DOMAIN ),
			    'ajaxurl' => admin_url( 'admin-ajax.php' ),
			);
			wp_localize_script( 'plm-admin-ajax-js', 'plmAjaxHandler', $translation_array );
        }
		
	}
}
new PLM_Script;
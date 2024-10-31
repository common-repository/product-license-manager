jQuery(document).ready(function($){
	'use strict';
	if( $.fn.repeater() ){
		$('#excluded-file-table, #license-renewal-table').repeater({
		    show: function () {
		        $(this).slideDown();
		    },
		    hide: function (deleteElement) {
		        if( confirm( plmAjaxHandler.deleteMessage ) ) {
		            $(this).slideUp(deleteElement);
		        }
		    },
		    ready: function (setIndexes) {

		    }
	    });
	}
	$('.plm_pick_date').datepicker({
		dateFormat : 'yy-mm-dd'
	});
	$('.del').on( 'click', function( e ) {
		e.preventDefault();
		var regDomainID = $(this).attr('id');
		var deleteConfirm = confirm( plmAjaxHandler.deactivateMessage );
		if( deleteConfirm ){
			$.ajax({
				type:"POST",
				data:{
					action:'remove_registered_domain',	
					regDomainID:regDomainID,
				},
				url : plmAjaxHandler.ajaxurl,
				beforeSend:function(){
					//** Proccessing
					$('body').append('<div class="plm-loading">Loading...</div>');
				},
				success:function(data){
					if( data == 1 ){
						$('#registered-domain-wrapper').prepend( '<p id="action-notification" class="message-success">Domain successfully remove!</p>' );
	                    $('#'+regDomainID).parent().parent().remove();
					}else{
						$('#registered-domain-wrapper').prepend( '<p id="action-notification" class="message-error">Failed to remove!</p>' );
					}
					setTimeout( function(){
						$('#registered-domain-wrapper #action-notification').remove();
					},3000)
					$('body .plm-loading').remove();
				}
			});
		}
		return false;
    });
});
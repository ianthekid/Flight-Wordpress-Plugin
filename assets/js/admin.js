jQuery( document ).ready( function ( e ) {
	
	jQuery('.fbc_attachment').click(function(e){
		var name = jQuery(this).data('name');
		e.preventDefault();
		jQuery('#thumbnail-head-8').find('img').attr('src',jQuery(this).find('img').attr('src'));
		//jQuery('#attachments[8][post_title]').val( name );
		
		jQuery('.post_title').find('input').val( name );
		
		jQuery("#library-form").show();
		jQuery('.selected').removeClass('selected');
		jQuery(this).addClass('selected');
	});	
	jQuery("#library-form").appendTo("#fbc_media-sidebar");

	
	jQuery('#fbc_loadMore').click(function(e){
		jQuery.ajax({
			url: '/wp-content/plugins/Flight_by_Canto/includes/lib/loadMore.php',
			type: 'GET',
			data: {"limit": 40, "start": 40},
			error: function(xhr, desc, err) {
				console.log(xhr);
				console.log("Details: " + desc + "\nError:" + err);
			}
		});
	});

});
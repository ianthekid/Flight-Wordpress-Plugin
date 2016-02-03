jQuery( document ).ready( function ( e ) {
	jQuery('.media-upload-form').find('.button').on('click', function() {
		jQuery('#loader').show();
	});

	jQuery('#hideShow').on('click', function(){
	    var tree = jQuery('#fbc-tree');
	    if (tree.is(':visible')){
	        tree.animate({"left":"-250px"}, "fast").hide();
			jQuery('#hideShow>i').addClass('fa-bars');
			jQuery('#hideShow>i').removeClass('fa-close');
			jQuery('#fbc-loop').css({'margin-left':'0px' });
	    } else {
	        tree.animate({"left":"0px"}, "fast").show();
			jQuery('#hideShow>i').removeClass('fa-bars');
			jQuery('#hideShow>i').addClass('fa-close');
			jQuery('#fbc-loop').css({'margin-left':'250px' });
	    }
	});
});

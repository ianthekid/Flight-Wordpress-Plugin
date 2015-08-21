<?php

if ( ! defined( 'ABSPATH' ) ) exit;


// Add filter that inserts our new tab
function flight_by_canto_media_menu($tabs) {
	$newtab = array('flight_by_canto' => __('Flight by Canto', 'flight-by-canto'));
	return array_merge($tabs, $newtab);
}

// Load media_nsm_process() into the existing iframe
function flight_by_canto_media_upload_flight_by_canto() {
	$nsm = new flight_by_canto_media();
	return wp_iframe(array( $nsm, 'media_upload_flight_by_canto' ), array());
}

function flight_by_canto_media_init() {
	if ( current_user_can('upload_files') ) {
		load_plugin_textdomain( 'flight-by-canto', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_filter('media_upload_tabs', 'flight_by_canto_media_menu');
		add_action('media_upload_flight_by_canto', 'flight_by_canto_media_upload_flight_by_canto');
		add_action ('media_upload_flight_by_canto', 'get_meta_data');

	}
}

add_action( 'init', 'flight_by_canto_media_init' );

class flight_by_canto_media {
	var $media_items = '';

        function get_media_items( $post_id, $errors ) {
                global $blog_id;
                $output = get_media_items( $post_id, $errors );

                // remove edit button pre WP3.5
                $output = preg_replace( "%<p><input type='button' id='imgedit-open-btn.+?class='imgedit-wait-spin'[^>]+></p>%s", '', $output );

                //  remove edit button WP3.5+
                $output = preg_replace( "%<p><input type='button' id='imgedit-open-btn.+?<span class='spinner'></span></p>%s", '', $output );

                // remove delete link
                $output = preg_replace( "%<a href='#' class='del-link' onclick=.+?</a>%s", '', $output );
                $output = preg_replace( "%<div id='del_attachment_.+?</div>%s", '', $output );

                return $output;
        }


	/**
	 * @param unknown_type $errors
	 */
	function media_upload_flight_by_canto($errors) {
		global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types, $blog_id;

		media_upload_header();

wp_enqueue_script('fbc_media_js', plugins_url().'/Flight_by_Canto/assets/js/admin.js' );
//		echo "<script type='text/javascript' src='" . ABSPATH . plugins_dir() . "/Flight_By_Canto/assets/js/admin.js'></script>";








if( get_option( 'fbc_flight_domain' ) == '' ||  get_option( 'fbc_app_id' ) == '' ||  get_option( 'fbc_app_secret' ) == '') :
	echo '<form><h3 class="media-title">' . __("<span style='font-size:14px;font-family:Helvetica,Arial'><strong>Oops!</strong> You haven't connected your Flight account yet. <a href=\"javascript:;\" onclick=\"window.top.location.href='".get_bloginfo('url')."/wp-admin/options-general.php?page=flight_by_canto_settings'\">Plugin Settings</a></span>", 'flight-by-canto' ) . '</h3></form>';

	return false;

else :


?>
<?php /*
    <div class="media-toolbar">
        <div class="media-toolbar-primary search-form" style="float:left; margin: 10px">
            <label for="media-search-input" class="screen-reader-text">Search Media</label>
            <input type="search" placeholder="Search" id="media-search-input" class="search">
        </div>
        <div class="media-toolbar-secondary" style="float:left; margin: 10px">
            <label for="media-attachment-filters" class="screen-reader-text">Filter by type</label>
            <select id="media-attachment-filters" class="attachment-filters"><option value="all">All media items</option><option value="image">Images</option></select>
            <label for="media-attachment-date-filters" class="screen-reader-text">Filter by date</label>
            <select id="media-attachment-date-filters" class="attachment-filters"><option value="all">Order by Date</option><option value="0">Ascending</option><option value="0">Descending</option></select>
            <span class="spinner"></span>
        </div>
    </div> */?>
	<div id="fbc_media-sidebar" class="media-sidebar">
		<div class="media-uploader-status">
			<h3 id="fbc_file_name"></h3>
			<div class="media-progress-bar"></div>
        </div>
    </div>
    <div class="upload-details">
        <span class="upload-filename"></span>
	</div>
    <div class="upload-errors"></div>

    <div style="clear:both"></div>



<?php


?>


<img src="/ajax-loader.gif" id="loader">

<ul tabindex="-1" class="attachments" id="__attachments-view-fbc">
<?php

/*
$flight['url']		= 'demo';
$flight['appId']	= 'f38812b27dc24b1eabd2837e15b8f119';
$flight['secret']	= '7113cf4ce1a54e74a5fd0a3f324d05a98b7eb0d269004db5ad09ccc577ba5773';
*/
$flight['url']		= get_option('fbc_flight_domain');
$flight['appId']	= get_option('fbc_app_id');
$flight['secret']	= get_option('fbc_app_secret');

//New token:: 111ecb6e79024216aad2f1a4d9bcb9b1
$flight['token']	= get_option('fbc_app_token');
//$flight['token']	= '18a91e5134f54e78a1138ad26800df4a';
$flight['header']	= array('Authorization: Bearer '.$flight['token']);
$flight['agent']	= 'Canto Dev Team';

//INIT PULL
$flight['api_url']	= 'https://'.$flight['url'].'.run.cantoflight.com/api/v1/';
$flight['req'] 		= $flight['api_url'].'image?sortBy=name&sortDirection=descending&limit=40&start=0';


$response = Flight_by_Canto()->curl_action($flight['req'],$flight['header'],$flight['agent'],0);
//var_dump($response);
//echo $response;
$response = json_decode($response);
$results = $response->results;

//$dir = plugin_dir_path( __FILE__ ).'assets/';

$dir = ABSPATH . 'wp-content/plugins/Flight_by_Canto/assets/cache/';
$display = get_bloginfo('url') . '/wp-content/plugins/Flight_by_Canto/assets/cache/';

$allowed_exts = array('jpg','jpeg','gif','png');
$images = array();
$cnt=0;
/*foreach ($results as $res){
//				$res->details 	=
echo "<pre>";
//print_r($res->url);
 print_r( json_decode(Flight_by_Canto()->curl_action($flight['api_url'].'image/'.$res->id, $flight['header'], $flight['agent'],0)));
echo "</pre>";
}*/

foreach($results as $res) {

			$img = array(
				'id'		=> $res->id,
				'name'		=> $res->name,
				'preview'	=> $res->url->preview,
				'ext'		=> strtolower(end((explode(".",$res->name)))));

	$ext = strtolower(end((explode(".",$res->name))));

	if( in_array($ext,$allowed_exts) && !file_exists($dir . $res->id .'.'. $ext) )
		array_push($images,$img);

}

//If there are new assets that we dont already have cached, go get them
$r = Flight_by_Canto()->multiRequest($images);

foreach($r as $i) {

	//if( !file_exists($dir.'cache/'.$i['name']) ) :

		list($httpheader) = explode("\r\n\r\n", $i['img'], 2);
		$matches = array();
		preg_match('/(Location:|URI:)(.*?)\n/', $httpheader, $matches);
		$location = trim(str_replace("Location: ","",$matches[0]));

	$ext = strtolower(end((explode(".",$i['name']))));
		copy($location,$dir.$i['id'].'.'.$ext);

//	endif;

}

/* Print the results to the screen */
foreach($results as $res) {
//print_r($res);
	$ext = strtolower(end((explode(".",$res->name))));
	if( in_array($ext,$allowed_exts) ) :

	?>
	<li tabindex="0" role="checkbox" data-id="<?php echo $res->id; ?>" data-name="<?php echo str_replace('.'.$ext,"",$res->name); ?>" class="fbc_attachment attachment save-ready details">
		<div class="attachment-preview js--select-attachment type-image subtype-jpeg landscape">
			<div class="thumbnail">
                <div class="centered">
                    <img src="<?php echo $display.$res->id . '.'.$ext; ?>" draggable="false" alt="">
                </div>
			</div>
		</div>
        <a class="check" href="#" title="Deselect" tabindex="0"><div class="media-modal-icon"></div></a>
	</li>
    <?php

	endif;
//	echo '<a class="fbc_link fbc_selected" href="javascript:;" data-id="'.$i['id'].'"><div style="background-image:url('.$display.$res->name.')"></div></a>';

}

?>
</ul>

<?php
/*
<div style="clear:both; margin: 20px auto; text-align:center;">
	<button class="btn" id="fbc_loadMore">Load More</button>
</div>
*/
?>

<script>
document.getElementById("loader").style.display='none';
</script>



<?php







endif;

//Stop checking to see if user has valid flight credentials 









		// set the first part of the form action url now, to the current active site, to prevent X-Frame-Options problems
		$form_action_url = plugins_url( 'copy-media.php', __FILE__ );


?>

<?php
		$post_id = intval($_REQUEST['post_id']);

		// fix to make get_media_item add "Insert" button
		unset($_GET['post_id']);

		$form_action_url .= "?type=$type&tab=library&post_id=$post_id";
		$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);

		$form_class = 'media-upload-form validate';


		$_GET['paged'] = isset( $_GET['paged'] ) ? intval($_GET['paged']) : 0;
		if ( $_GET['paged'] < 1 )
			$_GET['paged'] = 1;
		$start = ( $_GET['paged'] - 1 ) * 10;
		if ( $start < 1 )
			$start = 0;
		add_filter( 'post_limits', create_function( '$a', "return 'LIMIT $start, 10';" ) );

		list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query();
?>

	<form id="filter" action="" method="get">
	<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>" />
	<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
	<input type="hidden" name="post_id" value="<?php echo (int) $post_id; ?>" />

	<input type="hidden" name="post_mime_type" value="<?php echo isset( $_GET['post_mime_type'] ) ? esc_attr( $_GET['post_mime_type'] ) : ''; ?>" />
	<?php if( isset($_GET['chromeless']) ) : ?>
	<input type="hidden" name="chromeless" value="<?php echo (bool) $_GET['chromeless']; ?>" />
	<?php endif; ?>

	<style type="text/css">
		#media-upload #filter .nsm-site-select { float: none; width: 100%; margin: 0 1em 2em 1em; white-space: normal; }
	</style>


	<!--p id="media-search" class="search-box">
		<label class="screen-reader-text" for="media-search-input"><?php _e('Search Media');?>:</label>
		<input type="text" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
		<?php submit_button( __( 'Search Media' ), 'button', '', false ); ?>
	</p-->

	<ul class="subsubsub" style="display:none">
	<?php
	$type_links = array();
	$_num_posts = (array) wp_count_attachments();
	$matches = wp_match_mime_types(array_keys($post_mime_types), array_keys($_num_posts));
	foreach ( $matches as $_type => $reals )
		foreach ( $reals as $real )
			if ( isset($num_posts[$_type]) )
				$num_posts[$_type] += $_num_posts[$real];
			else
				$num_posts[$_type] = $_num_posts[$real];
	// If available type specified by media button clicked, filter by that type
	if ( empty($_GET['post_mime_type']) && !empty($num_posts[$type]) ) {
		$_GET['post_mime_type'] = $type;
		list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query();
	}
	if ( empty($_GET['post_mime_type']) || $_GET['post_mime_type'] == 'all' )
		$class = ' class="current"';
	else
		$class = '';
	$type_links[] = "<li><a href='" . esc_url(add_query_arg(array('post_mime_type'=>'all', 'paged'=>false, 'm'=>false))) . "'$class>".__('All Types')."</a>";
	foreach ( $post_mime_types as $mime_type => $label ) {
		$class = '';

		if ( !wp_match_mime_types($mime_type, $avail_post_mime_types) )
			continue;

		if ( isset($_GET['post_mime_type']) && wp_match_mime_types($mime_type, $_GET['post_mime_type']) )
			$class = ' class="current"';

		$type_links[] = "<li><a href='" . esc_url(add_query_arg(array('post_mime_type'=>$mime_type, 'paged'=>false))) . "'$class>" . sprintf( translate_nooped_plural( $label[2], $num_posts[$mime_type] ), "<span id='$mime_type-counter'>" . number_format_i18n( $num_posts[$mime_type] ) . '</span>') . '</a>';
	}
	echo implode(' | </li>', apply_filters( 'media_upload_mime_type_links', $type_links ) ) . '</li>';
	unset($type_links);
	?>
	</ul>

	<div class="tablenav" style="display:none">

	<?php
	$page_links = paginate_links( array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'format' => '',
		'prev_text' => __('&laquo;'),
		'next_text' => __('&raquo;'),
		'total' => ceil($wp_query->found_posts / 10),
		'current' => $_GET['paged']
	));

	if ( $page_links )
		echo "<div class='tablenav-pages'>$page_links</div>";
	?>

	<div class="alignleft actions">
	<?php

	$arc_query = "SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY post_date DESC";

	$arc_result = $wpdb->get_results( $arc_query );

	$month_count = count($arc_result);

	if ( $month_count && !( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) { ?>
	<select name='m'>
	<option<?php selected( @$_GET['m'], 0 ); ?> value='0'><?php _e('Show all dates'); ?></option>
	<?php
	foreach ($arc_result as $arc_row) {
		if ( $arc_row->yyear == 0 )
			continue;
		$arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );

		if ( isset($_GET['m']) && ( $arc_row->yyear . $arc_row->mmonth == $_GET['m'] ) )
			$default = ' selected="selected"';
		else
			$default = '';

		echo "<option$default value='" . esc_attr( $arc_row->yyear . $arc_row->mmonth ) . "'>";
		echo esc_html( $wp_locale->get_month($arc_row->mmonth) . " $arc_row->yyear" );
		echo "</option>\n";
	}
	?>
	</select>
	<?php } ?>

	<?php submit_button( __( 'Filter &#187;' ), 'secondary', 'post-query-submit', false ); ?>

	</div>

	<br class="clear" />
	</div>
	</form>

	<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="<?php echo $form_class; ?>" id="library-form">

	<?php wp_nonce_field('media-form'); ?>

	<?php
		if( isset($_GET['chromeless']) && $_GET['chromeless'] ):
			// WP3.5+ Media Browser calls iframe 'chromeless' and handles inserting differently
	?>
	<script type="text/javascript">
	/* <![CDATA[ */
	function nsm_media_send_to_editor(htmlString) {
		<?php /* copied from /wp-admin/includes/media.php media_send_to_editor() */ ?>
		var win = window.dialogArguments || opener || parent || top;
		win.send_to_editor(htmlString);
	}

	jQuery(function($){
		$('input[id^=send].button').click(function(event) {
			event.preventDefault();
			var $this = $(event.target);
			var form = $('#library-form');
			var result = $.ajax({
				url: form.attr('action'),
				type: form.attr('method'),
				data: form.serialize() + '&' + encodeURIComponent($this.attr('id') ) + '=true&chromeless=1',
				//success: function(data){alert(data);}//nsm_media_send_to_editor
				success: nsm_media_send_to_editor
			});
		});
	});
	/* ]]> */
	</script>
	<?php endif; /* chromeless */ ?>

	<script type="text/javascript">
	<!--
	jQuery(function($){
		var preloaded = $(".media-item.preloaded");
		if ( preloaded.length > 0 ) {
			preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
			updateMediaForm();
		}
	});
	-->
	</script>










	<div id="media-items">
	<?php //add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2); ?>
<?php //add_filter('attachment_fields_to_edit', 'media_single_attachment_fields_to_edit', 10, 2); ?>
	<input id="fbc_id" name="fbc_id" type="hidden" value="" />


<div tabindex="0" data-id="0" class="fbc attachment-details save-ready">
        <h3>
            Attachment Details
            <span class="settings-save-status">
                <span class="spinner"></span>
                <span class="saved">Saved.</span>
            </span>
        </h3>
        <div class="attachment-info">
            <div class="thumbnail thumbnail-image">

                    <img draggable="false" src="http://wp.flightbycanto.com/wp-content/uploads/2015/06/flight-by-canto-300x142.jpg">

            </div>
            <div class="details">
                <div class="filename">name</div>
                <div class="uploaded">name</div>
                <div class="filesize">name</div>
                <div class="dimensions">name</div>
            </div>
        </div>

        <label data-setting="title" class="setting">
            <span class="name">Title</span>
            <input type="text" id="title" name="title" value="">
        </label>

        <label data-setting="caption" class="setting">
            <span class="name">Caption</span>
            <textarea id="caption" name="caption"></textarea>
        </label>

        <label data-setting="alt" class="setting">
            <span class="name">Alt Text</span>
            <input type="text" id="alt-text" name="alt" value="">
        </label>

        <label data-setting="description" class="setting">
            <span class="name">Description</span>
            <textarea id="description" name="description"></textarea>
        </label>
    </div>
    <div class="attachment-display-settings">
        <h3>Attachment Display Settings</h3>

            <label class="setting">
                <span>Alignment</span>
                <select data-user-setting="align" name="align" data-setting="align" class="alignment">

                    <option value="left">Left</option>
                    <option value="center">Center</option>
                    <option value="right">Right</option>
                    <option selected="" value="none">None</option>
                </select>
            </label>

        <div class="setting">
            <label>
                    <span>Link To</span>


                <select data-user-setting="urlbutton" data-setting="link" class="link-to" name="link">
                    <option selected="" value="file">Media File</option>
                    <option value="post">Attachment Page</option>
                    <!--<option value="custom">Custom URL</option> -->
                    <option value="none">None</option>
                </select>
            </label>
        </div>


            <label class="setting">
                <span>Size</span>
                <select data-user-setting="imgsize" data-setting="size" name="size" class="size">
<?php
                                        /** This filter is documented in wp-admin/includes/media.php */
                                        $sizes = apply_filters( 'image_size_names_choose', array(
                                                'thumbnail' => __('Thumbnail'),
                                                'medium'    => __('Medium'),
                                                'large'     => __('Large'),
                                                'full'      => __('Full Size'),
                                        ) );


                                        foreach ( $sizes as $value => $name ) : ?>
                                                <#
                                                var size = data.sizes['<?php echo esc_js( $value ); ?>'];
                                                if ( size ) { #>
                                                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, 'full' ); ?>>
                                                                <?php echo esc_html( $name ); ?> &ndash; {{ size.width }} &times; {{ size.height }}
                                                        </option>
                                                <# } #>
                                        <?php endforeach; ?>





                    //Get available thumbnail sizes
                </select>
            </label>
<?php echo get_submit_button( __( 'Insert into Post' ), 'button', "send[$post_id]", false );
?>
      </div>



















	</div>
	<p class="ml-submit"></p>
	</form>
	<?php
	}
}
?>
<?php

function get_image_sizes( $size = '' ) {

        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach( $get_intermediate_image_sizes as $_size ) {

                if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

                        $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
                        $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
                        $sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

                } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

                        $sizes[ $_size ] = array(
                                'width' => $_wp_additional_image_sizes[ $_size ]['width'],
                                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                                'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
                        );

                }

        }

        // Get only 1 size if found
        if ( $size ) {

                if( isset( $sizes[ $size ] ) ) {
                        return $sizes[ $size ];
                } else {
                        return false;
                }

        }

        return $sizes;
}


function fbc_insert_custom_image_sizes( $sizes ) {
  global $_wp_additional_image_sizes;
  if ( empty($_wp_additional_image_sizes) )
    return $sizes;

  foreach ( $_wp_additional_image_sizes as $id => $data ) {
    if ( !isset($sizes[$id]) )
      $sizes[$id] = ucfirst( str_replace( '-', ' ', $id ) );
  }

  return $sizes;
}

//Add custom image sized to thumbnail selection if the user hasnt already.
if ( ! has_filter ('image_size_names_choose')){
	add_filter( 'image_size_names_choose', 'fbc_insert_custom_image_sizes' );
}

function get_meta_data() {

 	$nonce = wp_create_nonce('flight-by-canto');
?>
        <script type="text/javascript" >

	jQuery('.fbc_attachment').click(function(e){
                e.preventDefault();

                var data = {
                        'action': 'fbc_getMetadata',
                        'fbc_id': jQuery(this).data('id'),
			'nonce': '<?php echo $nonce; ?>'
                };

		//jQuery('#thumbnail-head-8').find('img').attr('src',jQuery(this).find('img').attr('src'));
		var src = jQuery(this).find('img').attr('src'); //;alert(src);
                jQuery.post(ajaxurl, data, function(response) {
//build out the form
//"name":"\u963f\u62c9.jpg","dimensions":"460*367 Pixels","mime":"image\/jpeg","size":"59983"
                        // alert('Got this from the server: ' + response);
	                response = jQuery.parseJSON(response);
	                jQuery('#library-form').find('img').attr('src', src);
	                jQuery('#library-form #fbc_id').val(response.id);
	                jQuery('#library-form .filename').html(response.name);
	                jQuery('#library-form .filesize').html(response.size);
	                jQuery('#library-form .dimensions').html(response.dimensions);
	                jQuery('#library-form .uploaded').html(response.uploaded);
	                jQuery('#library-form .mime').html(response.mime);

	                    jQuery("#library-form").appendTo("#fbc_media-sidebar");
	                    jQuery("#library-form").show();

                });

                /*var name = jQuery(this).data('name');

                jQuery('#library-form .post_title').find('input').val( name );
                jQuery('.selected').removeClass('selected');
                jQuery(this).addClass('selected'); */
        });

        </script> <?php
return;
}


                                                         

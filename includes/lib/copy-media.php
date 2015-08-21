<?php
/**
 * @package Netword_Shared_Media
 * @version 0.10.1
 */
define('WP_ADMIN', FALSE);
define('WP_LOAD_IMPORTERS', FALSE);

require_once( dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/wp-admin/admin.php' );

if (!current_user_can('upload_files'))
	wp_die(__('You do not have permission to upload files.'));

// $blog_id is global var in WP

if( isset( $_POST['send'] ) ) {
//	$nsm_blog_id = (int) $_GET['blog_id'];
	reset( $_POST['send'] );
	$nsm_send_id = (int) key( $_POST['send'] );
}

/* copied from wp-admin/inculdes/ajax-actions.php wp_ajax_send_attachment_to_editor() */
if (  isset( $nsm_send_id ) ) {
	//switch_to_blog( $nsm_blog_id );

	global $post;

	$attachment = $_POST['fbc_id'] ; //wp_unslash( $_POST['attachments'][$nsm_send_id] );
	$id = $nsm_send_id;
/* Checks to see if you're using a real attachment (We're not)
	if ( ! $post = get_post( $id ) )
		wp_send_json_error();

	if ( 'attachment' != $post->post_type )
		wp_send_json_error();
*/

//Go get the media item from Flight
$flight['token']        = '18a91e5134f54e78a1138ad26800df4a';
$flight['token'] 	= get_option('fbc_app_token');
$flight['header']       = array('Authorization: Bearer '.$flight['token']);
$flight['agent']        = 'Canto Dev Team';

//INIT PULL
$flight['api_url']      = 'https://'.get_option('fbc_flight_domain').'.run.cantoflight.com/api/v1/';
$flight['api_url2']     = 'https://'.get_option('fbc_flight_domain').'.run.cantoflight.com/api_binary/v1/';
$flight['req']          = $flight['api_url'].'image/' . $_POST['fbc_id'];// .'/download';


$instance = Flight_by_Canto::instance();
$response = $instance->curl_action($flight['req'],$flight['header'],$flight['agent'],0);
$response  = (json_decode($response));

//Get the download url
$detail = $response->url->download;
$detail = $instance->curl_action($detail, $flight['header'],$flight['agent'],1);

//echo(json_encode($response));
//echo($detail);
//exit();

                list($httpheader) = explode("\r\n\r\n", $detail, 2);
                $matches = array();
                preg_match('/(Location:|URI:)(.*?)\n/', $httpheader, $matches);
                $location = trim(str_replace("Location: ","",$matches[0]));







    $tmp = download_url( $location );
    $file_array = array(
        'name' => $response->name,
        'tmp_name' => $tmp
    );
//var_dump($file_array);
//exit();
    // Check for download errors
    if ( is_wp_error( $tmp ) ) {
        @unlink( $file_array[ 'tmp_name' ] );
        return $tmp;
    }

    $id = media_handle_sideload( $file_array, 0 );
    // Check for handle sideload errors.
    if ( is_wp_error( $id ) ) {
        @unlink( $file_array['tmp_name'] );
        return $id;
    }

    $attachment_url = wp_get_attachment_url( $id );
    // Do whatever you have to here

	$caption = $title = $align = $rel = $size = $alt = '';
	$rel = false;
	$html = get_image_send_to_editor( $id, $caption, $title, $align, $attachment_url, (bool) $rel, $size, $alt );


/*
	$rel = $url = '';
	$html = $title = isset( $attachment['post_title'] ) ? $attachment['post_title'] : '';
	if ( ! empty( $attachment['url'] ) ) {
		$url = $attachment['url'];
		if ( strpos( $url, 'attachment_id') || get_attachment_link( $id ) == $url )
			$rel = ' rel="attachment wp-att-' . $id . '"';
		$html = '<a href="' . esc_url( $url ) . '"' . $rel . '>' . $html . '</a>';
	}

	if ( 'image' === substr( $post->post_mime_type, 0, 5 ) ) {
		$align = isset( $attachment['align'] ) ? $attachment['align'] : 'none';
		$size = isset( $attachment['image-size'] ) ? $attachment['image-size'] : 'medium';
		$alt = isset( $attachment['image_alt'] ) ? $attachment['image_alt'] : '';
		$caption = isset( $attachment['post_excerpt'] ) ? $attachment['post_excerpt'] : '';
		$title = ''; // We no longer insert title tags into <img> tags, as they are redundant.
		$html = get_image_send_to_editor( $id, $caption, $title, $align, $url, (bool) $rel, $size, $alt );
	} elseif ( 'video' === substr( $post->post_mime_type, 0, 5 ) || 'audio' === substr( $post->post_mime_type, 0, 5 ) ) {
		global $wp_embed;
		$meta = get_post_meta( $id, '_wp_attachment_metadata', true );
		$html = $wp_embed->shortcode( $meta, $url );
	}
*/
$attachment  =array();
	$attachment['url'] = $attachment_url;
	$attachment['post_title'] = '';
	$attachment['post_excerpt'] = '';
	/** This filter is documented in wp-admin/includes/media.php */
	$html = apply_filters( 'media_send_to_editor', $html, $id, $attachment );

	// replace wp-image-<id>, wp-att-<id> and attachment_<id>
	$html = preg_replace(
		array(
			'#(caption id="attachment_)(\d+")#', // mind the quotes!
			'#(wp-image-|wp-att-)(\d+)#'
		),
		array(
			sprintf('${1}nsm_%s_${2}', esc_attr($nsm_send_id)),
			sprintf('${1}nsm-%s-${2}', esc_attr($nsm_send_id)),
		),
		$html
	);


	if( isset($_POST['chromeless']) && $_POST['chromeless'] ) {
		// WP3.5+ media browser is identified by the 'chromeless' parameter
		exit($html);
	} else {
		return media_send_to_editor($html);
	}
}

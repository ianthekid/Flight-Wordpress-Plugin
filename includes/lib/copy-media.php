<?php
/**
 * @package Flight_By_Canto
 * @version 1.0.0
 */

function curl_action( $url, $echo ) {

	if ( ! function_exists( 'curl_init' ) ) {
		die( 'Sorry cURL is not installed!' );
	}

	$agent  = "WordPress Plugin";
	$header = array( 'Authorization: Bearer ' . $_POST['fbc_app_token'] );

	$ch = curl_init();

	$options = array(
		CURLOPT_URL            => $url,
		CURLOPT_REFERER        => "Wordpress Plugin",
		CURLOPT_USERAGENT      => $agent,
		CURLOPT_HTTPHEADER     => $header,
		//CURLOPT_SSLVERSION     => 3,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_HEADER         => $echo,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT        => 10,
	);

	curl_setopt_array( $ch, $options );
	$output = curl_exec( $ch );
	curl_close( $ch );

	//return curl_error($ch);
	return $output;
}

define( 'WP_ADMIN', false );
define( 'WP_LOAD_IMPORTERS', false );

//require_once( dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/wp-admin/admin.php' );

require_once ( urldecode($_POST["abspath"]) . 'wp-admin/admin.php' );

if ( isset( $_POST['send'] ) ) {
	reset( $_POST['send'] );
	$send_id = (int) key( $_POST['send'] );
}

/* copied from wp-admin/inculdes/ajax-actions.php wp_ajax_send_attachment_to_editor() */
if ( isset( $send_id ) ) {

	global $post;

	$attachment = $_POST['fbc_id'];
	$id         = $send_id;

	//Go get the media item from Flight
	$flight['api_url']  = 'https://' . $_POST['fbc_flight_domain'] . '.cantoflight.com/api/v1/';
	$flight['req']      = $flight['api_url'] . $_POST['fbc_scheme'] . '/' . $_POST['fbc_id'];


//	$instance = Flight_by_Canto::instance();
	$response = curl_action( $flight['req'], 0 );
	$response = ( json_decode( $response ) );

	//Get the download url
	$detail = $response->url->download;
	$detail = curl_action( $detail, 1 );

/*
	list( $httpheader ) = explode( "\r\n\r\n", $detail, 2 );
	$matches = array();
	preg_match( '/(Location:|URI:)(.*?)\?x-amz-security-token/', $httpheader, $matches );
	$location = trim( str_replace( "Location: ", "", $matches[0] ) );
*/

    $matches = array();
    $httpheader = explode("Server: ",$detail);
    preg_match( '/(Location:|URI:)(.*?)\n/', $httpheader[0], $matches );
    $uri = str_replace( array("Location: "), "", $matches[0] );
    $location = trim( $uri );



	$tmp        = download_url( $location );
	$file_array = array(
		'name'     => $response->name,
		'tmp_name' => $tmp
	);

	// Check for download errors
	if ( is_wp_error( $tmp ) ) {
		@unlink( $file_array['tmp_name'] );

		return $tmp;
	}

	$post_data = array(
			'post_content' => $_POST['description'],
			'post_excerpt' => $_POST['caption'],
	);


	if (! empty($_POST['title'])) {
		$post_data['post_title'] = $_POST['title'];
	} else {
		$post_data['post_title'] = basename($file_array['name']);
	}

	$id = media_handle_sideload( $file_array, $send_id, '', $post_data);
	// Check for handle sideload errors.
	if ( is_wp_error( $id ) ) {
		@unlink( $file_array['tmp_name'] );

		return $id;
	}
	//Save away the default alt text
	add_post_meta ($id, '_wp_attachment_image_alt' , $_POST['alt']);
	add_post_meta ($id, 'description' , $_POST['description']);
	add_post_meta ($id, 'copyright' , $_POST['copyright']);
	add_post_meta ($id, 'terms' , $_POST['terms']);

	$attachment_url = wp_get_attachment_url( $id );

	// Additional parameters

	//$caption = $title = $align = $rel = $size = $alt = '';
	//$rel     = false;
	//$html    = get_image_send_to_editor( $id, $caption, $title, $align, $attachment_url, (bool) $rel, $size, $alt );



		$rel = $url = '';
		$html = $title = isset( $_POST['title'] ) ? $_POST['title'] : '';

		//Create the link to section here.
		/*
		if ( $_POST['link'] === "none" ) {
			$attachment_url = '';
		} else {
			$url = $attachment_url;
			if ( strpos( $url, 'attachment_id') || get_attachment_link( $id ) == $url )
				$rel = ' rel="attachment wp-att-' . $id . '"';
			$html = '<a href="' . esc_url( $url ) . '"' . $rel . '>' . $html . '</a>';
		}
		*/

			$align = isset( $_POST['align'] ) ? $_POST['align'] : 'none';
			$size = isset( $_POST['size'] ) ? $_POST['size'] : 'medium';
			$alt = isset( $_POST['alt'] ) ? $_POST['alt'] : '';
			$caption = isset( $_POST['caption'] ) ? $_POST['caption'] : '';
			$title = ''; // We no longer insert title tags into <img> tags, as they are redundant.
			$html = get_image_send_to_editor( $id, $caption, $title, $align, $url, (bool) $rel, $size, $alt );


	$attachment                 = array();
	$attachment['url']          = $attachment_url;
	$attachment['post_title']   = $_POST['title'];
	$attachment['post_excerpt'] = $_POST['caption'];
	$attachment['image-size'] = $_POST['size'];
	$attachment['image_alt'] = $_POST['alt'];
	$attachment['align'] = $_POST['align'];
	$attachment['description'] = $_POST['description'];
	$attachment['copyright'] = $_POST['copyright'];
	$attachment['terms'] = $_POST['terms'];
	/** This filter is documented in wp-admin/includes/media.php */
	$html = apply_filters( 'media_send_to_editor', $html, $id, $attachment );

	// replace wp-image-<id>, wp-att-<id> and attachment_<id>
	$html = preg_replace(
		array(
			'#(caption id="attachment_)(\d+")#', // mind the quotes!
			'#(wp-image-|wp-att-)(\d+)#'
		),
		array(
			sprintf( '${1}nsm_%s_${2}', esc_attr( $send_id ) ),
			sprintf( '${1}nsm-%s-${2}', esc_attr( $send_id ) ),
		),
		$html
	);

	if ( isset( $_POST['chromeless'] ) && $_POST['chromeless'] ) {
		// WP3.5+ media browser is identified by the 'chromeless' parameter
		exit( $html );
	} else {
		return media_send_to_editor( $html );
	}
}

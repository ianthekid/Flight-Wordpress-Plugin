<?php


define( 'WP_ADMIN', false );
define( 'WP_LOAD_IMPORTERS', false );
require_once( dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) . '/wp-admin/admin.php' );
//Get an instance of the DAM Flight by Canto Class

$instance = Flight_by_Canto::instance();


//INIT PULL
$flight['req'] 		= 'https://'.$instance->fbc_flight_domain.'.run.cantoflight.com/api/v1/image?sortBy=name&sortDirection=descending&limit='.$_GET['limit'].'&start='.$_GET['start'];


$response = json_decode($instance->curl_action($flight['req'],0));
$results = $response->results;


$dir = plugin_dir_path( __FILE__ ). '../../assets/cache/';

//$dir = ABSPATH . 'wp-content/plugins/Flight_by_Canto/assets/cache/';
$display = get_bloginfo('url') . '/wp-content/plugins/Flight_by_Canto/assets/cache/';

$allowed_exts = array('jpg','jpeg','gif','png');
$images = array();

foreach($results as $res) {
	$namearray = explode(".",$res->name);
	$img = array('id'		=> $res->id,
				'name'		=> $res->name,
				'preview'	=> $res->url->preview,
				'ext'     => strtolower( end( $namearray ) )
	);
	$ext = strtolower(end($namearray));

	if( in_array($ext,$allowed_exts) && !file_exists($dir.$res->id . '.' . $ext) )
		array_push($images,$img);	

}

$r = $instance->multiRequest($images);

foreach($r as $i) {

	list($httpheader) = explode("\r\n\r\n", $i['img'], 2);
	$matches = array();
	preg_match('/(Location:|URI:)(.*?)\n/', $httpheader, $matches);
	$location = trim(str_replace("Location: ","",$matches[0]));

	$namearray = explode( ".", $i['name'] );
	$ext = strtolower( end( $namearray ) );
	copy( $location, $dir . $i['id'] . '.' . $ext );

}


foreach($results as $res) {
	$namearray = explode(".",$res->name);
	$ext = strtolower(end($namearray));
	if( in_array($ext,$allowed_exts) ) :

	?>
		<li tabindex="0" role="checkbox" data-id="<?php echo $res->id; ?>"
		    data-name="<?php echo str_replace( '.' . $ext, "", $res->name ); ?>"
		    class="fbc_attachment attachment save-ready details">
			<div class="attachment-preview js--select-attachment type-image subtype-jpeg landscape">
				<div class="thumbnail">
					<div class="centered">
						<img src="<?php echo $display . $res->id . '.' . $ext; ?>" draggable="false"
						     alt="">
					</div>
				</div>
			</div>
			<a class="check" href="#" title="Deselect" tabindex="0">
				<div class="media-modal-icon"></div>
			</a>
		</li>
    <?php
	else : echo "<li style='display:none'></li>";
	endif;
//	echo '<a class="fbc_link fbc_selected" href="javascript:;" data-id="'.$i['id'].'"><div style="background-image:url('.$display.$res->name.')"></div></a>';

}
 
?>


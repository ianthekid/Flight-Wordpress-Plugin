<?php
$flight['url']		= 'obj';
$flight['appId']	= 'e76d47111648438eaeda463d2993656d';
$flight['secret']	= 'bf164b54722a42bea721acb1274376c1f2dc43b22e5f4ac3a446bc43f2a5c24e';

$flight['token']	= 'c8cd35ea50f24f6bbce5db1e89cfe95f';
$flight['header']	= array('Authorization: Bearer '.$flight['token']);
$flight['agent']	= 'Canto Dev Team';

//INIT PULL
$flight['req'] 		= 'https://'.$flight['url'].'.run.cantoflight.com/api/v1/image?sortBy=name&sortDirection=descending&limit='.$_GET['limit'].'&start='.$_GET['start'];


$response = json_decode(curl_action($flight['req'],$flight['header'],$flight['agent'],0));
$results = $response->results;


//$dir = plugin_dir_path( __FILE__ ).'assets/';

$dir = ABSPATH . 'wp-content/plugins/Flight_by_Canto/assets/cache/';
$display = get_bloginfo('url') . '/wp-content/plugins/Flight_by_Canto/assets/cache/';

$allowed_exts = array('jpg','jpeg','gif','png');
$images = array();
$cnt=0;
foreach($results as $res) {

	$img = array('id'		=> $res->id,
				'name'		=> $res->name,
				'preview'	=> $res->url->preview);

	$ext = strtolower(end(explode(".",$res->name)));

	if( in_array($ext,$allowed_exts) && !file_exists($dir.$res->name) )
		array_push($images,$img);	

}

$r = multiRequest($images);

foreach($r as $i) {
	
	//if( !file_exists($dir.'cache/'.$i['name']) ) :

		list($httpheader) = explode("\r\n\r\n", $i['img'], 2);
		$matches = array();
		preg_match('/(Location:|URI:)(.*?)\n/', $httpheader, $matches);
		$location = trim(str_replace("Location: ","",$matches[0]));
		
		copy($location,$dir.$i['name']);
		
//	endif;

}


foreach($results as $res) {
	
	$ext = strtolower(end(explode(".",$res->name)));
	if( in_array($ext,$allowed_exts) ) :

	?>
	<li tabindex="0" role="checkbox" data-id="<?php echo $res->id; ?>" data-name="<?php echo str_replace('.'.$ext,"",$res->name); ?>" class="fbc_attachment attachment save-ready details">
		<div class="attachment-preview js--select-attachment type-image subtype-jpeg landscape">
			<div class="thumbnail">
                <div class="centered">
                    <img src="<?php echo $display.$res->name; ?>" draggable="false" alt="">
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


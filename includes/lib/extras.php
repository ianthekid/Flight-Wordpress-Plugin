<?php

/*************************
INVENTORY LOAD MORE SCROLLING
*************************/

function load_more_inventory() {
	$path = get_bloginfo('template_directory').'/assets/js/loadMore.js';
    wp_enqueue_script( 'inventory_posts', $path, array( 'jquery' ), '1.0', true);
    wp_localize_script( 'inventory_posts', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('wp_enqueue_scripts', 'load_more_inventory');

add_action( 'wp_ajax_inventory_posts', 'inventory_query_posts' );
add_action( 'wp_ajax_nopriv_inventory_posts', 'inventory_query_posts' );
function inventory_query_posts() {
    $response = array();
	$postCount = 0;
	$number = 3;
	for($i=0;$i<4;$i++)
	{
		switch($i){
			case 0:
				$st= 'ebooks';
				$offset = intval($_POST['offsetbook']);
				break;
			case 1:
				$st= 'product-information';
				$offset = intval($_POST['offsetproduct']);
				break;
			case 2:
				$st= 'white-papers';
				$offset = intval($_POST['offsetpaper']);
				break;
			case 3:
				$st = 'webinars';
				$offset = intval($_POST['offsetweb']);
				break;
			default:
				break;
		}
		
		$queryMore = new WP_Query( array(
	        'post_type' => 'dam-resources',
			'tax_query' => array(
								array(
									'taxonomy' => 'resource',
									'field'    => 'slug',
									'terms'    => $st,
								),
							),
			'orderby'	=> 'date',
			'order'		=> 'DESC',
			'posts_per_page' => $number,
			'offset'	=> $offset
		));
	
	    if ( ! $queryMore->have_posts() ) {
	        $response->status = false;
	        $response->message = esc_attr__( 'No posts were found' );
	    } else {
	        $response->status = true;
			
			$posts = array();
			while ($queryMore->have_posts()) : $queryMore->the_post(); 
				$postCount++;
				$namePost = $post->post_name;
				$taxCat = ''; $taxName = "";
				$taxs = get_the_terms($post->ID, 'resource');
				foreach($taxs as $tax)
					$taxCat .= $tax->slug.' ';
					$taxName .= $tax->name.' ';
					
				$bgimg = wp_get_attachment_image_src(get_post_thumbnail_id( $post->ID ), 'medium');
				
				switch ($taxCat) {
					case "ebooks ":
						$color = "red";
						break;
					case "webinars ":
						$color = "blue";
						break;
					case "white-papers ":
						$color = "orange";
						break;
					case "product-information ":
						$color = "green";
						break; 
					default :
						$color = "blue";
						break;
				}
	
				$post[] = array('title' 	=> get_the_title(),
								'color'		=> $color,
								'taxCat'	=> $taxCat,
								'taxName'	=> ucfirst( $taxName ),
								'timeC'		=> get_the_date('c'),
								'time'		=> get_the_date('M y'),
								'theLink'	=> get_the_permalink(),
								'imgURL' 	=> $bgimg[0],
								'postName'	=> $namePost,
							);
	
			  
			endwhile;
	        }
	        
		}	
    $response->success = true;
	$response->message = esc_attr__( 'No posts were found' );

    // Never forget to exit or die on the end of a WordPress AJAX action!
    exit( json_encode( $post ) ); 
//	exit (json_encode($tax_query));
}

/*
function load_custom_js() {
	$path = get_bloginfo('template_directory').'/assets/js/_custom.js';
    wp_enqueue_script( 'custom_js_scripts', $path, array( 'jquery' ) );
}
add_action('wp_enqueue_scripts', 'load_custom_js');
*/

function load_isotope() {
	$path = get_bloginfo('template_directory').'/assets/js/plugins/isotope.pkgd.min.js';
    wp_enqueue_script( 'hook_isotope', $path, array( 'jquery' ), '2.2.0', true );
}
add_action('wp_enqueue_scripts', 'load_isotope');



add_filter( 'wp_default_editor', create_function('', 'return "tinymce";'));

//Grant Editors access to edit Gravity Forms
function add_grav_forms(){
    $role = get_role('editor');
    $role->add_cap('gform_full_access');
}
add_action('admin_init','add_grav_forms');


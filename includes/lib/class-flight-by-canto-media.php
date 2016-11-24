<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Add filter that inserts our new tab
function flight_by_canto_media_menu( $tabs ) {
	$newtab = array( 'flight_by_canto' => __( 'Flight by Canto', 'flight-by-canto' ) );

	return array_merge( $tabs, $newtab );
}

// Load media_nsm_process() into the existing iframe
function flight_by_canto_media_upload_flight_by_canto() {
	$nsm = new flight_by_canto_media();

	return wp_iframe( array( $nsm, 'media_upload_flight_by_canto' ), array() );
}

function flight_by_canto_media_init() {
	if ( current_user_can( 'upload_files' ) ) {
		load_plugin_textdomain( 'flight-by-canto', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_filter( 'media_upload_tabs', 'flight_by_canto_media_menu' );
		add_action( 'media_upload_flight_by_canto', 'flight_by_canto_media_upload_flight_by_canto' );
		add_action( 'media_upload_flight_by_canto', 'get_meta_data' );
	}
}

add_action( 'init', 'flight_by_canto_media_init' );

class flight_by_canto_media {
	var $media_items = '';

	/**
	 * @param unknown_type $errors
	 */
	function media_upload_flight_by_canto( $errors ) {
		global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types, $blog_id;

		media_upload_header();

		?>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

		<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
		<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" />

		<link href="//cdn.rawgit.com/noelboss/featherlight/1.3.5/release/featherlight.min.css" type="text/css" rel="stylesheet" />
		<script src="//cdn.rawgit.com/noelboss/featherlight/1.3.5/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>


		<div id="fbc-react"></div>

		<?php

		if ( get_option( 'fbc_flight_domain' ) == '' || get_option( 'fbc_app_token' ) == '' ) :
			echo '<form><h3 class="media-title"><span style="font-size:14px;font-family:Helvetica,Arial">' . __( "<strong>Oops!</strong> You haven't connected your Flight account yet. <a href=\"javascript:;\" onclick=\"window.top.location.href='" . get_bloginfo( 'url' ) . "/wp-admin/options-general.php?page=flight_by_canto_settings'\">Plugin Settings</a>",
					'flight-by-canto' ) . '</span></h3></form>';

			return false;

		else :


			?>
			<?php /* TODO: Next version, add Filter and Search
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
				</div> */
			?>
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

			<img src="<?php echo FBC_URL; ?>/assets/loader_white.gif" id="loader">



		<?php
			//endif;


		endif;
		//Stop checking to see if user has valid flight credentials

		// set the first part of the form action url now, to the current active site, to prevent X-Frame-Options problems
		$form_action_url = plugins_url( 'copy-media.php', __FILE__ );

		$post_id = intval( $_REQUEST['post_id'] );

		$form_action_url .= "?type=$type&tab=library&post_id=$post_id";
		$form_action_url = apply_filters( 'media_upload_form_url', $form_action_url, $type );

		$form_class = 'media-upload-form validate';


		$_GET['paged'] = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 0;
		if ( $_GET['paged'] < 1 ) {
			$_GET['paged'] = 1;
		}
		$start = ( $_GET['paged'] - 1 ) * 10;
		if ( $start < 1 ) {
			$start = 0;
		}
		add_filter( 'post_limits', create_function( '$a', "return 'LIMIT $start, 10';" ) );

		list( $post_mime_types, $avail_post_mime_types ) = wp_edit_attachments_query();
		?>

		<form id="filter" action="" method="get">
			<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>"/>
			<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>"/>
			<input type="hidden" name="post_id" value="<?php echo (int) $post_id; ?>"/>

			<input type="hidden" name="post_mime_type"
			       value="<?php echo isset( $_GET['post_mime_type'] ) ? esc_attr( $_GET['post_mime_type'] ) : ''; ?>"/>
			<?php if ( isset( $_GET['chromeless'] ) ) : ?>
				<input type="hidden" name="chromeless" value="<?php echo (bool) $_GET['chromeless']; ?>"/>
			<?php endif; ?>

			<style type="text/css">
				#media-upload #filter .nsm-site-select {
					float: none;
					width: 100%;
					margin: 0 1em 2em 1em;
					white-space: normal;
				}
			</style>


			<!--p id="media-search" class="search-box">
		<label class="screen-reader-text" for="media-search-input"><?php _e( 'Search Media' ); ?>:</label>
		<input type="text" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
		<?php submit_button( __( 'Search Media' ), 'button', '', false ); ?>
	</p-->

			<ul class="subsubsub" style="display:none">
				<?php
				$type_links = array();
				$_num_posts = (array) wp_count_attachments();
				$matches    = wp_match_mime_types( array_keys( $post_mime_types ), array_keys( $_num_posts ) );
				foreach ( $matches as $_type => $reals ) {
					foreach ( $reals as $real ) {
						if ( isset( $num_posts[ $_type ] ) ) {
							$num_posts[ $_type ] += $_num_posts[ $real ];
						} else {
							$num_posts[ $_type ] = $_num_posts[ $real ];
						}
					}
				}
				// If available type specified by media button clicked, filter by that type
				if ( empty( $_GET['post_mime_type'] ) && ! empty( $num_posts[ $type ] ) ) {
					$_GET['post_mime_type'] = $type;
					list( $post_mime_types, $avail_post_mime_types ) = wp_edit_attachments_query();
				}
				if ( empty( $_GET['post_mime_type'] ) || $_GET['post_mime_type'] == 'all' ) {
					$class = ' class="current"';
				} else {
					$class = '';
				}
				$type_links[] = "<li><a href='" . esc_url( add_query_arg( array(
						'post_mime_type' => 'all',
						'paged'          => false,
						'm'              => false
					) ) ) . "'$class>" . __( 'All Types' ) . "</a>";
				foreach ( $post_mime_types as $mime_type => $label ) {
					$class = '';

					if ( ! wp_match_mime_types( $mime_type, $avail_post_mime_types ) ) {
						continue;
					}

					if ( isset( $_GET['post_mime_type'] ) && wp_match_mime_types( $mime_type,
							$_GET['post_mime_type'] )
					) {
						$class = ' class="current"';
					}

					$type_links[] = "<li><a href='" . esc_url( add_query_arg( array(
							'post_mime_type' => $mime_type,
							'paged'          => false
						) ) ) . "'$class>" . sprintf( translate_nooped_plural( $label[2], $num_posts[ $mime_type ] ),
							"<span id='$mime_type-counter'>" . number_format_i18n( $num_posts[ $mime_type ] ) . '</span>' ) . '</a>';
				}
				echo implode( ' | </li>', apply_filters( 'media_upload_mime_type_links', $type_links ) ) . '</li>';
				unset( $type_links );
				?>
			</ul>

			<div class="tablenav" style="display:none">

				<?php
				$page_links = paginate_links( array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'prev_text' => __( '&laquo;' ),
					'next_text' => __( '&raquo;' ),
					'total'     => ceil( $wp_query->found_posts / 10 ),
					'current'   => $_GET['paged']
				) );

				if ( $page_links ) {
					echo "<div class='tablenav-pages'>$page_links</div>";
				}
				?>

				<div class="alignleft actions">
					<?php

					$arc_query = "SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY post_date DESC";

					$arc_result = $wpdb->get_results( $arc_query );

					$month_count = count( $arc_result );

					if ( $month_count && ! ( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) { ?>
						<select name='m'>
							<option<?php selected( @$_GET['m'], 0 ); ?>
								value='0'><?php _e( 'Show all dates' ); ?></option>
							<?php
							foreach ( $arc_result as $arc_row ) {
								if ( $arc_row->yyear == 0 ) {
									continue;
								}
								$arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );

								if ( isset( $_GET['m'] ) && ( $arc_row->yyear . $arc_row->mmonth == $_GET['m'] ) ) {
									$default = ' selected="selected"';
								} else {
									$default = '';
								}

								echo "<option$default value='" . esc_attr( $arc_row->yyear . $arc_row->mmonth ) . "'>";
								echo esc_html( $wp_locale->get_month( $arc_row->mmonth ) . " $arc_row->yyear" );
								echo "</option>\n";
							}
							?>
						</select>
					<?php } ?>

					<?php submit_button( __( 'Filter &#187;' ), 'secondary', 'post-query-submit', false ); ?>

				</div>

				<br class="clear"/>
			</div>
		</form>

		<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr( $form_action_url ); ?>"
		      class="<?php echo $form_class; ?>" id="library-form">

			<?php wp_nonce_field( 'media-form' ); ?>

			<?php
			if ( isset( $_GET['chromeless'] ) && $_GET['chromeless'] ):
				// WP3.5+ Media Browser calls iframe 'chromeless' and handles inserting differently
				?>
				<script type="text/javascript">
					/* <![CDATA[ */
					function fbc_media_send_to_editor(htmlString) {
						<?php /* copied from /wp-admin/includes/media.php media_send_to_editor() */ ?>
						var win = window.dialogArguments || opener || parent || top;
						win.send_to_editor(htmlString);
						document.getElementById("loader").style.display = "none";
					}
					jQuery(function ($) {
						$('input[id^=send]:submit').click(function (event) {
							event.preventDefault();
							var $this = $(event.target);
							var form = $('#library-form');
							var result = $.ajax({
								url: form.attr('action'),
								type: form.attr('method'),
								data: form.serialize() + '&' + encodeURIComponent($this.attr('id')) + '=true&chromeless=1',
								success: fbc_media_send_to_editor
								/*
								,error: function (xhr, ajaxOptions, thrownError) {
										   console.log(xhr.status);
										   console.log(xhr.responseText);
										   console.log(thrownError);
									   },
								*/
							});
						});
					});
					/* ]]> */
				</script>
			<?php endif; /* chromeless */ ?>

			<script type="text/javascript">
				<!--
				jQuery(function ($) {
					var preloaded = $(".media-item.preloaded");
					if (preloaded.length > 0) {
						preloaded.each(function () {
							prepareMediaItem({id: this.id.replace(/[^0-9]/g, '')}, '');
						});
						updateMediaForm();
					}
				});
				-->
			</script>


			<div id="media-items">
				<input id="fbc_id" name="fbc_id" type="hidden" value=""/>
				<input id="fbc_scheme" name="fbc_scheme" type="hidden" value=""/>
                <input id="abspath" name="abspath" type="hidden" value="<?php echo urlencode(ABSPATH); ?>"/>
                <input id="fbc_flight_domain" name="fbc_flight_domain" type="hidden" value="<?php echo get_option( 'fbc_flight_domain' ); ?>"/>
                <input id="fbc_app_token" name="fbc_app_token" type="hidden" value="<?php echo get_option( 'fbc_app_token' ); ?>"/>

				<div tabindex="0" data-id="0" class="fbc attachment-details save-ready">
					<h3>
						Attachment Details
                        <span class="settings-save-status">
                            <span class="spinner"></span>
                            <span class="saved"></span>
                        </span>
					</h3>

					<div class="attachment-info">
						<div class="thumbnail thumbnail-image">

							<img draggable="false"
							     src="">

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
					
					<label data-setting="copyright" class="setting">
						<span class="name">Copyright</span>
						<input type="text" id="copyright" name="copyright" value="">
					</label>
					
					<label data-setting="terms" class="setting">
						<span class="name">Terms & Conditions</span>
						<textarea id="terms" name="terms"></textarea>
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
							<select name="link" class="link-to" data-setting="link" data-user-setting="urlbutton">
								<option value="none" selected="">None</option>
								<option value="file">Media File</option>
								<option value="post">Attachment Page</option>
							</select>
						</label>
						<input type="text" class="link-to-custom hidden" data-setting="linkUrl">
					</div>

					<label class="setting">
						<span>Size</span>
						<?php

						/** This filter is documented in wp-admin/includes/media.php */
						$sizes = apply_filters( 'image_size_names_choose', array(
							'thumbnail' => __( 'Thumbnail' ),
							'medium'    => __( 'Medium' ),
							'large'     => __( 'Large' ),
							'full'      => __( 'Full Size' ),
						) );
						$thesizes = get_image_sizes();

						 foreach($thesizes as $key => $value){
							$thesizes[$key]['name'] = $sizes[$key];
						} ?> <?php //print_r($thesizes); ?>

						<select data-user-setting="imgsize" data-setting="size" name="size" class="size">
							<?php

							foreach ($thesizes as $value => $name) : ?>

								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, 'full' ); ?>>
									<?php echo esc_html( $name['name'] ); ?>

									<?php
										/*
										 * TODO: Calculate image dimensions
										 <?php echo esc_html( $name['name'] ); ?> &ndash; <?php echo $name['height'];?> &times; <?php echo $name['width']; ?>
										 */
									 ?>
								</option>
									<?php endforeach; ?>

						</select>
					</label>
					<a href="javascript:;" class="button" id="fbc_media-sidebar-cancel">Cancel</a> &nbsp;
					<?php echo get_submit_button( __( 'Insert into Post' ), 'button submit', "send[$post_id]", false ); ?>
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

	$sizes                        = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach ( $get_intermediate_image_sizes as $_size ) {

		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

			$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );

		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop']
			);

		}

	}

	// Get only 1 size if found
	if ( $size ) {
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}

	return $sizes;
}


function fbc_insert_custom_image_sizes( $sizes ) {
	global $_wp_additional_image_sizes;
	if ( empty( $_wp_additional_image_sizes ) ) {
		return $sizes;
	}

	foreach ( $_wp_additional_image_sizes as $id => $data ) {
		if ( ! isset( $sizes[ $id ] ) ) {
			$sizes[ $id ] = ucfirst( str_replace( '-', ' ', $id ) );
		}
	}

	return $sizes;
}

//Add custom image sized to thumbnail selection if the user hasnt already.
add_filter( 'image_size_names_choose', 'fbc_insert_custom_image_sizes' );

if ( ! has_filter( 'image_size_names_choose' ) ) {
	add_filter( 'image_size_names_choose', 'fbc_insert_custom_image_sizes' );
}



function get_meta_data() {

	$nonce = wp_create_nonce( 'flight-by-canto' );
	return;
}

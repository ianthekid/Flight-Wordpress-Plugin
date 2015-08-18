<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Flight_by_Canto_Settings {

	/**
	 * The single instance of Flight_by_Canto_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for Flight by Canto.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'fbc_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register Flight by Canto
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Flight by Canto', 'flight-by-canto' ) , __( 'Flight by Canto', 'flight-by-canto' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'flight-by-canto' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['standard'] = array(
			'title' => "Flight Settings!",
			'description'			=> __( 'Connect to your Flight instance using the Flight API. All fields are required, and you must be a Pilot to enable API access.<br><br><a href="https://www.canto.com/flight/api/">API Documentation</a> or <a href="https://www.canto.com/flight/request-api/">Request for API Access</a>', 'flight-by-canto' ),
			'fields'				=> array(
				array(
					'id' 			=> 'flight_domain',
					'label'			=> __( 'Flight Domain' , 'flight-by-canto' ),
					'description'	=> __( '.run.cantoflight.com', 'flight-by-canto' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'flightdomain', 'flight-by-canto' )
				),
				array(
					'id' 			=> 'app_id',
					'label'			=> __( 'App ID' , 'flight-by-canto' ),
					'description'	=> __( 'Your API Client ID', 'flight-by-canto' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'App ID', 'flight-by-canto' )
				),
				array(
					'id' 			=> 'app_secret',
					'label'			=> __( 'Secret Key' , 'flight-by-canto' ),
					'description'	=> __( 'Your API Secret Key', 'flight-by-canto' ),
					'type'			=> 'password',
					'default'		=> '',
					'placeholder'	=> __( 'Secret Key', 'flight-by-canto' )
				),
				array(
					'id' 			=> 'app_token',
					'label'			=> '',
					'description'	=> __( '', 'flight-by-canto' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Token', 'flight-by-canto' )
				)
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register Flight by Canto
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}
	
	public function oauth_token () {		
		$html = '<h2>' . __( 'Login to Flight Complete Authorization' , 'flight-by-canto' ) . '</h2>' . "\n";
		$html .= '<iframe onload="alert(this.contentWindow.location.href);"';
		$html .= ' src="https://oauth.staging.cantoflight.com:8443/oauth/api/oauth2/authorize?response_type=code&app_id=f38812b27dc24b1eabd2837e15b8f119&state=groovy" ';
		$html .= 'width="500" height="700"></iframe>';
		return $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {
		
		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Flight by Canto' , 'flight-by-canto' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'flight-by-canto' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;

		//Generate OAuth Token
		if( get_option( 'fbc_app_token' ) == '' && (get_option( 'fbc_flight_domain' ) != '' &&  get_option( 'fbc_app_id' ) != '' &&  get_option( 'fbc_app_secret' ) != '') ) {
			echo $this->oauth_token();
		}

	}

	/**
	 * Main Flight_by_Canto_Settings Instance
	 *
	 * Ensures only one instance of Flight_by_Canto_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Flight_by_Canto()
	 * @return Main Flight_by_Canto_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}

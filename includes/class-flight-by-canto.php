<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
function fbc_admin_notice__success() {
	?>
	<div class="notice notice-success">
		<p><?php _e( 'Done!', 'flight-by-canto' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'fbc_admin_notice__success' );
*/

class Flight_by_Canto {

	/**
	 * The single instance of Flight_by_Canto.
	 * @var    object
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 */
	public $_token;

	/**
	 * The flight authorization token.
	 * @var     string
	 */
	private $fbc_app_id;

	/**
	 * The flight authorization token.
	 * @var     string
	 */
	private $fbc_app_secret;

	/**
	 * The flight authorization token.
	 * @var     string
	 */
	private $fbc_app_token;

	/**
	 * The flight domain
	 * @var     string
	 */
	public $fbc_flight_domain;

	/**
	 * The authorization refresh token
	 * @var     string
	 */
	private $fbc_refresh_token;

	/**
	 * The Flight Password
	 * @var    string
	 */
	private $fbc_flight_password;

	/**
	 * The Flight Username
	 * @var    string
	 */
	private $fbc_flight_username;

	/**
	 * The main plugin file.
	 * @var     string
	 */
	public $file;

	/**
	 * The wordpress plugin directory.
	 * @var     string
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @return  void
	 */
	public function __construct( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token   = 'flight_by_canto';

		// Load plugin environment variables
		$this->file       = $file;
		$this->dir        = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$this->fbc_app_token       = get_option( 'fbc_app_token' );
		$this->fbc_flight_domain   = get_option( 'fbc_flight_domain' );
		$this->fbc_refresh_token   = get_option( 'fbc_refresh_token' );
		$this->fbc_flight_username = get_option( 'fbc_flight_username' );

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );





		/*
		 * ToDo: separate outside of Constructor
		 */
		function md_modify_jsx_tag( $tag, $handle, $src ) {
		  // Check that this is output of JSX file
		  if( strstr($handle,'react') != FALSE ) {
			  $tag = str_replace( "<script type='text/javascript'", "<script type='text/jsx'", $tag );
		  }

		  return $tag;
		}
		add_filter( 'script_loader_tag', 'md_modify_jsx_tag', 10, 3 );

		/* end additions to Constructor */





		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Flight_by_Canto_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Add Ajax functions
		add_action( 'wp_ajax_fbc_get_token', array( $this, 'getToken' ) );
		//add_action( 'wp_ajax_fbc_refresh_token', array( $this, 'refreshToken' ) );
		add_action( 'wp_ajax_fbc_getMetadata', array( $this, 'getMetadata' ) );

	} // End __construct ()


	public function getMetaData( $fbc_id ) {
		check_ajax_referer( 'flight-by-canto', 'nonce' );

		if ( empty ( $fbc_id ) ) {
			$fbc_id = stripslashes( htmlspecialchars( $_POST['fbc_id'] ) );
		} else {
			echo "Passsed by value: ";
			$fbc_id = stripslashes( htmlspecialchars( $fbc_id ) );
		}

		$flight['api_url'] = 'https://' . $this->fbc_flight_domain . '.cantoflight.com/api/v1/';


//Get the metadata from the server to send off the the library form.
		$result = $this->curl_action( $flight['api_url'] . 'image/' . $fbc_id, 0 );

		//var_dump($result); wp_die();
		$result = json_decode( $result );


		//Build out the array
		$data = array(
			'id'         => $fbc_id,
			'name'       => $result->name,
			'dimensions' => $result->default->{'Dimensions'},
			'mime'       => $result->default->{'Content Type'},
			'size'       => size_format( $result->size ),
			'uploaded'   => $result->lastUploaded
		);

		echo json_encode( $data );
		wp_die();
	}

	/*
	 * Used to authenticate the Grant access to the app and get the token
	 */

	public function getToken() {
		//authenticate to OATUH -- Need to save the Session Cookie from Set Cookie

		$req = "https://oauth.cantoflight.com:8443/oauth/rest/oauth2/authenticate";
		$postfields = "tenant=" . $this->fbc_flight_domain . '.cantoflight.com&user=' . $this->fbc_flight_username;
		$postfields .= '&password=' . $this->fbc_flight_password;


		if ( ! function_exists( 'curl_init' ) ) {
			die( 'Sorry cURL is not installed!' );
		}

		$ch = curl_init();

		$options = array(
			CURLOPT_URL            => $req,
			CURLOPT_REFERER        => get_bloginfo( 'url' ),
			CURLOPT_USERAGENT      => "Flight Wordpress Plugin",
			CURLOPT_HTTPHEADER     => array(),
			//CURLOPT_SSLVERSION     => 3,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HEADER         => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $postfields,
		);

		curl_setopt_array( $ch, $options );
		$response = curl_exec( $ch );

//		var_dump(curl_error($ch));


		list( $httpheader ) = explode( "\r\n\r\n", $response);

		//Check to see if the user supplied proper credentials, return error
		$invalid_credentials = $matches = array();
		preg_match('/(.*?)401/',$httpheader, $invalid_credentials);
			if (count($invalid_credentials) > 0) { echo json_encode( array( 'error' => "Invalid Login Credentials")); wp_die(); }

		//The DAM Credentials are working
		preg_match( '/(Set-Cookie:)(.*?);.*\n/', $httpheader, $matches );
		$cookie = preg_replace( '/Set-Cookie: (.*?);.*/', '\\1', $matches[0], 1 );

		//Now we have the authorization cookie and we can proceed to get the authorization code


		$options[ CURLOPT_URL ] = "https://oauth.cantoflight.com:8443/oauth/rest/oauth2/grant";
		$options[ CURLOPT_URL ] .= "?action=grant&response_type=code&app_id=" . $this->fbc_app_id ;//. "&app_secret=" . $this->fbc_app_secret;
		$options[ CURLOPT_COOKIE ] = $cookie;
		$options[ CURLOPT_POST ]   = false;
		unset( $options[ CURLOPT_POSTFIELDS ] );
		curl_setopt_array( $ch, $options );
		$response = curl_exec( $ch );

		//Check to see if the proper code/cookie are in place.

		$invalid_credentials = $matches = array();
		preg_match('/(.*?)400/',$httpheader, $invalid_credentials);
			if (count($invalid_credentials) > 0) { echo json_encode( array( 'error' => "Invalid AppID")); wp_die(); }

		//Now we have the header again which contains the location (aka the code);

		list( $httpheader ) = explode( "\r\n\r\n", $response, 2 );
		preg_match( '/Location:(.*?)\n/', $httpheader, $matches );
		$code = preg_replace( '/^.*code\=(.*?)&.*/', '\\1', $matches[0], 1 );
		//we have a DAM code! make the final request to get the token


		$options[ CURLOPT_URL ] = "https://oauth.cantoflight.com:8443/oauth/api/oauth2/token";
		$options[ CURLOPT_URL ] .= "?app_id=" . $this->fbc_app_id . "&app_secret=" . $this->fbc_app_secret . "&grant_type=authorization_code&code=" . trim($code);
		$options[ CURLOPT_POST ]   = true;
		$options[ CURLOPT_HEADER ] = 0;
		curl_setopt_array( $ch, $options );
		$response = curl_exec( $ch );

		//no more curl! the json is stored in the response var
		curl_close( $ch );

		//now set the DAM Authentication tokens
		$response = json_decode( $response );
		update_option( 'fbc_app_token', $response->accessToken );
		update_option( 'fbc_app_refresh_token', $response->refreshToken );
		update_option( 'fbc_app_token_expire', time() + $response->expiresIn );
		update_option( 'fbc_app_refresh_token_expire', time() + ( 86400 * 365 ) );

		//var_dump($response);
	}

	/**
	 * Refreshes the current token saved in options via the Refresh Token
	 */
	public function refreshToken() {
//		check_ajax_referer('flight-by-canto-refresh-token', 'nonce');
		//Need to check if we have the tools needed to refresh the token
//		if ( ! get_option('fbc_app_secret') || ! get_option('fbc_flight_domain') || !get_option('fbc_app_id')){

		$req    = 'https://' . $this->fbc_flight_domain . '.cantoflight.com:8443/oauth/api/oauth2/token';
		$header = 'app_id=' . $this->fbc_app_id . '&app_secret=' . $this->fbc_app_secret
		          . '&grant_type=refresh_token&refresh_token=' . $this->fbc_app_refresh_token;
		$agent  = "Flight Wordpress Plugin";
//var_dump($req.'?'.$header); wp_die();
		$response = $this->curl_action( $req . '?' . $header,
			array( 'Authorization: Bearer ' . $this->fbc_app_refresh_token ), $agent, 1 );
		$response = json_decode( $response );
		update_option( 'fbc_app_token', $response['accessToken'] );
		update_option( 'fbc_app_refresh_token', $response['refreshToken'] );
		update_option( 'fbc_app_expire_token', time() + $response['expiresIn'] );
//		}

//		echo "Fail";
	}


	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(),
			$this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->_token . '-frontend',
			esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ),
			$this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(),
			$this->_version );
		wp_enqueue_style( $this->_token . '-admin' );

		wp_enqueue_script( 'fbc_media_js', plugins_url() . '/flight-by-canto/assets/js/admin.js' );

		wp_register_script( 'fbc-js', 'https://cdnjs.cloudflare.com/ajax/libs/react/0.13.1/react.min.js' );
		wp_register_script( 'fbc-jsx', 'https://cdnjs.cloudflare.com/ajax/libs/react/0.13.1/JSXTransformer.js' );

		wp_enqueue_script ( 'fbc-js' );
		wp_enqueue_script ( 'fbc-jsx' );

		$translation_array = array(
			'FBC_URL' 	=> FBC_URL,
			'FBC_PATH' 	=> FBC_PATH,
			'subdomain' => get_option( 'fbc_flight_domain' ),
			'token'		=> get_option( 'fbc_app_token' ),
			'limit'		=> 30,
			'start'		=> 0
		);
		$path_to_script = FBC_URL .'assets/js/images.js';
	 	wp_register_script( 'react-loop', $path_to_script );
		wp_localize_script( 'react-loop', 'args', $translation_array );
		wp_enqueue_script ( 'react-loop' );

		$path_to_script_a = FBC_URL .'assets/js/attachment.js';
		wp_register_script( 'react-attachment', $path_to_script_a );
		wp_enqueue_script ( 'react-attachment' );

		$path_to_script_c = FBC_URL .'assets/js/tree.js';
		wp_register_script( 'react-tree', $path_to_script_c );
		wp_localize_script( 'react-tree', 'args', $translation_array );
		wp_enqueue_script ( 'react-tree' );

		$path_to_script_c = FBC_URL .'assets/js/search.js';
		wp_register_script( 'react-search', $path_to_script_c );
		wp_localize_script( 'react-search', 'args', $translation_array );
		wp_enqueue_script ( 'react-search' );

		$path_to_script_d = FBC_URL .'assets/js/fbc.js';
		wp_register_script( 'react-main', $path_to_script_d );
		wp_localize_script( 'react-main', 'args', $translation_array );
		wp_enqueue_script ( 'react-main' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		wp_register_script( $this->_token . '-admin',
			esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ),
			$this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'flight-by-canto', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		$domain = 'flight-by-canto';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Flight_by_Canto Instance
	 *
	 * Ensures only one instance of Flight_by_Canto is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Flight_by_Canto()
	 * @return Main Flight_by_Canto instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}

		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}

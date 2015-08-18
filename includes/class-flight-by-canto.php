<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Flight_by_Canto {

	/**
	 * The single instance of Flight_by_Canto.
	 * @var 	object
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
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'flight_by_canto';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Flight_by_Canto_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Add Ajax functions
		add_action( 'wp_ajax_fbc_getMetadata', array($this, 'getMetadata') );

	} // End __construct ()
	

	/**
	 * CURL function to query Flight API
	 * @param  string $url		   	Full Flight API query string
	 * @param  string $header		Flight API token authorization
	 * @param  string $agent      	Standard browser agent for CURL requests
	 * @param  int $echo 			True/False (1/0) for including CURL header in output
	 * @return object              	CURL response output
	 */
	public function curl_action($url,$header,$agent,$echo) {
	 
		if (!function_exists('curl_init')){
			die('Sorry cURL is not installed!');
		}
		
		$ch = curl_init();
	
		$options = array( 
			CURLOPT_URL				=> $url,				// get request
			CURLOPT_REFERER			=> get_bloginfo('url'), // who r u
			CURLOPT_USERAGENT		=> $agent,				// who am i
			CURLOPT_HTTPHEADER		=> $header,				// provides authorization and token
			CURLOPT_SSLVERSION		=> 3,					// required for api handshake
			CURLOPT_HEADER			=> $echo,				// include header in output?
			CURLOPT_RETURNTRANSFER		=> 1,					// output as string instead of file
			CURLOPT_TIMEOUT			=> 10,					// how long til i give up?
		);
		
		curl_setopt_array($ch,$options);
		$output = curl_exec($ch);
		curl_close($ch);	
	
		return $output;
	}

	public function getMetaData($fbc_id  ){
  	  check_ajax_referer( 'flight-by-canto','nonce');
//	var_dump(empty($fbc_id));
//var_dump($fbc_id);
//	wp_die();
	  if ( empty ( $fbc_id ) ) {
	  	$fbc_id = stripslashes(htmlspecialchars($_POST['fbc_id']));
	  } else {
	    echo "Passsed by value: ";	
	    $fbc_id = stripslashes(htmlspecialchars($fbc_id));
	  }
	  $flight['token']        = '18a91e5134f54e78a1138ad26800df4a';
	  $flight['token'] = get_option('fbc_app_token');
	  $flight['header']       = array('Authorization: Bearer '.$flight['token']);
	  $flight['agent']        = 'Canto Dev Team';
	
	  $flight['api_url'] = 'https://'. get_option('fbc_flight_domain') .'.staging.cantoflight.com/api/v1/';

	
//Get the metadata from the server to send off the the library form.
	  $result = $this->curl_action($flight['api_url'].'image/'.$fbc_id, $flight['header'], $flight['agent'],0);

	  $result = json_decode($result);
	//Build out the array
//print_r( $result);	
//	echo( $result->metadata->{'Image Width'});i
/*
	$data =  array(
		'height' 	=> $result->metadata->{'Horizontal Pixels'},
		'width' 	=> $result->metadata->{'Vertical Pixels'},
//		'mime' 		=> $result->metadata->{'MIME Type'},
		'height' 	=> $result->metadata->{'Horizontal Pixels'}
	);
*/
		//print_r(json_encode($data));
	  //echo $_POST['fbc_id'];
 	//echo json_encode($result->metadata);
 	echo json_encode($result);
	  wp_die();
	}

	/**
	 * Multi-Threaded CURL function to loop through Flight API response, and request multiple items
	 * @param  string $data		   	Full Flight API query string
	 * @param  string $header		Flight API token authorization
	 * @param  string $agent      	Standard browser agent for CURL requests
	 * @param  int $echo 			True/False (1/0) for including CURL header in output
	 * @return object              	CURL response output + associative File ID and Name
	 */
	public function multiRequest($data, $options = array()) {
	 
	  // array of curl handles
	  $curly = array();
	  // data to be returned
	  $result = array();
	 
	  // multi handle
	  $mh = curl_multi_init();
	 
	  // loop through $data and create curl handles
	  // then add them to the multi-handle
	  foreach ($data as $id => $d) {
		  
	
		$curly[$id]['img'] 	= curl_init();
		$curly[$id]['id']	= $d['id'];
		$curly[$id]['name'] = $d['name'];
	
	
		$url = (is_array($d) && !empty($d['preview'])) ? $d['preview'] : $d;
		
		//$imgUrl = 'https://obj.run.cantoflight.com/api_binary/v1/image/'.$url.'/preview';
		
		curl_setopt($curly[$id]['img'], CURLOPT_URL,$url);
		//curl_setopt($curly[$id]['img'], CURLOPT_HTTPHEADER, array('Authorization: Bearer 18a91e5134f54e78a1138ad26800df4a') );
		curl_setopt($curly[$id]['img'], CURLOPT_HTTPHEADER, array('Authorization: Bearer '. get_option('fbc_app_token')) );
		curl_setopt($curly[$id]['img'], CURLOPT_USERAGENT, 'Dev Team' );
		curl_setopt($curly[$id]['img'], CURLOPT_HEADER,1);
		curl_setopt($curly[$id]['img'], CURLOPT_SSLVERSION,3);
		curl_setopt($curly[$id]['img'], CURLOPT_RETURNTRANSFER, 1);
	 
		// post?
		if (is_array($d)) {
		  if (!empty($d['post'])) {
			curl_setopt($curly[$id]['img'], CURLOPT_POST,       1);
			curl_setopt($curly[$id]['img'], CURLOPT_POSTFIELDS, $d['post']);
		  }
		}
	 
		// extra options?
		if (!empty($options)) {
		  curl_setopt_array($curly[$id]['img'], $options);
		}
	 
		curl_multi_add_handle($mh, $curly[$id]['img']);
	  }
	 
	  // execute the handles
	  $running = null;
	  do {
		curl_multi_exec($mh, $running);
	  } while($running > 0);
	 
	
	  // get content and remove handles
	  foreach($curly as $idRes) {
		array_push($result,array('img' => curl_multi_getcontent($idRes['img']), 'id' => $idRes['id'], 'name' => $idRes['name']));
		curl_multi_remove_handle($mh, $idRes['img']);
	  }
	 
	  // all done
	  curl_multi_close($mh);
	 
	  return $result;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'flight-by-canto', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
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
	public static function instance ( $file = '', $version = '1.0.0' ) {
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
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}


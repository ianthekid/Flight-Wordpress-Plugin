<?php
/*
 * Plugin Name: Flight by Canto
 * Version: 1.2.3
 * Plugin URI: https://www.canto.com/flight/
 * Description: Easily find and publish your creative assets directly to wordpress without having to search through emails or folders.
 * Author: Canto Inc
 * Author URI: https://www.canto.com/
 * Requires at least: 4.0
 * Tested up to: 4.5-RC1
 *
 * Text Domain: flight-by-canto
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Canto
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FBC_PATH', plugin_dir_path(__FILE__) );
define( 'FBC_URL', plugin_dir_url(__FILE__) );
define( 'FBC_DIR', plugin_basename( __FILE__ ) );

// Load plugin class files
require_once( 'includes/class-flight-by-canto.php' );
require_once( 'includes/class-flight-by-canto-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-flight-by-canto-admin-api.php' );
require_once( 'includes/lib/class-flight-by-canto-media.php' );

/**
 * Returns the main instance of Flight_by_Canto to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Flight_by_Canto
 */
function Flight_by_Canto () {
	$instance = Flight_by_Canto::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Flight_by_Canto_Settings::instance( $instance );
	}

	return $instance;
}

Flight_by_Canto();

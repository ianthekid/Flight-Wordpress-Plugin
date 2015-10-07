<?php
/*
 * Plugin Name: Flight by Canto
 * Version: 1.0.3
 * Plugin URI: http://www.canto.com/flight/
 * Description: Pull in photos, images and graphics from your Flight account
 * Author: Canto Inc
 * Author URI: http://www.canto.com/
 * Requires at least: 4.0
 * Tested up to: 4.3
 *
 * Text Domain: flight-by-canto
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Canto
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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

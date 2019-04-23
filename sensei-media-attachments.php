<?php
/*
 * Plugin Name: Sensei Media Attachments
 * Version: 2.0.0
 * Plugin URI: https://woocommerce.com/products/sensei-media-attachments/
 * Description: Provide your students with easy access to additional learning materials, from audio files to slideshows and PDFs.
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Requires at least: 3.5
 * Tested up to: 5.1
 * Requires PHP: 5.6
 * Woo: 290551:788647a9a1d8ef5c95371f0e69223a0f
 *
 * @package WordPress
 * @author Automattic
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SENSEI_MEDIA_ATTACHMENTS_VERSION', '2.0.0' );
define( 'SENSEI_MEDIA_ATTACHMENTS_PLUGIN_FILE', __FILE__ );
define( 'SENSEI_MEDIA_ATTACHMENTS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once dirname( __FILE__ ) . '/classes/class-sensei-media-attachments-dependency-checker.php';

if ( ! Sensei_Media_Attachments_Dependency_Checker::are_system_dependencies_met() ) {
	return;
}

require_once dirname( __FILE__ ) . '/classes/class-sensei-media-attachments.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', array( 'Sensei_Media_Attachments', 'init' ), 5 ) ;

Sensei_Media_Attachments::instance();

<?php
/*
 * Plugin Name: Sensei Media Attachments
 * Version: 1.0.1
 * Plugin URI: https://woocommerce.com/products/sensei-media-attachments/
 * Description: Enhance your lessons by attaching media files to lessons and courses in Sensei
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Requires at least: 3.5
 * Tested up to: 3.8
 * Woo: 290551:788647a9a1d8ef5c95371f0e69223a0f
 *
 * @package WordPress
 * @author Automattic
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


require_once dirname( __FILE__ ) . '/classes/class-sensei-media-attachments-dependency-checker.php';

if ( ! Sensei_Media_Attachments_Dependency_Checker::are_dependencies_met() ) {
	return;
}

require_once( 'classes/class-sensei-media-attachments.php' );

global $sensei_media_attachments;
$sensei_media_attachments = new Sensei_Media_Attachments( __FILE__ );

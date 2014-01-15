<?php
/*
 * Plugin Name: Sensei Media Attachments
 * Version: 1.0.0
 * Plugin URI: http://www.woothemes.com/
 * Description: Enhance your lessons by attaching media files to lessons and courses in Sensei
 * Author: WooThemes
 * Author URI: http://www.woothemes.com/
 * Requires at least: 3.5
 * Tested up to: 3.8
 *
 * @package WordPress
 * @author WooThemes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '788647a9a1d8ef5c95371f0e69223a0f', '290551' );

/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WooThemes_Sensei_Dependencies' ) ) {
	require_once 'woo-includes/class-woothemes-sensei-dependencies.php';
}

/**
 * Sensei Detection
 */
if ( ! function_exists( 'is_sensei_active' ) ) {
  function is_sensei_active() {
    return WooThemes_Sensei_Dependencies::sensei_active_check();
  }
}

if( is_sensei_active() ) {
	require_once( 'classes/class-sensei-media-attachments.php' );

	global $sensei_media_attachments;
	$sensei_media_attachments = new Sensei_Media_Attachments( __FILE__ );
}
<?php
/*
 * Plugin Name: Sensei Media Attachments
 * Version: 1.0.0
 * Plugin URI: http://www.woothemes.com/
 * Description: Attach media files to lessons and courses in Sensei
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

require_once( 'classes/class-sensei-media-attachments.php' );

global $sensei_media_attachments;
$sensei_media_attachments = new Sensei_Media_Attachments( __FILE__ );
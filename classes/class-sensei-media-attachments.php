<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Sensei_Media_Attachments {
	private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;
	private $token;

	public function __construct( $file ) {
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		$this->token = 'sensei_media_attachments';

		// Localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Admin JS
		add_action( 'admin_enqueue_scripts' , array( $this, 'enqueue_admin_scripts' ) , 10 );

		// Meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_media_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_media_meta_box' ) );

		// Media files display
		add_action( 'sensei_course_single_lessons', array( $this, 'display_attached_media' ), 9 );
		add_action( 'sensei_lesson_single_meta', array( $this, 'display_attached_media' ), 1 );
	}

	/**
	 * Load admin JS
	 * @return void
	 */
	public function enqueue_admin_scripts () {
		global $wp_version;

		if( $wp_version >= 3.5 ) {
			// Load admin JS
			wp_register_script( 'sensei-media-attachments-admin', esc_url( $this->assets_url . 'js/admin.js' ), array( 'jquery' ), '1.0.0' );
			wp_enqueue_script( 'sensei-media-attachments-admin' );

			// Localise Javacript text strings
			$localised_data = array(
				'upload_file' => __( 'Upload File' , 'sensei_media_attachments' ),
				'choose_file' => __( 'Choose a file', 'sensei_media_attachments' ),
				'add_file'	  => __( 'Add file', 'sensei_media_attachments' )
			);
			wp_localize_script( 'sensei-media-attachments-admin', 'sensei_media_attachments_localisation', $localised_data );

			// Load media uploader scripts
			wp_enqueue_media();
		}

	}

	/**
	 * Add metaboxes to course and lesson edit pages
	 * @return void
	 */
	public function add_media_meta_box() {
		add_meta_box( 'course-media', __( 'Course Media', 'sensei_media_attachments' ), array( $this, 'media_meta_box' ), 'course', 'normal', 'high' );
		add_meta_box( 'lesson-media', __( 'Lesson Media', 'sensei_media_attachments' ), array( $this, 'media_meta_box' ), 'lesson', 'normal', 'high' );
	}

	/**
	 * Load meta box content
	 * @return void
	 */
	public function media_meta_box() {
		global $post_id;

		$media = get_post_meta( $post_id, '_attached_media', true );

		$html = '<input type="hidden" name="' . esc_attr( $this->token . '_nonce' ) . '" id="' . esc_attr( $this->token . '_nonce' ) . '" value="' . esc_attr( wp_create_nonce( plugin_basename( $this->file ) ) ) . '" />';

		$html .= '<table class="form-table" id="sensei_media_attachments">' . "\n";
		$html .= '<tbody>' . "\n";

		$c = 0;
		if( isset( $media ) && is_array( $media ) && count( $media ) > 0 ) {
			foreach( $media as $k => $file ) {
				if( $c == 0 ) {
					$html .= '<tr valign="top">' . "\n";
				}
				$html .= '<td><input type="button" id="sensei_media_attachments_' . $k . '_button" class="button upload_media_file_button" value="'. __( 'Upload File' , 'sensei_media_attachments' ) . '" data-uploader_title="' . __( 'Choose a file', 'sensei_media_attachments' ) . '" data-uploader_button_text="' . __( 'Add file', 'sensei_media_attachments' ) . '" /> <input name="sensei_media_attachments[]" type="text" id="sensei_media_attachments_' . $k . '" value="' . $file . '" /></td>' . "\n";
				$c++;
				if( $c == 2 ) {
					$html .= '</tr>' . "\n";
					$c = 0;
				}
			}
		}

		if( $c == 1 ) {
			$html .= '<td><input type="button" id="sensei_media_attachments_extra_button" class="button upload_media_file_button" value="'. __( 'Upload File' , 'sensei_media_attachments' ) . '" data-uploader_title="' . __( 'Choose a file', 'sensei_media_attachments' ) . '" data-uploader_button_text="' . __( 'Add file', 'sensei_media_attachments' ) . '" /> <input name="sensei_media_attachments[]" type="text" id="sensei_media_attachments_extra" value="" /></td>' . "\n";
			$html .= '</tr>' . "\n";
		}

		$html .= '<tr valign="top">' . "\n";
		$html .= '<td><input type="button" id="sensei_media_attachments_one_button" class="button upload_media_file_button" value="'. __( 'Upload File' , 'sensei_media_attachments' ) . '" data-uploader_title="' . __( 'Choose a file', 'sensei_media_attachments' ) . '" data-uploader_button_text="' . __( 'Add file', 'sensei_media_attachments' ) . '" /> <input name="sensei_media_attachments[]" type="text" id="sensei_media_attachments_one" value="" /></td>' . "\n";
		$html .= '<td><input type="button" id="sensei_media_attachments_two_button" class="button upload_media_file_button" value="'. __( 'Upload File' , 'sensei_media_attachments' ) . '" data-uploader_title="' . __( 'Choose a file', 'sensei_media_attachments' ) . '" data-uploader_button_text="' . __( 'Add file', 'sensei_media_attachments' ) . '" /> <input name="sensei_media_attachments[]" type="text" id="sensei_media_attachments_two" value="" /></td>' . "\n";
		$html .= '</tr>' . "\n";

		$html .= '<tr id="sensei_media_attachments_new_row" colspan="1" valign="top">' . "\n";
		$html .= '<td><a class="button-secondary" id="sensei_media_attachments_add_row">'. __( '+ Add more files' , 'sensei_media_attachments' ) . '</a></td>' . "\n";
		$html .= '</tr>' . "\n";

		$html .= '</tbody>' . "\n";
		$html .= '</table>' . "\n";

		echo $html;
	}

	/**
	 * Save meta box content
	 * @param  int $post_id ID of post
	 * @return void
	 */
	public function save_media_meta_box( $post_id ) {
		global $post;

		// Verify nonce
		if ( ! in_array( get_post_type(), array( 'lesson', 'course' ) ) || ! wp_verify_nonce( $_POST[ $this->token . '_nonce' ], plugin_basename( $this->file ) ) ) {
			return $post_id;
		}

		// Get post type object
		$post_type = get_post_type_object( $post->post_type );

		// Check if the current user has permission to edit the post
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		// Save array of media files
		if( isset( $_POST['sensei_media_attachments'] ) && is_array( $_POST['sensei_media_attachments'] ) && count( $_POST['sensei_media_attachments'] ) > 0 ) {
			$media = array();
			foreach( $_POST['sensei_media_attachments'] as $k => $file ) {
				if( $file && strlen( $file ) > 0 ) {
					$media[ $k ] = $file;
				}
			}
			update_post_meta( $post_id, '_attached_media', $media );
		}
	}

	/**
	 * Display attached media files on single lesson & course pages
	 * @return void
	 */
	public function display_attached_media() {
		global $post;

		$media = get_post_meta( $post->ID, '_attached_media', true );

		$html = '';

		$post_type = ucfirst( get_post_type( $post ) );

		if( $media && is_array( $media ) && count( $media ) > 0 ) {
			$html .= '<div id="attached-media">';
				$html .= '<h2>' . sprintf( __( '%s Media', 'sensei_media_attachments' ), $post_type ) . '</h2>';
				$html .= '<ul>';
					foreach( $media as $k => $file ) {
						$file_parts = explode( '/', $file );
		    			$file_name = array_pop( $file_parts );
						$html .= '<li id="attached_media_' . $k . '"><a href="' . esc_url( $file ) . '" target="_blank">' . esc_html( $file_name ) . '</a></li>';
					}
				$html .= '</ul>';
			$html .= '</div>';
		}

		echo $html;
	}

	/**
	 * Load localisation
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'sensei_media_attachments', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Load plguin textdomain
	 * @return void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'sensei_media_attachments';

	    $locale = apply_filters( 'plugin_locale' , get_locale() , $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

}
<?php
/**
 * Sensei Media Attachments Extension
 *
 * @package sensei-media-attachments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Media Attachment Extension main class.
 */
class Sensei_Media_Attachments {
	/**
	 * The single instance of self.
	 *
	 * @var    self
	 * @access private
	 * @static
	 * @since  2.0.0
	 */
	private static $instance = null;

	/**
	 * Assets directory path.
	 *
	 * @var string
	 */
	private $assets_dir;

	/**
	 * Assets directory public URL.
	 *
	 * @var string
	 */
	private $assets_url;

	/**
	 * Plugin prefix.
	 *
	 * @var string
	 */
	private $token;

	/**
	 * Sensei_Media_Attachments constructor.
	 */
	private function __construct() {
		$this->assets_dir = trailingslashit( dirname( SENSEI_MEDIA_ATTACHMENTS_PLUGIN_FILE ) ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', SENSEI_MEDIA_ATTACHMENTS_PLUGIN_FILE ) ) );
		$this->token      = 'sensei_media_attachments';

		$this->load_plugin_textdomain();
	}

	/**
	 * Set up all actions and filters.
	 */
	public static function init() {
		global $sensei_media_attachments;

		$instance = self::instance();
		add_action( 'init', array( $instance, 'load_localisation' ), 0 );

		if ( ! Sensei_Media_Attachments_Dependency_Checker::are_plugin_dependencies_met() ) {
			return;
		}

		// Set the global only if plugin dependencies are met.
		$sensei_media_attachments = $instance;

		add_action( 'init', array( $instance, 'frontend_hooks' ), 15 );

		// Admin JS.
		add_action( 'admin_enqueue_scripts', array( $instance, 'enqueue_admin_scripts' ), 10 );

		// Meta boxes.
		add_action( 'add_meta_boxes', array( $instance, 'add_media_meta_box' ) );
		add_action( 'save_post', array( $instance, 'save_media_meta_box' ) );
	}

	/**
	 * All frontend hooks.
	 */
	public function frontend_hooks() {
		// Media files display.
		add_action( 'sensei_single_lesson_content_inside_after', array( $this, 'display_attached_media' ), 35 );
		add_action( 'sensei_single_course_content_inside_before', array( $this, 'display_attached_media' ), 35 );
	}

	/**
	 * Load admin JS
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		// Load admin JS.
		wp_register_script( 'sensei-media-attachments-admin', esc_url( $this->assets_url . 'js/admin.js' ), array( 'jquery' ), SENSEI_MEDIA_ATTACHMENTS_VERSION, true );
		wp_enqueue_script( 'sensei-media-attachments-admin' );

		// Localise Javacript text strings.
		$localised_data = array(
			'upload_file' => esc_html__( 'Upload File', 'sensei-media-attachments' ),
			'choose_file' => esc_html__( 'Choose a file', 'sensei-media-attachments' ),
			'add_file'    => esc_html__( 'Add file', 'sensei-media-attachments' ),
		);
		wp_localize_script( 'sensei-media-attachments-admin', 'sensei_media_attachments_localisation', $localised_data );

		// Load media uploader scripts.
		wp_enqueue_media();

		wp_enqueue_style( 'sensei-media-attachments-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), SENSEI_MEDIA_ATTACHMENTS_VERSION );
	}

	/**
	 * Add metaboxes to course and lesson edit pages
	 *
	 * @return void
	 */
	public function add_media_meta_box() {
		add_meta_box( 'course-media', __( 'Course Media', 'sensei-media-attachments' ), array( $this, 'media_meta_box' ), 'course', 'normal', 'high' );
		add_meta_box( 'lesson-media', __( 'Lesson Media', 'sensei-media-attachments' ), array( $this, 'media_meta_box' ), 'lesson', 'normal', 'high' );
	}

	/**
	 * Load meta box content
	 *
	 * @return void
	 */
	public function media_meta_box() {
		global $post_id;

		$media = get_post_meta( $post_id, '_attached_media', true );

		$html = '<input type="hidden" name="' . esc_attr( $this->token . '_nonce' ) . '" id="' . esc_attr( $this->token . '_nonce' ) . '" value="' . esc_attr( wp_create_nonce( SENSEI_MEDIA_ATTACHMENTS_PLUGIN_BASENAME ) ) . '" />';

		$html .= '<table class="form-table" id="sensei_media_attachments">' . "\n";
		$html .= '<tbody>' . "\n";

		if ( isset( $media ) && is_array( $media ) && count( $media ) > 0 ) {
			foreach ( $media as $k => $file ) {
				$html .= '<tr valign="top">' . "\n";
				$html .= '<td><input type="button" id="sensei_media_attachments_' . esc_attr( $k ) . '_button" class="button upload_media_file_button" value="' . esc_attr__( 'Upload File', 'sensei-media-attachments' ) . '" data-uploader_title="' . esc_attr__( 'Choose a file', 'sensei-media-attachments' ) . '" data-uploader_button_text="' . esc_attr__( 'Add file', 'sensei-media-attachments' ) . '" /> <input name="sensei_media_attachments[]" class="sensei_media_attachments_file_input" type="text" id="sensei_media_attachments_' . esc_attr( $k ) . '" value="' . esc_url( $file ) . '" /></td>' . "\n";
				$html .= '</tr>' . "\n";
			}
		}

		$html .= '<tr valign="top">' . "\n";
		$html .= '<td><input type="button" id="sensei_media_attachments_two_button" class="button upload_media_file_button" value="' . esc_attr__( 'Upload File', 'sensei-media-attachments' ) . '" data-uploader_title="' . esc_attr__( 'Choose a file', 'sensei-media-attachments' ) . '" data-uploader_button_text="' . esc_attr__( 'Add file', 'sensei-media-attachments' ) . '" /> <input name="sensei_media_attachments[]" class="sensei_media_attachments_file_input" type="text" id="sensei_media_attachments_two" value="" /></td>' . "\n";
		$html .= '</tr>' . "\n";

		$html .= '<tr id="sensei_media_attachments_new_row" colspan="1" valign="top">' . "\n";
		$html .= '<td><a class="button-secondary" id="sensei_media_attachments_add_row">' . esc_html__( '+ Add more files', 'sensei-media-attachments' ) . '</a></td>' . "\n";
		$html .= '</tr>' . "\n";

		$html .= '</tbody>' . "\n";
		$html .= '</table>' . "\n";

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- User data escaped above.
	}

	/**
	 * Save meta box content
	 *
	 * @param  int $post_id ID of post.
	 * @return int|void
	 */
	public function save_media_meta_box( $post_id ) {
		global $post;

		// Verify nonce.
		if ( ! in_array( get_post_type(), array( 'lesson', 'course' ), true ) ||
			! isset( $_POST[ $this->token . '_nonce' ] ) ||
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Don't transform nonce.
			! wp_verify_nonce( $_POST[ $this->token . '_nonce' ], SENSEI_MEDIA_ATTACHMENTS_PLUGIN_BASENAME ) ) {
			return $post_id;
		}

		// Get post type object.
		$post_type = get_post_type_object( $post->post_type );

		// Check if the current user has permission to edit the post.
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		// Save array of media files.
		if ( ! empty( $_POST['sensei_media_attachments'] ) && is_array( $_POST['sensei_media_attachments'] ) ) {
			$media = array();

			$files = array_map( 'esc_url_raw', wp_unslash( (array) $_POST['sensei_media_attachments'] ) );

			foreach ( $files as $k => $file ) {
				if ( $file && strlen( $file ) > 0 ) {
					$media[ $k ] = $file;
				}
			}
			update_post_meta( $post_id, '_attached_media', $media );
		}
	}

	/**
	 * Display attached media files on single lesson & course pages
	 *
	 * @return void
	 */
	public function display_attached_media() {
		global $post;

		$media = get_post_meta( $post->ID, '_attached_media', true );
		if ( ! is_array( $media ) || 0 === count( $media ) ) {
			return;
		}

		$post_type = get_post_type( $post );

		if ( ! in_array( $post_type, array( 'course', 'lesson' ), true ) ) {
			return;
		}

		$user_id    = get_current_user_id();
		$course_id  = 'course' === $post_type ? $post->ID : get_post_meta( $post->ID, '_lesson_course', true );
		$show_links = $this->user_has_access( $course_id, $user_id );

		/**
		 * Filter whether to display the media attachment links on the course or
		 * lesson page. Defaults to true only if the user has started the
		 * course.
		 *
		 * @param bool   $show_links Whether to show the links.
		 * @param int    $user_id    The user ID.
		 * @param int    $post_id    The post ID.
		 * @param string $post_type  Either "course" or "lesson".
		 */
		if ( ! apply_filters( 'sensei_media_attachments_show_media_links', $show_links, $user_id, $post->ID, $post_type ) ) {
			return;
		}

		$media_heading = ( 'lesson' === $post_type ) ?
			__( 'Lesson Media', 'sensei-media-attachments' ) :
			__( 'Course Media', 'sensei-media-attachments' );

		/**
		 * Change the media heading title on course and lesson pages.
		 *
		 * @since 2.0.0
		 *
		 * @param string  $media_heading Heading text for course or lesson page's attachments.
		 * @param int     $post_id       Current post ID.
		 * @param string  $post_type     Post type for current post.
		 */
		$media_heading = apply_filters( 'sensei_media_attachments_media_heading', $media_heading, $post->ID, $post_type );

		$html  = '<div id="attached-media">';
		$html .= '<h2>' . esc_html( $media_heading ) . '</h2>';
		$html .= '<ul>';
		foreach ( $media as $k => $file ) {
			$attachment_label = $this->get_attachment_title( $file );
			$html            .= '<li id="attached_media_' . esc_attr( $k ) . '"><a href="' . esc_url( $file ) . '" target="_blank">' . esc_html( $attachment_label ) . '</a></li>';
		}
		$html .= '</ul></div>';

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- User data escaped above.
	}

	/**
	 * Check if user has access.
	 *
	 * @param int $course_id Course post ID.
	 * @param int $user_id   User ID.
	 *
	 * @return bool
	 */
	private function user_has_access( $course_id, $user_id ) {
		global $post;

		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		if ( current_user_can( $post_type_object->cap->edit_post, $post->ID ) ) {
			return true;
		}

		// If we're using an older version of Sensei, use the progress method.
		if ( ! method_exists( 'Sensei_Course', 'is_user_enrolled' ) ) {
			return Sensei_Utils::user_started_course( $course_id, $user_id );
		}

		return Sensei_Course::is_user_enrolled( $course_id, $user_id );
	}

	/**
	 * Get the attachment title.
	 *
	 * @param string $attachment_url Attachment URL.
	 *
	 * @return string
	 */
	private function get_attachment_title( $attachment_url ) {
		$attachment_id = attachment_url_to_postid( $attachment_url );

		if ( ! empty( $attachment_id ) ) {
			$attachment_title = get_the_title( $attachment_id );
		}

		if ( empty( $attachment_title ) ) {
			$url_path         = wp_parse_url( $attachment_url, PHP_URL_PATH );
			$attachment_title = basename( $url_path );
		}

		/**
		 * Update the title that is displayed in the attachment list.
		 *
		 * @param string $attachment_title Title to display for the attachment.
		 * @param string $attachment_url   URL of the attachment.
		 * @param int    $attachment_id    Attachment post ID (if known).
		 */
		return apply_filters( 'sensei_media_attachments_get_attachment_title', $attachment_title, $attachment_url, $attachment_id );
	}

	/**
	 * Load localisation.
	 *
	 * @return void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'sensei-media-attachments', false, dirname( SENSEI_MEDIA_ATTACHMENTS_PLUGIN_BASENAME ) . '/lang/' );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$domain = 'sensei-media-attachments';

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using commonly used core hook to fetch locales.
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( SENSEI_MEDIA_ATTACHMENTS_PLUGIN_BASENAME ) . '/lang/' );
	}

	/**
	 * Main Sensei_Media_Attachments Instance
	 *
	 * Ensures only one instance of Sensei_Media_Attachments is loaded or can be loaded.
	 *
	 * @since  2.0.0
	 * @static
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

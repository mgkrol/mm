<?php
/**
 * bs-instagram.php
 *---------------------------
 * [bs-instagram] shortcode & widget
 *
 */


/**
 * Publisher Instagram Shortcode
 */
class Publisher_Instagram_Shortcode extends BF_Shortcode {

	function __construct( $id, $options ) {

		$id = 'bs-instagram';

		$_options = array(
			'defaults'            => array(
				'user_id'              => '',
				'photo_count'          => '',
				'style'                => '3-1',
				//
				'title'                => publisher_translation_get( 'widget_instagram' ),
				'show_title'           => 1,
				'icon'                 => '',
				'heading_style'        => 'default',
				'heading_color'        => '',
				//
				'bs-show-desktop'      => 1,
				'bs-show-tablet'       => 1,
				'bs-show-phone'        => 1,
				'css'                  => '',
				'custom-css-class'     => '',
				'custom-id'            => '',
				//
				'bs-text-color-scheme' => '',
			),
			'have_widget'         => TRUE,
			'have_vc_add_on'      => TRUE,
			'have_tinymce_add_on' => TRUE,
		);

		if ( isset( $options['shortcode_class'] ) ) {
			$_options['shortcode_class'] = $options['shortcode_class'];
		}

		if ( isset( $options['widget_class'] ) ) {
			$_options['widget_class'] = $options['widget_class'];
		}

		parent::__construct( $id, $_options );

	}


	/**
	 * Filter custom css codes for shortcode widget!
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function register_custom_css( $fields ) {

		return $fields;
	}


	/**
	 * Handle displaying of shortcode
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	function display( array $atts, $content = '' ) {

		ob_start();

		publisher_set_prop( 'shortcode-bs-instagram-atts', $atts );

		publisher_get_view( 'shortcodes', 'bs-instagram' );

		publisher_clear_props();

		return ob_get_clean();

	}


	/**
	 * Fields of Visual Composer and TinyMCE
	 *
	 * @return array
	 */
	public function get_fields() {

		$fields = array(
			array(
				'type' => 'tab',
				'name' => __( 'Instagram', 'publisher' ),
				'id'   => 'instagram',
			),
			array(
				'name'           => __( 'Instagram Username', 'publisher' ),
				'type'           => 'text',
				'id'             => 'user_id',
				//
				'vc_admin_label' => TRUE,
			),
			array(
				'name'           => __( 'Number of Shots', 'publisher' ),
				'id'             => 'photo_count',
				'type'           => 'text',
				//
				'vc_admin_label' => FALSE,
			),
			array(
				'name'           => __( 'Columns', 'publisher' ),
				'id'             => 'style',
				//
				'type'           => 'select',
				'options'        => array(
					'2'      => __( '2 Column', 'publisher' ),
					'2-1'    => __( '2 Column - First Big + Small thumbnails [Small sidebar]', 'publisher' ),
					'3'      => __( '3 Column', 'publisher' ),
					'3-1'    => __( '3 Column - First Big + Small thumbnails', 'publisher' ),
					'slider' => __( 'Slider', 'publisher' ),
				),
				//
				'vc_admin_label' => FALSE,
			),
		);


		/**
		 * Retrieve heading fields from outside (our themes are defining them)
		 */
		{
			$heading_fields = apply_filters( 'better-framework/shortcodes/heading-fields', array(), $this->id );

			if ( $heading_fields ) {
				$fields = array_merge( $fields, $heading_fields );
			}
		}


		/**
		 * Retrieve design fields from outside (our themes are defining them)
		 */
		{
			$design_fields = apply_filters( 'better-framework/shortcodes/design-fields', array(), $this->id );

			if ( $design_fields ) {
				$fields = array_merge( $fields, $design_fields );
			}
		}

		bf_array_insert_after(
			'bs-show-phone',
			$fields,
			'bs-text-color-scheme',
			array(
				'name'           => __( 'Block Text Color Scheme', 'publisher' ),
				'id'             => 'bs-text-color-scheme',
				//
				'type'           => 'select',
				'options'        => array(
					''      => __( '-- Default --', 'publisher' ),
					'light' => __( 'White Color Texts', 'publisher' ),
				),
				//
				'vc_admin_label' => FALSE,
			)
		);

		return $fields;
	}


	/**
	 * Registers Visual Composer Add-on
	 */
	function register_vc_add_on() {

		vc_map( array(
			'name'           => __( 'Instagram Photos', 'publisher' ),
			"base"           => $this->id,
			"weight"         => 10,
			"wrapper_height" => 'full',

			"category" => publisher_white_label_get_option( 'publisher' ),
			"params"   => $this->vc_map_listing_all(),
		) );

	} // register_vc_add_on


	function tinymce_settings() {

		return array(
			'name' => __( 'Instagram', 'publisher' ),
		);
	}
} // Publisher_Instagram_Shortcode


if ( ! function_exists( 'publisher_shortcode_instagram_get_data' ) ) {
	/**
	 * Wrapper ro getting Instagram data with cache mechanism
	 *
	 * @param $atts
	 *
	 * @return array|bool|mixed|void
	 */
	function publisher_shortcode_instagram_get_data( $atts ) {

		// version number will be added to replace cache in each theme update
		// to prevent bugs from changing data from last version
		$theme_version = str_replace(
			array(
				'.',
				' '
			),
			'-',
			PUBLISHER_THEME_VERSION
		);

		// count will be added to prevent deference counts problem in widgets for same username
		$data_store = 'bs-insta-' . $atts['photo_count'] . '-' . $theme_version . '-' . $atts['user_id'];
		$back_store = 'bs-insta-bk-' . $atts['photo_count'] . '-' . $theme_version . '-' . $atts['user_id'];
		$cache_time = HOUR_IN_SECONDS * 6;

		if ( ( $images_list = get_transient( $data_store ) ) === FALSE ) {

			$images_list = publisher_shortcode_instagram_fetch_data( $atts );

			if ( is_wp_error( $images_list ) && is_user_logged_in() ) {
				return $images_list;
			} elseif ( ! is_wp_error( $images_list ) ) {

				// Save a transient to expire in $cache_time and a permanent backup option ( fallback )
				set_transient( $data_store, $images_list, $cache_time );
				update_option( $back_store, $images_list, 'no' );

			} // Fall to permanent backup store
			else {
				$images_list = get_option( $back_store );
			}
		}

		return $images_list;
	} // publisher_shortcode_instagram_get_data
} // if


if ( ! function_exists( 'publisher_shortcode_instagram_fetch_data' ) ) {
	/**
	 * Retrieve Instagram fresh data
	 * covered as function to support shortcode $atts
	 *
	 * output[]
	 *      [
	 *          'description',
	 *          'link'',
	 *          'time',
	 *          'comments',
	 *          'comments',
	 *          'likes',
	 *          'type',
	 *          'images'[]
	 *              [
	 *                  'thumbnail',
	 *                  'small',
	 *                  'large',
	 *                  'original',
	 *              ],
	 *      ]
	 *
	 * @param $atts
	 *
	 * @return array|bool
	 */
	function publisher_shortcode_instagram_fetch_data( $atts ) {

		if ( ! class_exists( 'Publisher_Instagram_Client_v1' ) ) {
			require_once bf_get_theme_dir( 'includes/libs/bs-theme-api/class-publisher-instagram-api.php' );
		}

		// Get images
		try {
			$client = new Publisher_Instagram_Scraper_Client_v1();

			// scrape user images
			$images_list = $client->scrape_user( $atts['user_id'], $atts['photo_count'] );
		} catch( Exception $e ) {
			return array();
		}

		return $images_list;

	} // publisher_shortcode_instagram_fetch_data
} // if


/**
 * Publisher Instagram Widget
 */
class Publisher_Instagram_Widget extends BF_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
			'bs-instagram',
			sprintf( __( '%s - Instagram', 'publisher' ), publisher_white_label_get_option( 'publisher' ) ),
			array(
				'description' => __( 'Display latest photos from Instagram.', 'publisher' )
			)
		);
	} // __construct


	/**
	 * Adds backend fields
	 */
	function load_fields() {

		$this->fields = array(
			array(
				'name' => __( 'Title:', 'publisher' ),
				'id'   => 'title',
				'type' => 'text',
			),
			array(
				'name' => __( 'Instagram Username:', 'publisher' ),
				'id'   => 'user_id',
				'type' => 'text',
			),
			array(
				'name' => __( 'Number of Photos:', 'publisher' ),
				'id'   => 'photo_count',
				'type' => 'text',
			),
			array(
				'name'    => __( 'Columns', 'publisher' ),
				'id'      => 'style',
				'type'    => 'select',
				'options' => array(
					'2'      => __( '2 Column', 'publisher' ),
					'2-1'    => __( '2 Column - First Big + Small thumbnails [Small sidebar]', 'publisher' ),
					'3'      => __( '3 Column', 'publisher' ),
					'3-1'    => __( '3 Column - First Big + Small thumbnails', 'publisher' ),
					'slider' => __( 'Slider', 'publisher' ),
				),
			),
		);
	}
} // Publisher_Instagram_Widget

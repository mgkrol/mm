<?php
/**
 * bs-heading.php
 *---------------------------
 * [bs-heading] shortcode
 *
 */


/**
 * Publisher Heading shortcode
 */
class Publisher_Heading_Shortcode extends BF_Shortcode {

	function __construct( $id, $options ) {

		$id = 'bs-heading';

		$_options = array(
			'defaults'            => array(
				'title_link'           => '',
				//
				'title'                => publisher_translation_get( 'heading' ),
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
			'have_widget'         => FALSE,
			'have_vc_add_on'      => TRUE,
			'have_tinymce_add_on' => TRUE,
		);

		if ( isset( $options['shortcode_class'] ) ) {
			$_options['shortcode_class'] = $options['shortcode_class'];
		}

		parent::__construct( $id, $_options );

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

		if ( ! empty( $content ) ) {
			$atts['content'] = $content;
		}

		publisher_set_prop( 'shortcode-bs-heading-atts', $atts );

		$output = publisher_get_view( 'shortcodes', 'bs-heading', '', FALSE );

		publisher_clear_props();

		return $output;

	}


	/**
	 * Fields of Visual Composer and TinyMCE
	 */
	public function get_fields() {

		$fields = array();

		/**
		 * Retrieve heading fields from outside (our themes are defining them)
		 */
		{
			$heading_fields = apply_filters( 'better-framework/shortcodes/heading-fields', array(), $this->id );

			if ( $heading_fields ) {
				$fields = array_merge( $fields, $heading_fields );
			}
		}

		bf_array_insert_after(
			'icon',
			$fields,
			'title_link',
			array(
				'name'           => __( 'Title Link', 'publisher' ),
				'id'             => 'title_link',
				'type'           => 'text',
				//
				'vc_admin_label' => FALSE,
			)
		);


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

		vc_map(
			array(
				'name'           => __( 'Heading', 'publisher' ),
				"base"           => $this->id,
				"weight"         => 10,
				"wrapper_height" => 'full',
				"category"       => publisher_white_label_get_option( 'publisher' ),
				"params"         => $this->vc_map_listing_all(),
			)
		);
	}


	function tinymce_settings() {

		return array(
			'name' => __( 'Heading', 'publisher' ),
		);
	}
}

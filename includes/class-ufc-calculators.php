<?php
/**
 * Handles the registration of shortcodes and AJAX handlers.
 *
 * @package Urban_Farming_Calculators
 * @since   0.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class UFC_Calculators {

	/**
	 * Data loader instance.
	 *
	 * @var UFC_Data_Loader
	 */
	private $data_loader;

	/**
	 * Calculator service instance.
	 *
	 * @var UFC_Calculator_Service
	 */
	private $calculator_service;

	/**
	 * Constructor.
	 *
	 * @param UFC_Data_Loader        $data_loader        The data loader.
	 * @param UFC_Calculator_Service $calculator_service The calculator service.
	 */
	public function __construct( $data_loader, $calculator_service ) {
		$this->data_loader = $data_loader;
		$this->calculator_service = $calculator_service;

		add_shortcode( 'seed_calculator', array( $this, 'render_seed_calculator' ) );
		add_shortcode( 'mulch_calculator', array( $this, 'render_mulch_calculator' ) );

		// AJAX handlers for both logged-in and non-logged-in users.
		add_action( 'wp_ajax_ufc_search_seeds', array( $this, 'search_seeds_handler' ) );
		add_action( 'wp_ajax_nopriv_ufc_search_seeds', array( $this, 'search_seeds_handler' ) );

		add_action( 'wp_ajax_ufc_calculate_seeds', array( $this, 'calculate_seeds_handler' ) );
		add_action( 'wp_ajax_nopriv_ufc_calculate_seeds', array( $this, 'calculate_seeds_handler' ) );

		add_action( 'wp_ajax_ufc_calculate_mulch', array( $this, 'calculate_mulch_handler' ) );
		add_action( 'wp_ajax_nopriv_ufc_calculate_mulch', array( $this, 'calculate_mulch_handler' ) );
	}

	/**
	 * AJAX handler for the real-time seed search.
	 */
	public function search_seeds_handler() {
		check_ajax_referer( 'ufc_ajax_nonce', 'nonce' );

		$search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) : '';

		if ( strlen( $search_term ) < 2 ) {
			wp_send_json_success( array() );
		}

		$results = $this->data_loader->search_seeds_by_name( $search_term );
		wp_send_json_success( $results );
	}

	/**
	 * AJAX handler for the seed calculator submission.
	 */
	public function calculate_seeds_handler() {
		check_ajax_referer( 'ufc_ajax_nonce', 'nonce' );

		$area       = isset( $_POST['area'] ) ? floatval( $_POST['area'] ) : 0;
		$unit       = isset( $_POST['unit'] ) ? sanitize_key( $_POST['unit'] ) : 'metric';
		$plant_type = isset( $_POST['plant_type'] ) ? sanitize_key( $_POST['plant_type'] ) : '';

		if ( 'us' === $unit ) {
			$area *= 0.092903; // Convert sq ft to sq meters.
		}

		$calculation = $this->calculator_service->calculate_seeds_needed( $area, $plant_type );

		if ( is_wp_error( $calculation ) ) {
			wp_send_json_error(
				array(
					'message' => $calculation->get_error_message(),
				)
			);
		} else {
			// Instead of a simple string, render the full HTML template.
			$html = $this->get_template_html(
				'seed-calculator-results.php',
				array(
					'seeds_needed' => $calculation['seeds_needed'],
					'grams_needed' => $calculation['grams_needed'],
					'seed_data'    => $calculation['seed_data'],
				)
			);
			wp_send_json_success( array( 'html' => $html ) );
		}
	}

	/**
	 * AJAX handler for the mulch calculator submission.
	 */
	public function calculate_mulch_handler() {
		check_ajax_referer( 'ufc_ajax_nonce', 'nonce' );

		$area         = isset( $_POST['area'] ) ? floatval( $_POST['area'] ) : 0;
		$depth        = isset( $_POST['depth'] ) ? floatval( $_POST['depth'] ) : 0;
		$unit         = isset( $_POST['unit'] ) ? sanitize_key( $_POST['unit'] ) : 'metric';

		$mulch_needed = $this->calculator_service->calculate_mulch_needed( $area, $depth, $unit );

		if ( is_wp_error( $mulch_needed ) ) {
			wp_send_json_error(
				array(
					'message' => $mulch_needed->get_error_message(),
				)
			);
		} else {
			$html = $this->get_template_html(
				'mulch-calculator-results.php',
				array( 'mulch_needed' => $mulch_needed )
			);
			wp_send_json_success( array( 'html' => $html ) );
		}
	}

	/**
	 * Renders the seed calculator shortcode.
	 */
	public function render_seed_calculator() {
		return $this->get_template_html( 'seed-calculator-form.php' );
	}

	/**
	 * Renders the mulch calculator shortcode.
	 */
	public function render_mulch_calculator() {
		return $this->get_template_html( 'mulch-calculator-form.php' );
	}

	/**
	 * Includes a template file and returns its output as a string.
	 *
	 * @param string $template_name The name of the file in the /templates/ directory.
	 * @param array  $args          Optional. An array of variables to make available to the template.
	 * @return string The buffered HTML output.
	 */
	private function get_template_html( $template_name, $args = array() ) {
		$template_path = UFC_PLUGIN_DIR . 'templates/' . $template_name;

		if ( ! file_exists( $template_path ) ) {
			return '<!-- Template not found -->';
		}

		if ( is_array( $args ) ) {
			extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		ob_start();
		include $template_path;
		return ob_get_clean();
	}
}

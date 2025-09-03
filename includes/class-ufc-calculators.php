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

	public function __construct() {
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

		$results = UFC_Data::search_seeds_by_name( $search_term );
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

		$calculation = $this->calculate_seeds_needed( $area, $plant_type );

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
					'grams_needed' => $calculation['grams_needed'], // Pass the new data.
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
		$mulch_needed = $this->calculate_mulch_needed( $area, $depth, $unit );

		if ( is_wp_error( $mulch_needed ) ) {
			wp_send_json_error(
				array(
					'message' => $mulch_needed->get_error_message(),
				)
			);
		} else {
			$html = '<div class="ufc-result ufc-success">' . esc_html( $mulch_needed ) . '</div>';
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

	/**
	 * Calculates the number and weight of seeds needed.
	 *
	 * @return array|WP_Error An array with 'seeds_needed', 'grams_needed', and 'seed_data' on success, or WP_Error on failure.
	 */
	private function calculate_seeds_needed( $area, $plant_type ) {
		$all_seeds = UFC_Data::get_all_seeds();

		if ( ! isset( $all_seeds[ $plant_type ] ) ) {
			return new WP_Error(
				'invalid_plant_type',
				esc_html__( 'Invalid plant type selected.', 'urban-farming-calculators' )
			);
		}

		if ( $area <= 0 ) {
			return new WP_Error(
				'invalid_area',
				esc_html__( 'Area must be greater than zero.', 'urban-farming-calculators' )
			);
		}

		$seed_data  = $all_seeds[ $plant_type ];
		$spacing_cm = isset( $seed_data['plant_spacing_cm'] ) ? (float) $seed_data['plant_spacing_cm'] : 0;

		if ( $spacing_cm <= 0 ) {
			return new WP_Error(
				'invalid_spacing_data',
				esc_html__( 'Plant spacing data is invalid.', 'urban-farming-calculators' )
			);
		}
		$spacing_m    = $spacing_cm / 100;
		$seeds_needed = ceil( $area / $spacing_m ** 2 );
		$grams_needed = null;

		// --- NEW: Calculate grams if possible ---
		// Defensively check that the data exists and is not zero to prevent errors.
		$seeds_per_gram = isset( $seed_data['seeds_per_gram'] ) ? (int) $seed_data['seeds_per_gram'] : 0;
		if ( $seeds_per_gram > 0 ) {
			$grams_needed = $seeds_needed / $seeds_per_gram;
		}

		// Return the full data array, now including the calculated grams.
		return array(
			'seeds_needed' => $seeds_needed,
			'grams_needed' => $grams_needed,
			'seed_data'    => $seed_data,
		);
	}

	/**
	 * Calculates the volume of mulch needed.
	 */
	private function calculate_mulch_needed( $area, $depth, $unit ) {
		if ( $area <= 0 || $depth <= 0 ) {
			return new WP_Error(
				'invalid_dimensions',
				esc_html__( 'Area and depth must be greater than zero.', 'urban-farming-calculators' )
			);
		}

		if ( 'metric' === $unit ) {
			$volume_m3 = $area * ( $depth / 100 );
			return sprintf(
				esc_html__( '%s cubic meters', 'urban-farming-calculators' ),
				round( $volume_m3, 2 )
			);
		} elseif ( 'us' === $unit ) {
			$volume_ft3 = $area * ( $depth / 12 );
			return sprintf(
				esc_html__( '%s cubic feet', 'urban-farming-calculators' ),
				round( $volume_ft3, 2 )
			);
		}

		return new WP_Error(
			'invalid_unit',
			esc_html__( 'Invalid unit selected.', 'urban-farming-calculators' )
		);
	}
}

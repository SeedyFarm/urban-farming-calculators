<?php
/**
 * Service class for calculator logic.
 *
 * @package Urban_Farming_Calculators
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class UFC_Calculator_Service {

	/**
	 * Data loader instance.
	 *
	 * @var UFC_Data_Loader
	 */
	private $data_loader;

	/**
	 * Constructor.
	 *
	 * @param UFC_Data_Loader $data_loader The data loader instance.
	 */
	public function __construct( $data_loader ) {
		$this->data_loader = $data_loader;
	}

	/**
	 * Calculates the number and weight of seeds needed.
	 *
	 * @param float  $area       The area to plant.
	 * @param string $plant_type The slug of the plant type.
	 * @return array|WP_Error An array with 'seeds_needed', 'grams_needed', and 'seed_data' on success, or WP_Error on failure.
	 */
	public function calculate_seeds_needed( $area, $plant_type ) {
		$all_seeds = $this->data_loader->get_all_seeds();

		if ( ! isset( $all_seeds[ $plant_type ] ) ) {
			return new WP_Error(
				'invalid_plant_type',
				__( 'Invalid plant type selected.', 'urban-farming-calculators' )
			);
		}

		if ( $area <= 0 ) {
			return new WP_Error(
				'invalid_area',
				__( 'Area must be greater than zero.', 'urban-farming-calculators' )
			);
		}

		$seed_data  = $all_seeds[ $plant_type ];
		$spacing_cm = isset( $seed_data['plant_spacing_cm'] ) ? (float) $seed_data['plant_spacing_cm'] : 0;

		if ( $spacing_cm <= 0 ) {
			return new WP_Error(
				'invalid_spacing_data',
				__( 'Plant spacing data is invalid for this plant type.', 'urban-farming-calculators' )
			);
		}

		$spacing_m    = $spacing_cm / 100;
		$seeds_needed = ceil( $area / ( $spacing_m ** 2 ) );
		$grams_needed = null;

		// Calculate grams if possible.
		$seeds_per_gram = isset( $seed_data['seeds_per_gram'] ) ? (int) $seed_data['seeds_per_gram'] : 0;
		if ( $seeds_per_gram > 0 ) {
			$grams_needed = $seeds_needed / $seeds_per_gram;
		}

		return array(
			'seeds_needed' => $seeds_needed,
			'grams_needed' => $grams_needed,
			'seed_data'    => $seed_data,
		);
	}

	/**
	 * Calculates the volume of mulch needed.
	 *
	 * @param float  $area  The area to cover.
	 * @param float  $depth The depth of the mulch.
	 * @param string $unit  The unit system ('metric' or 'us').
	 * @return string|WP_Error The formatted result string on success, or WP_Error on failure.
	 */
	public function calculate_mulch_needed( $area, $depth, $unit ) {
		if ( $area <= 0 || $depth <= 0 ) {
			return new WP_Error(
				'invalid_dimensions',
				__( 'Area and depth must be greater than zero.', 'urban-farming-calculators' )
			);
		}

		if ( 'metric' === $unit ) {
			$volume_m3 = $area * ( $depth / 100 );
			return sprintf(
				__( '%s cubic meters', 'urban-farming-calculators' ),
				round( $volume_m3, 2 )
			);
		} elseif ( 'us' === $unit ) {
			$volume_ft3 = $area * ( $depth / 12 );
			return sprintf(
				__( '%s cubic feet', 'urban-farming-calculators' ),
				round( $volume_ft3, 2 )
			);
		}

		return new WP_Error(
			'invalid_unit',
			__( 'Invalid unit selected.', 'urban-farming-calculators' )
		);
	}
}

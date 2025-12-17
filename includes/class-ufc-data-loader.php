<?php
/**
 * Handles all data retrieval for the calculators.
 *
 * @package Urban_Farming_Calculators
 * @since   0.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class UFC_Data_Loader {

	/**
	 * The transient key for caching seed data.
	 */
	const TRANSIENT_KEY = 'ufc_all_seeds_data';

	/**
	 * Get all available seed types, using a cache.
	 *
	 * @return array An associative array of seed data.
	 */
	public function get_all_seeds() {
		// For debugging: allow force-refreshing the cache via a URL parameter.
		// Note: Using $_GET directly in a method like this is not ideal, but keeping consistent with original intent.
		// Ideally this should be handled by a controller/hook.
		if ( isset( $_GET['ufc_force_refresh'] ) && current_user_can('manage_options') ) {
			$this->clear_cache();
		}

		$cached_seeds = get_transient( self::TRANSIENT_KEY );

		if ( false !== $cached_seeds ) {
			return $cached_seeds;
		}

		return $this->load_from_file();
	}

	/**
	 * Loads data from the JSON file.
	 *
	 * @return array
	 */
	private function load_from_file() {
		$json_file_path = UFC_PLUGIN_DIR . 'data/seeds.json';

		if ( ! file_exists( $json_file_path ) ) {
			return array();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json_content = file_get_contents( $json_file_path );

		if ( ! $json_content ) {
			return array();
		}

		// IMPORTANT: Remove the UTF-8 BOM, which can cause json_decode to fail silently.
		if ( substr( $json_content, 0, 3 ) === "\xEF\xBB\xBF" ) {
			$json_content = substr( $json_content, 3 );
		}

		$seeds_array = json_decode( $json_content, true );

		if ( ! is_array( $seeds_array ) ) {
			return array();
		}

		$processed_seeds = array();
		foreach ( $seeds_array as $seed_item ) {
			if ( ! empty( $seed_item['common_name'] ) ) {
				$slug = sanitize_title( $seed_item['common_name'] );
				$processed_seeds[ $slug ] = $seed_item;
			}
		}

		// Cache for 12 hours.
		set_transient(
			self::TRANSIENT_KEY,
			$processed_seeds,
			12 * HOUR_IN_SECONDS
		);

		return $processed_seeds;
	}

	/**
	 * Searches seeds by common name.
	 *
	 * @param  string $search_term The user's search query.
	 * @return array An array of matching seed items, formatted for the AJAX response.
	 */
	public function search_seeds_by_name( $search_term ) {
		$all_seeds = $this->get_all_seeds();
		$matched_seeds = array();
		$search_term = strtolower( $search_term );

		foreach ( $all_seeds as $slug => $seed_data ) {
			if (
				isset( $seed_data['common_name'] ) &&
				false !== stripos( $seed_data['common_name'], $search_term )
			) {
				$matched_seeds[] = array(
					'key'  => $slug,
					'name' => $seed_data['common_name'],
				);
			}
		}

		return $matched_seeds;
	}

	/**
	 * Clears the seed data cache.
	 */
	public function clear_cache() {
		delete_transient( self::TRANSIENT_KEY );
	}
}

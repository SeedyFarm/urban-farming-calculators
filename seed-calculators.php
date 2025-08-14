<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UFC_Seed_Calculator {
    private $calculator_data;

    public function __construct() {
        add_action( 'wp_ajax_calculate_seeds', array( $this, 'calculate_seeds_ajax' ) );
        add_action( 'wp_ajax_nopriv_calculate_seeds', array( $this, 'calculate_seeds_ajax' ) );
        add_shortcode( 'seed_calculators', array( $this, 'seed_calculator_shortcode' ) );
    }

    private function get_calculator_data() {
        if ( ! isset( $this->calculator_data ) ) {
            $data_file = UFC_PLUGIN_DIR . 'data/calculator-data.json';
            if ( file_exists( $data_file ) ) {
                $json_data = file_get_contents( $data_file );
                $this->calculator_data = json_decode( $json_data, true );
            }
        }
        return $this->calculator_data;
    }

    private function calculate_seeds_needed( $area, $plant_type ) {
        $data = $this->get_calculator_data();
        $seed_types = $data['seed_types'] ?? [];

        if ( isset( $seed_types[ $plant_type ]['metric'] ) ) {
            $metric = $seed_types[ $plant_type ]['metric'];
            return ceil( $area / ( $metric ** 2 ) );
        }

        return 'Invalid plant type.';
    }

    public function calculate_seeds_ajax() {
        $area = floatval( $_POST['area'] );
        $unit = sanitize_text_field( $_POST['unit'] );
        $plant_type = sanitize_text_field( $_POST['plant_type'] );
        $area = ( $unit === 'us' ) ? $area * 0.092903 : $area;
        $seeds_needed = $this->calculate_seeds_needed( $area, $plant_type );

        wp_send_json( [
            'seeds_needed' => $seeds_needed,
        ] );

        wp_die();
    }

    public function seed_calculator_shortcode() {
        $data = $this->get_calculator_data();
        $all_seed_types = $data['seed_types'] ?? [];
        $seed_types = [];
        foreach ($all_seed_types as $key => $details) {
            $seed_types[$key] = $details['label'];
        }

        ob_start();
        include UFC_PLUGIN_DIR . 'templates/seed-calculator-form.php';
        return ob_get_clean();
    }
}

new UFC_Seed_Calculator();

<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UFC_Seed_Calculator {
    public function __construct() {
        add_action( 'wp_ajax_calculate_seeds', array( $this, 'calculate_seeds_ajax' ) );
        add_action( 'wp_ajax_nopriv_calculate_seeds', array( $this, 'calculate_seeds_ajax' ) );
        add_shortcode( 'seed_calculators', array( $this, 'seed_calculator_shortcode' ) );
    }

    private function calculate_seeds_needed( $area, $plant_type ) {
        $metrics = [
            'tomato' => 0.5,
            'lettuce' => 0.25,
            'carrot' => 0.1,
            'spinach' => 0.2,
        ];

        if ( isset( $metrics[ $plant_type ] ) ) {
            return ceil( $area / ( $metrics[ $plant_type ] ** 2 ) );
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
        $seed_types = [
            'tomato'    => 'Tomato',
            'lettuce'   => 'Lettuce',
            'carrot'    => 'Carrot',
            'spinach'   => 'Spinach',
        ];

        ob_start();
        include UFC_PLUGIN_DIR . 'templates/seed-calculator-form.php';
        return ob_get_clean();
    }
}

new UFC_Seed_Calculator();

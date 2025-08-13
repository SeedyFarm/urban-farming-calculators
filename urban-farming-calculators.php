<?php
/*
Plugin Name: Urban Farming Calculators
Description: A plugin that calculates the number of seeds needed for urban farming based on predefined metrics.
Version: 1.0.1
Author: Your Name
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin paths
define( 'UFC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UFC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

final class Urban_Farming_Calculators {
    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    private function includes() {
        require_once UFC_PLUGIN_DIR . 'seed-calculators.php';
    }

    public function enqueue_scripts() {
        if ( is_singular() && has_shortcode( get_post()->post_content, 'seed_calculators' ) ) {
            wp_enqueue_style( 'ufc-styles', UFC_PLUGIN_URL . 'css/style.css', array(), '1.0' );
            wp_enqueue_script( 'ufc-main-script', UFC_PLUGIN_URL . 'js/main.js', array( 'jquery' ), '1.0', true );

            wp_localize_script( 'ufc-main-script', 'ufc_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ) );
        }
    }
}

Urban_Farming_Calculators::get_instance();

<?php
/*
Plugin Name: Urban Farming Calculators
Description: A plugin that calculates the number of seeds needed for urban farming based on predefined metrics.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin paths
define( 'UFC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UFC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Enqueue styles and scripts only on pages using the shortcode
function ufc_enqueue_scripts() {
	if ( is_singular() && has_shortcode( get_post()->post_content, 'seed_calculators' ) ) {
		wp_enqueue_style( 'ufc-styles', UFC_PLUGIN_URL . 'css/style.css', array(), '1.0' );
		wp_enqueue_script( 'ufc-scripts', UFC_PLUGIN_URL . 'js/script.js', array( 'jquery' ), '1.0', true );
	}
}
add_action( 'wp_enqueue_scripts', 'ufc_enqueue_scripts' );

// Include the seed calculator functionality
require_once UFC_PLUGIN_DIR . 'seed-calculators.php';
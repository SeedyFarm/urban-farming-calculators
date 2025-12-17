<?php
/**
 * Plugin Name:       Urban Farming Calculators
 * Plugin URI:        https://seedy.farm/
 * Description:       Calculators for seeds and mulch needed for urban farming.
 * Version:           0.0.2
 * Author:            Seedy Farm
 * Author URI:        https://seedy.farm/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       urban-farming-calculators
 * Requires PHP:      7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Define plugin constants.
define( 'UFC_VERSION', '2.0.0' );
define( 'UFC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UFC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The main plugin class.
 */
final class Urban_Farming_Calculators {

	private static $instance;

	/**
	 * @var UFC_Data_Loader
	 */
	public $data_loader;

	/**
	 * @var UFC_Calculator_Service
	 */
	public $calculator_service;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->init_services();
		$this->init_hooks();
	}

	private function includes() {
		require_once UFC_PLUGIN_DIR . 'includes/class-ufc-data-loader.php';
		require_once UFC_PLUGIN_DIR . 'includes/class-ufc-calculator-service.php';
		require_once UFC_PLUGIN_DIR . 'includes/class-ufc-calculators.php';

		if ( is_admin() ) {
			require_once UFC_PLUGIN_DIR . 'includes/class-ufc-admin.php';
		}
	}

	private function init_services() {
		$this->data_loader        = new UFC_Data_Loader();
		$this->calculator_service = new UFC_Calculator_Service( $this->data_loader );
	}

	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Instantiate the calculators class to register shortcodes and AJAX hooks.
		// Pass dependencies.
		new UFC_Calculators( $this->data_loader, $this->calculator_service );

		if ( is_admin() ) {
			new UFC_Admin( $this->data_loader );
		}
	}

	public function activate() {
		// Clear the data transient on activation to ensure fresh data.
		// We use the constant from the class since we might not have the instance readily available in a static context hook if we were using static methods,
		// but here we are in a singleton instance method so we could access $this->data_loader if init_services() ran.
		// However, activation hook often runs in a separate process or early.
		// Safer to just delete the transient using the constant.
		delete_transient( UFC_Data_Loader::TRANSIENT_KEY );
	}

	public function enqueue_scripts() {
		global $post;

		// Only load assets on pages containing one of the calculator shortcodes.
		if (
			is_a( $post, 'WP_Post' ) &&
			( has_shortcode( $post->post_content, 'seed_calculator' ) ||
				has_shortcode( $post->post_content, 'mulch_calculator' ) )
		) {
			wp_enqueue_style(
				'ufc-styles',
				UFC_PLUGIN_URL . 'css/style.css',
				array(),
				UFC_VERSION
			);

			wp_enqueue_script(
				'ufc-main-script',
				UFC_PLUGIN_URL . 'js/main.js',
				array( 'jquery' ),
				UFC_VERSION,
				true
			);

			// Pass data to JavaScript, including the AJAX URL and a security nonce.
			wp_localize_script(
				'ufc-main-script',
				'ufc_ajax_object',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'ufc_ajax_nonce' ),
				)
			);
		}
	}
}

// Begins execution of the plugin.
Urban_Farming_Calculators::get_instance();

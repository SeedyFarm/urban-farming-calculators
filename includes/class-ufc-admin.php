<?php
/**
 * Handles the admin settings page and functionality.
 *
 * @package Urban_Farming_Calculators
 * @since   0.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class UFC_Admin {

	/**
	 * The hook suffix for the settings page.
	 *
	 * @var string
	 */
	private $settings_page_hook;

	/**
	 * Constructor. Hooks into WordPress.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'handle_clear_cache_action' ) );
	}

	/**
	 * Adds the plugin settings page to the "Tools" menu.
	 */
	public function add_settings_page() {
		$this->settings_page_hook = add_submenu_page(
			'tools.php', // Parent slug.
			__( 'Urban Farming Settings', 'urban-farming-calculators' ), // Page title.
			__( 'Urban Farming', 'urban-farming-calculators' ), // Menu title.
			'manage_options', // Capability required.
			'urban-farming-calculators-settings', // Menu slug.
			array( $this, 'render_settings_page' ) // Callback function to render the page.
		);
	}

	/**
	 * Renders the HTML for the settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Urban Farming Calculators Settings', 'urban-farming-calculators' ); ?></h1>
			<?php settings_errors(); ?>

			<p><?php esc_html_e( 'The plugin caches the seed data from the seeds.json file for performance. If you have manually updated this file, click the button below to clear the cache and load the new data immediately.', 'urban-farming-calculators' ); ?></p>

			<form method="post" action="">
				<?php
				// Security: Add a nonce field.
				wp_nonce_field( 'ufc_clear_cache_nonce' );
				?>
				<p>
					<input type="hidden" name="action" value="ufc_clear_cache">
					<?php
					submit_button(
						__( 'Clear Cache & Reload Data', 'urban-farming-calculators' ),
						'primary', // CSS class.
						'ufc_clear_cache_submit', // Name attribute.
						false // Don't wrap in <p> tags.
					);
					?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Handles the form submission for clearing the data cache.
	 */
	public function handle_clear_cache_action() {
		// 1. Check if our form has been submitted.
		if ( ! isset( $_POST['action'] ) || 'ufc_clear_cache' !== $_POST['action'] ) {
			return;
		}

		// 2. Security: Verify the nonce.
		check_admin_referer( 'ufc_clear_cache_nonce' );

		// 3. Security: Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'urban-farming-calculators' ) );
		}

		// 4. Perform the action: Delete the transient.
		delete_transient( 'ufc_all_seeds_data' );

		// 5. Provide feedback to the user.
		add_settings_error(
			'ufc_settings_notices', // Slug title of the setting.
			'ufc_cache_cleared', // Suffix-id for the error message box.
			__( 'Seed data cache has been successfully cleared. The latest data from seeds.json will now be used.', 'urban-farming-calculators' ),
			'success' // Type of message.
		);
	}
}

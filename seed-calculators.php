<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to calculate seeds needed
function calculate_seeds_needed( $area, $plant_type ) {
	$metrics = [
		'tomato' => 0.5,
		'lettuce' => 0.25,
		'carrot' => 0.1,
		'spinach' => 0.2,
	];

	if ( isset( $metrics[ $plant_type ] ) ) {
		return ceil( $area / ($metrics[ $plant_type ] ** 2) );
	}

	return 'Invalid plant type.';
}

// AJAX handler for seed calculation
function ufc_calculate_seeds_ajax() {
	$area = floatval( $_POST['area'] );
	$unit = sanitize_text_field( $_POST['unit'] );
	$plant_type = sanitize_text_field( $_POST['plant_type'] );
	$area = ( $unit === 'us' ) ? $area * 0.092903 : $area;
	$seeds_needed = calculate_seeds_needed( $area, $plant_type );

	wp_send_json( [
		'seeds_needed' => $seeds_needed,
	] );

	wp_die();
}
add_action( 'wp_ajax_calculate_seeds', 'ufc_calculate_seeds_ajax' );
add_action( 'wp_ajax_nopriv_calculate_seeds', 'ufc_calculate_seeds_ajax' );

// Shortcode for the seed calculator form
function ufc_seed_calculator_shortcode() {
	$seed_types = [
		'tomato'    => 'Tomato',
		'lettuce'   => 'Lettuce',
		'carrot'    => 'Carrot',
		'spinach'   => 'Spinach',
	];

	ob_start();
	?>
	<div id="ufc-seed-calculator">
		<h3>Urban Farming Seed Calculator</h3>
		<form id="ufc-seed-calculator-form">
			<label for="ufc-plant-type">Select Plant Type:</label>
			<select id="ufc-plant-type" name="plant_type">
				<?php foreach ( $seed_types as $type => $label ) : ?>
					<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<br><br>
			<label>Select Measurement Unit:</label><br>
			<input type="radio" id="ufc-unit-metric" name="unit" value="metric" checked>
			<label for="ufc-unit-metric">Metric</label><br>
			<input type="radio" id="ufc-unit-us" name="unit" value="us">
			<label for="ufc-unit-us">US</label>
			<br><br>
			<label for="ufc-area">Enter Area Size:</label>
			<input type="number" id="ufc-area" name="area" min="0" step="0.1" required>
			<br><br>
			<button type="button" id="ufc-calculate-button">Calculate</button>
		</form>
		<div id="ufc-result" style="margin-top: 20px;"></div>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#ufc-calculate-button').on('click', function() {
				var formData = {
					action: 'calculate_seeds',
					plant_type: $('#ufc-plant-type').val(),
					unit: $('input[name="unit"]:checked').val(),
					area: $('#ufc-area').val()
				};

				$.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', formData, function(response) {
					if (response.seeds_needed !== undefined) {
						$('#ufc-result').html('<p>Seeds Needed: <strong>' + response.seeds_needed + '</strong></p>');
					} else {
						$('#ufc-result').html('<p>Error calculating seeds. Please try again.</p>');
					}
				});
			});
		});
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'seed_calculators', 'ufc_seed_calculator_shortcode' );

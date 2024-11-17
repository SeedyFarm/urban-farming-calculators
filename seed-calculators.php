<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define the seed calculation function
function calculate_seeds_needed( $area, $plant_type ) {
	// Placeholder metrics data
	$metrics = [
		'tomato' => [
			'seed_spacing' => 0.5,  // Spacing in meters between seeds
		],
		'lettuce' => [
			'seed_spacing' => 0.25, // Spacing in meters between seeds
		],
		'carrot' => [
			'seed_spacing' => 0.1,  // Spacing in meters between seeds
		],
		'spinach' => [
			'seed_spacing' => 0.2,  // Spacing in meters between seeds
		],
	];

	// Check if the plant type is available in the metrics
	if ( ! array_key_exists( $plant_type, $metrics ) ) {
		return 'Invalid plant type.';
	}

	// Calculate the number of seeds needed
	$seed_spacing = $metrics[ $plant_type ]['seed_spacing'];
	$seeds_needed = ceil( $area / ( $seed_spacing * $seed_spacing ) );

	return $seeds_needed;
}

// Create the shortcode for the seed calculator form
function ufc_seed_calculator_shortcode() {
	if ( isset( $_POST['ufc_calculate_seeds'] ) ) {
		$area = floatval( $_POST['ufc_area'] );
		$unit = sanitize_text_field( $_POST['ufc_unit'] );
		$plant_type = sanitize_text_field( $_POST['ufc_plant_type'] );

		// Convert area to square meters if the unit is in square feet
		if ( $unit === 'us' ) {
			$area = $area * 0.092903; // 1 square foot = 0.092903 square meters
		}

		$seeds_needed = calculate_seeds_needed( $area, $plant_type );
	}

	ob_start();
	?>
	<div id="ufc-seed-calculator">
		<h3>Urban Farming Seed Calculator</h3>
		<form method="post">
			<label for="ufc-plant-type">Select Plant Type:</label>
			<select id="ufc-plant-type" name="ufc_plant_type">
				<option value="tomato" <?php echo isset( $plant_type ) && $plant_type === 'tomato' ? 'selected' : ''; ?>>Tomato</option>
				<option value="lettuce" <?php echo isset( $plant_type ) && $plant_type === 'lettuce' ? 'selected' : ''; ?>>Lettuce</option>
				<option value="carrot" <?php echo isset( $plant_type ) && $plant_type === 'carrot' ? 'selected' : ''; ?>>Carrot</option>
				<option value="spinach" <?php echo isset( $plant_type ) && $plant_type === 'spinach' ? 'selected' : ''; ?>>Spinach</option>
			</select>
			<br><br>
			<label for="ufc-unit">Select Measurement Unit:</label>
			<select id="ufc-unit" name="ufc_unit">
				<option value="metric" <?php echo isset( $unit ) && $unit === 'metric' ? 'selected' : ''; ?>>Metric (sq.m)</option>
				<option value="us" <?php echo isset( $unit ) && $unit === 'us' ? 'selected' : ''; ?>>U.S. (sq.ft)</option>
			</select>
			<br><br>
			<label for="ufc-area">Enter Area Size:</label>
			<input type="number" id="ufc-area" name="ufc_area" min="0" step="0.1" value="<?php echo isset( $area ) ? esc_attr( $area ) : ''; ?>" required>
			<br><br>
			<button type="submit" name="ufc_calculate_seeds">Calculate</button>
		</form>
		<?php if ( isset( $seeds_needed ) ) : ?>
			<div id="ufc-result">
				<p>Seeds Needed: <strong><?php echo esc_html( $seeds_needed ); ?></strong></p>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'seed_calculators', 'ufc_seed_calculator_shortcode' );

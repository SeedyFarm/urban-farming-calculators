<?php
/**
 * Template for the Mulch Calculator form.
 *
 * @package Urban_Farming_Calculators
 * @since   0.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="ufc-mulch-calculator" class="ufc-calculator-wrapper">
	<h3><?php esc_html_e( 'Urban Farming Mulch Calculator', 'urban-farming-calculators' ); ?></h3>
	<form id="ufc-mulch-calculator-form" class="ufc-calculator-form">

		<!-- The AJAX action we want to call. -->
		<input type="hidden" name="action" value="ufc_calculate_mulch">

		<div class="ufc-form-field">
			<label><?php esc_html_e( 'Select Measurement Unit:', 'urban-farming-calculators' ); ?></label>
			<label class="ufc-radio-label">
				<input type="radio" class="ufc-unit-radio" name="unit" value="metric" data-area-label="m²" data-depth-label="cm" checked>
				<?php esc_html_e( 'Metric', 'urban-farming-calculators' ); ?>
			</label>
			<label class="ufc-radio-label">
				<input type="radio" class="ufc-unit-radio" name="unit" value="us" data-area-label="ft²" data-depth-label="inches">
				<?php esc_html_e( 'US', 'urban-farming-calculators' ); ?>
			</label>
		</div>

		<div class="ufc-form-field">
			<label for="ufc-mulch-area"><?php esc_html_e( 'Enter Area Size', 'urban-farming-calculators' ); ?> (<span class="ufc-area-unit">m²</span>):</label>
			<input type="number" id="ufc-mulch-area" name="area" min="0" step="0.1" required>
		</div>

		<div class="ufc-form-field">
			<label for="ufc-mulch-depth"><?php esc_html_e( 'Enter Mulch Depth', 'urban-farming-calculators' ); ?> (<span class="ufc-depth-unit">cm</span>):</label>
			<input type="number" id="ufc-mulch-depth" name="depth" min="0" step="0.1" required>
		</div>

		<div class="ufc-form-field">
			<button type="submit" class="ufc-calculate-button"><?php esc_html_e( 'Calculate', 'urban-farming-calculators' ); ?></button>
		</div>
	</form>

	<div class="ufc-result-wrapper" style="margin-top: 20px;"></div>
</div>

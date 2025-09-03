<?php
/**
 * Template for the Seed Calculator form.
 *
 * @package Urban_Farming_Calculators
 * @since   0.0.2
 */

if (!defined("ABSPATH")) {
    exit(); // Exit if accessed directly.
} ?>
<div class="ufc-calculator-wrapper">
	<h3><?php esc_html_e(
     "Urban Farming Seed Calculator",
     "urban-farming-calculators"
 ); ?></h3>

	<form class="ufc-calculator-form">

		<input type="hidden" name="action" value="ufc_calculate_seeds">

		<div class="ufc-form-field ufc-search-wrapper">
			<label for="plant-search"><?php esc_html_e(
       "Search for Plant Type:",
       "urban-farming-calculators"
   ); ?></label>
			<input type="text" class="ufc-plant-search" name="plant_search" placeholder="<?php esc_attr_e(
       "e.g., Tomato",
       "urban-farming-calculators"
   ); ?>" autocomplete="off" required>
			<input type="hidden" class="ufc-plant-type-key" name="plant_type">
			<ul class="ufc-search-results"></ul>
		</div>

		<div class="ufc-form-field">
			<label><?php esc_html_e(
       "Select Measurement Unit:",
       "urban-farming-calculators"
   ); ?></label>
			<label class="ufc-radio-label">
				<input type="radio" name="unit" value="metric" checked>
				<?php esc_html_e("Metric (m²)", "urban-farming-calculators"); ?>
			</label>
			<label class="ufc-radio-label">
				<input type="radio" name="unit" value="us">
				<?php esc_html_e("US (ft²)", "urban-farming-calculators"); ?>
			</label>
		</div>

		<div class="ufc-form-field">
			<label for="area"><?php esc_html_e(
       "Enter Area Size:",
       "urban-farming-calculators"
   ); ?></label>
			<input type="number" name="area" min="0" step="0.1" required>
		</div>

		<div class="ufc-form-field">
			<button type="submit" class="ufc-calculate-button"><?php esc_html_e(
       "Calculate",
       "urban-farming-calculators"
   ); ?></button>
		</div>
	</form>

	<div class="ufc-result-wrapper"></div>
</div>

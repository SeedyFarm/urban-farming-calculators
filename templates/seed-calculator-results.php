<?php
/**
 * Template for displaying the detailed seed calculation results.
 *
 * This template is loaded via AJAX and receives the following variables:
 * @var int        $seeds_needed The calculated number of seeds.
 * @var float|null $grams_needed The calculated weight of seeds in grams.
 * @var array      $seed_data    The full data array for the selected seed.
 *
 * @package Urban_Farming_Calculators
 * @since   0.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="ufc-result ufc-success">
	<p class="ufc-result-main">
		<?php
		// Check if we were able to calculate the weight.
		if ( ! empty( $grams_needed ) && $grams_needed > 0 ) {
			// New, preferred format.
			printf(
				/* translators: 1: Weight in grams, 2: Common name of the plant, 3: Number of seeds. */
				esc_html__( 'You will need approximately %1$s grams of %2$s seeds (~%3$s seeds).', 'urban-farming-calculators' ),
				'<strong>' . esc_html( round( $grams_needed, 2 ) ) . '</strong>',
				'<strong>' . esc_html( $seed_data['common_name'] ) . '</strong>',
				esc_html( number_format_i18n( $seeds_needed ) )
			);
		} else {
			// Fallback to the original format if grams couldn't be calculated.
			printf(
				/* translators: 1: Number of seeds, 2: Common name of the plant. */
				esc_html__( 'You will need approximately %1$s %2$s seeds.', 'urban-farming-calculators' ),
				'<strong>' . absint( $seeds_needed ) . '</strong>',
				'<strong>' . esc_html( $seed_data['common_name'] ) . '</strong>'
			);
		}
		?>
	</p>
</div>

<div class="ufc-result-details">
	<h4><?php esc_html_e( 'Planting Details', 'urban-farming-calculators' ); ?></h4>
	<ul>
		<?php if ( isset( $seed_data['scientific_name'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Scientific Name:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( $seed_data['scientific_name'] ); ?></li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['variety'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Variety:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( $seed_data['variety'] ); ?></li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['seeds_per_gram'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Seeds per Gram:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( number_format_i18n( $seed_data['seeds_per_gram'] ) ); ?></li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['plant_spacing_cm'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Plant Spacing:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( $seed_data['plant_spacing_cm'] ); ?> cm</li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['row_spacing_cm'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Row Spacing:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( $seed_data['row_spacing_cm'] ); ?> cm</li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['planting_depth_cm'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Planting Depth:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( $seed_data['planting_depth_cm'] ); ?> cm</li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['days_to_germination'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Days to Germination:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( $seed_data['days_to_germination'] ); ?></li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['days_to_maturity'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Days to Maturity:', 'urban-farming-calculators' ); ?></strong> <?php echo esc_html( $seed_data['days_to_maturity'] ); ?></li>
		<?php endif; ?>

		<?php if ( isset( $seed_data['notes'] ) ) : ?>
			<li><strong><?php esc_html_e( 'Notes:', 'urban-farming-calculators' ); ?></strong> <?php echo wp_kses_post( $seed_data['notes'] ); // Use wp_kses_post for potentially longer text with basic HTML. ?></li>
		<?php endif; ?>
	</ul>
</div>

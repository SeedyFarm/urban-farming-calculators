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

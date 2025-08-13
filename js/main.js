jQuery(document).ready(function($) {
    $('#ufc-calculate-button').on('click', function() {
        var formData = {
            action: 'calculate_seeds',
            plant_type: $('#ufc-plant-type').val(),
            unit: $('input[name="unit"]:checked').val(),
            area: $('#ufc-area').val()
        };

        $.post(ufc_ajax.ajax_url, formData, function(response) {
            if (response.seeds_needed !== undefined) {
                $('#ufc-result').html('<p>Seeds Needed: <strong>' + response.seeds_needed + '</strong></p>');
            } else {
                $('#ufc-result').html('<p>Error calculating seeds. Please try again.</p>');
            }
        });
    });
});

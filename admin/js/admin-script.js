jQuery(document).ready(function($) {
    // I set up the event listener for form submission
    $('#webtoapp-form').on('submit', function(e) {
        // I prevent the default form submission behavior
        e.preventDefault();

        // I get the values from the form fields
        var site_url = $('#webtoapp_url').val();
        var security = $('input[name="security"]').val();

        // I make an AJAX request to trigger the APK generation
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'webtoapp_generate_apk',
                webtoapp_url: site_url,
                security: security
            },
            // I handle the success response from the AJAX request
            success: function(response) {
                var result = JSON.parse(response);
                var resultDiv = $('#webtoapp-result');

                // I update the result div with the success or error message
                if (result.status === 'success') {
                    resultDiv.removeClass('error').addClass('success').text(result.message);
                } else {
                    resultDiv.removeClass('success').addClass('error').text(result.message);
                }
            },
            // I handle the error response from the AJAX request
            error: function() {
                $('#webtoapp-result').removeClass('success').addClass('error').text('An error occurred while processing your request.');
            }
        });
    });
});

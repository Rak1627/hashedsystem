/**
 * Event Registration System - Frontend JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Event Registration JS loaded');
        console.log('AJAX URL:', ersAjax.ajaxurl);

        // Handle registration form submission
        $('#event-registration-form').on('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');

            var form = $(this);
            var submitBtn = form.find('.submit-btn');
            var messageDiv = form.find('.form-message');

            // Disable submit button
            submitBtn.prop('disabled', true).text('Submitting...');

            // Clear previous messages
            messageDiv.hide().removeClass('success error');

            // Get form data
            var formData = {
                action: 'event_registration',
                nonce: ersAjax.nonce,
                event_id: form.find('input[name="event_id"]').val(),
                name: form.find('input[name="name"]').val(),
                email: form.find('input[name="email"]').val(),
                attendees: form.find('input[name="attendees"]').val()
            };

            // Submit via AJAX
            console.log('Sending AJAX request with data:', formData);

            $.ajax({
                url: ersAjax.ajaxurl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Response:', response);
                    console.log('Success:', response.success);
                    console.log('Data:', response.data);
                    console.log('Message:', response.data ? response.data.message : 'No message');

                    if (response.success) {
                        // Show success message
                        messageDiv
                            .addClass('success')
                            .html(response.data.message)
                            .fadeIn();

                        // Reset form
                        form[0].reset();

                        // Scroll to message (if visible)
                        if (messageDiv.length && messageDiv.is(':visible')) {
                            $('html, body').animate({
                                scrollTop: messageDiv.offset().top - 100
                            }, 500);
                        }

                        // Reload page after 2 seconds to update remaining seats
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        // Show error message
                        var errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error occurred.';
                        console.log('Error Message:', errorMsg);

                        messageDiv
                            .addClass('error')
                            .html(errorMsg)
                            .fadeIn();

                        // Re-enable submit button
                        submitBtn.prop('disabled', false).text('Register Now');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', xhr, status, error);
                    console.log('Response Text:', xhr.responseText);

                    // Show error message
                    messageDiv
                        .addClass('error')
                        .html('An error occurred. Please try again.<br>Error: ' + error)
                        .fadeIn();

                    // Re-enable submit button
                    submitBtn.prop('disabled', false).text('Register Now');
                }
            });
        });

        // Form validation
        $('input[name="email"]').on('blur', function() {
            var email = $(this).val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email && !emailRegex.test(email)) {
                $(this).css('border-color', '#f44336');
            } else {
                $(this).css('border-color', '#ddd');
            }
        });

        $('input[name="attendees"]').on('input', function() {
            var value = parseInt($(this).val());
            if (value < 1) {
                $(this).val(1);
            }
        });
    });

})(jQuery);

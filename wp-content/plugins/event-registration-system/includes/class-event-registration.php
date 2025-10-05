<?php
/**
 * Event Registration Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERS_Registration {

    public function __construct() {
        add_action('wp_ajax_event_registration', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_event_registration', array($this, 'handle_registration'));
    }

    /**
     * Handle Event Registration Form Submission
     */
    public function handle_registration() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ers_registration_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'event-registration-system')));
        }

        // Get and sanitize form data
        $event_id = isset($_POST['event_id']) ? absint($_POST['event_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $attendees = isset($_POST['attendees']) ? absint($_POST['attendees']) : 1;

        // Validation
        $errors = array();

        if (empty($event_id)) {
            $errors[] = __('Invalid event.', 'event-registration-system');
        }

        if (empty($name)) {
            $errors[] = __('Name is required.', 'event-registration-system');
        }

        if (empty($email) || !is_email($email)) {
            $errors[] = __('Valid email is required.', 'event-registration-system');
        }

        if ($attendees < 1) {
            $errors[] = __('Number of attendees must be at least 1.', 'event-registration-system');
        }

        // Check if email already registered
        if (ERS_Database::is_email_registered($event_id, $email)) {
            $errors[] = __('This email is already registered for this event.', 'event-registration-system');
        }

        // Check event capacity
        $capacity = get_post_meta($event_id, '_event_capacity', true);
        $registered = ERS_Database::get_total_attendees($event_id);
        $remaining = $capacity - $registered;

        if ($attendees > $remaining) {
            $errors[] = sprintf(
                __('Not enough seats available. Only %d seats remaining.', 'event-registration-system'),
                $remaining
            );
        }

        // Return errors if any
        if (!empty($errors)) {
            wp_send_json_error(array('message' => implode('<br>', $errors)));
        }

        // Insert registration
        $result = ERS_Database::insert_registration($event_id, $name, $email, $attendees);

        if ($result) {
            // Send confirmation email (optional feature)
            $this->send_confirmation_email($event_id, $name, $email, $attendees);

            wp_send_json_success(array(
                'message' => __('Registration successful! A confirmation email has been sent.', 'event-registration-system')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Registration failed. Please try again.', 'event-registration-system')
            ));
        }
    }

    /**
     * Send confirmation email to attendee
     */
    private function send_confirmation_email($event_id, $name, $email, $attendees) {
        $event = get_post($event_id);
        $event_date = get_post_meta($event_id, '_event_date', true);
        $event_location = get_post_meta($event_id, '_event_location', true);

        $subject = sprintf(__('Registration Confirmed: %s', 'event-registration-system'), $event->post_title);

        // Format date properly
        $formatted_date = 'Not set';
        if (!empty($event_date)) {
            $formatted_date = date('F j, Y, g:i a', strtotime($event_date));
        }

        $message = sprintf(
            __('Hello %s,

Your registration for the event "%s" has been confirmed.

Event Details:
- Event: %s
- Date: %s
- Location: %s
- Number of Attendees: %d

Thank you for registering!

Best regards,
Event Management Team', 'event-registration-system'),
            $name,
            $event->post_title,
            $event->post_title,
            $formatted_date,
            $event_location,
            $attendees
        );

        $headers = array('Content-Type: text/plain; charset=UTF-8');

        wp_mail($email, $subject, $message, $headers);
    }

    /**
     * Get remaining seats for an event
     */
    public static function get_remaining_seats($event_id) {
        $capacity = get_post_meta($event_id, '_event_capacity', true);
        $registered = ERS_Database::get_total_attendees($event_id);
        return max(0, $capacity - $registered);
    }

    /**
     * Check if event is full
     */
    public static function is_event_full($event_id) {
        return self::get_remaining_seats($event_id) <= 0;
    }
}

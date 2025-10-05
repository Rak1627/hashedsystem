<?php
/**
 * Database Handler for Event Registrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERS_Database {

    /**
     * Create custom database table for event registrations
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'event_registrations';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            attendee_name varchar(255) NOT NULL,
            attendee_email varchar(255) NOT NULL,
            number_of_attendees int(11) NOT NULL DEFAULT 1,
            registration_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_id (event_id),
            KEY attendee_email (attendee_email)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Insert registration data
     */
    public static function insert_registration($event_id, $name, $email, $attendees) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'event_registrations';

        return $wpdb->insert(
            $table_name,
            array(
                'event_id' => absint($event_id),
                'attendee_name' => sanitize_text_field($name),
                'attendee_email' => sanitize_email($email),
                'number_of_attendees' => absint($attendees),
                'registration_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );
    }

    /**
     * Get registrations for specific event
     */
    public static function get_event_registrations($event_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'event_registrations';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE event_id = %d ORDER BY registration_date DESC",
                $event_id
            )
        );
    }

    /**
     * Get total registered attendees for an event
     */
    public static function get_total_attendees($event_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'event_registrations';

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(number_of_attendees) FROM $table_name WHERE event_id = %d",
                $event_id
            )
        );

        return $result ? intval($result) : 0;
    }

    /**
     * Check if email is already registered for event
     */
    public static function is_email_registered($event_id, $email) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'event_registrations';

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE event_id = %d AND attendee_email = %s",
                $event_id,
                $email
            )
        );

        return $count > 0;
    }

    /**
     * Get all registrations (for admin)
     */
    public static function get_all_registrations() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'event_registrations';

        return $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY registration_date DESC"
        );
    }
}

<?php
/**
 * Admin Panel for Event Registrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERS_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_csv_export'));
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=event',
            __('Event Registrations', 'event-registration-system'),
            __('Registrations', 'event-registration-system'),
            'manage_options',
            'event-registrations',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $selected_event = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;

        ?>
        <div class="wrap">
            <h1><?php _e('Event Registrations', 'event-registration-system'); ?></h1>

            <div class="event-filter">
                <form method="get">
                    <input type="hidden" name="post_type" value="event">
                    <input type="hidden" name="page" value="event-registrations">

                    <label for="event_id"><?php _e('Select Event:', 'event-registration-system'); ?></label>
                    <select name="event_id" id="event_id" onchange="this.form.submit()">
                        <option value="0"><?php _e('All Events', 'event-registration-system'); ?></option>
                        <?php
                        $events = get_posts(array(
                            'post_type' => 'event',
                            'posts_per_page' => -1,
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ));

                        foreach ($events as $event) {
                            printf(
                                '<option value="%d"%s>%s</option>',
                                $event->ID,
                                selected($selected_event, $event->ID, false),
                                esc_html($event->post_title)
                            );
                        }
                        ?>
                    </select>

                    <?php if ($selected_event): ?>
                        <a href="<?php echo esc_url(add_query_arg(array('action' => 'export_csv', 'event_id' => $selected_event))); ?>" class="button button-primary">
                            <?php _e('Export CSV', 'event-registration-system'); ?>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <br>

            <?php
            if ($selected_event) {
                $this->display_event_registrations($selected_event);
            } else {
                $this->display_all_registrations();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Display registrations for specific event
     */
    private function display_event_registrations($event_id) {
        $event = get_post($event_id);
        $registrations = ERS_Database::get_event_registrations($event_id);
        $capacity = get_post_meta($event_id, '_event_capacity', true);
        $total_registered = ERS_Database::get_total_attendees($event_id);
        $remaining = $capacity - $total_registered;

        ?>
        <div class="event-stats">
            <h2><?php echo esc_html($event->post_title); ?></h2>
            <p>
                <strong><?php _e('Capacity:', 'event-registration-system'); ?></strong> <?php echo esc_html($capacity); ?><br>
                <strong><?php _e('Total Registered:', 'event-registration-system'); ?></strong> <?php echo esc_html($total_registered); ?><br>
                <strong><?php _e('Remaining Seats:', 'event-registration-system'); ?></strong> <?php echo esc_html($remaining); ?>
            </p>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'event-registration-system'); ?></th>
                    <th><?php _e('Attendee Name', 'event-registration-system'); ?></th>
                    <th><?php _e('Email', 'event-registration-system'); ?></th>
                    <th><?php _e('Number of Attendees', 'event-registration-system'); ?></th>
                    <th><?php _e('Registration Date', 'event-registration-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($registrations)): ?>
                    <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?php echo esc_html($registration->id); ?></td>
                            <td><?php echo esc_html($registration->attendee_name); ?></td>
                            <td><?php echo esc_html($registration->attendee_email); ?></td>
                            <td><?php echo esc_html($registration->number_of_attendees); ?></td>
                            <td><?php echo esc_html(date('F j, Y, g:i a', strtotime($registration->registration_date))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;"><?php _e('No registrations found.', 'event-registration-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Display all registrations
     */
    private function display_all_registrations() {
        $registrations = ERS_Database::get_all_registrations();

        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'event-registration-system'); ?></th>
                    <th><?php _e('Event', 'event-registration-system'); ?></th>
                    <th><?php _e('Attendee Name', 'event-registration-system'); ?></th>
                    <th><?php _e('Email', 'event-registration-system'); ?></th>
                    <th><?php _e('Number of Attendees', 'event-registration-system'); ?></th>
                    <th><?php _e('Registration Date', 'event-registration-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($registrations)): ?>
                    <?php foreach ($registrations as $registration): ?>
                        <?php $event = get_post($registration->event_id); ?>
                        <tr>
                            <td><?php echo esc_html($registration->id); ?></td>
                            <td><?php echo $event ? esc_html($event->post_title) : __('Unknown', 'event-registration-system'); ?></td>
                            <td><?php echo esc_html($registration->attendee_name); ?></td>
                            <td><?php echo esc_html($registration->attendee_email); ?></td>
                            <td><?php echo esc_html($registration->number_of_attendees); ?></td>
                            <td><?php echo esc_html(date('F j, Y, g:i a', strtotime($registration->registration_date))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;"><?php _e('No registrations found.', 'event-registration-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Handle CSV Export
     */
    public function handle_csv_export() {
        if (!isset($_GET['action']) || $_GET['action'] !== 'export_csv') {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'event-registration-system'));
        }

        $event_id = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;

        if (!$event_id) {
            wp_die(__('Invalid event ID.', 'event-registration-system'));
        }

        $event = get_post($event_id);
        $registrations = ERS_Database::get_event_registrations($event_id);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="event-registrations-' . sanitize_title($event->post_title) . '-' . date('Y-m-d') . '.csv"');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add CSV headers
        fputcsv($output, array(
            __('ID', 'event-registration-system'),
            __('Event Name', 'event-registration-system'),
            __('Attendee Name', 'event-registration-system'),
            __('Email', 'event-registration-system'),
            __('Number of Attendees', 'event-registration-system'),
            __('Registration Date', 'event-registration-system')
        ));

        // Add data rows
        if (!empty($registrations)) {
            foreach ($registrations as $registration) {
                fputcsv($output, array(
                    $registration->id,
                    $event->post_title,
                    $registration->attendee_name,
                    $registration->attendee_email,
                    $registration->number_of_attendees,
                    date('F j, Y, g:i a', strtotime($registration->registration_date))
                ));
            }
        }

        fclose($output);
        exit;
    }
}

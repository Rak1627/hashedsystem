<?php
/**
 * Event Custom Post Type
 */

if (!defined('ABSPATH')) {
    exit;
}

class ERS_Event_Post_Type {

    public function __construct() {
        add_action('init', array($this, 'register_event_post_type'));
        add_action('add_meta_boxes', array($this, 'add_event_meta_boxes'));
        add_action('save_post_event', array($this, 'save_event_meta'), 10, 2);
        add_filter('post_row_actions', array($this, 'add_duplicate_link'), 10, 2);
        add_action('admin_action_duplicate_event', array($this, 'duplicate_event'));
    }

    /**
     * Register Event Custom Post Type
     */
    public function register_event_post_type() {
        $labels = array(
            'name'                  => _x('Events', 'Post Type General Name', 'event-registration-system'),
            'singular_name'         => _x('Event', 'Post Type Singular Name', 'event-registration-system'),
            'menu_name'             => __('Events', 'event-registration-system'),
            'name_admin_bar'        => __('Event', 'event-registration-system'),
            'archives'              => __('Event Archives', 'event-registration-system'),
            'attributes'            => __('Event Attributes', 'event-registration-system'),
            'parent_item_colon'     => __('Parent Event:', 'event-registration-system'),
            'all_items'             => __('All Events', 'event-registration-system'),
            'add_new_item'          => __('Add New Event', 'event-registration-system'),
            'add_new'               => __('Add New', 'event-registration-system'),
            'new_item'              => __('New Event', 'event-registration-system'),
            'edit_item'             => __('Edit Event', 'event-registration-system'),
            'update_item'           => __('Update Event', 'event-registration-system'),
            'view_item'             => __('View Event', 'event-registration-system'),
            'view_items'            => __('View Events', 'event-registration-system'),
            'search_items'          => __('Search Event', 'event-registration-system'),
        );

        $args = array(
            'label'                 => __('Event', 'event-registration-system'),
            'description'           => __('Event Management System', 'event-registration-system'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-calendar-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );

        register_post_type('event', $args);
    }

    /**
     * Add Event Meta Boxes
     */
    public function add_event_meta_boxes() {
        add_meta_box(
            'event_details',
            __('Event Details', 'event-registration-system'),
            array($this, 'render_event_meta_box'),
            'event',
            'normal',
            'high'
        );
    }

    /**
     * Render Event Meta Box
     */
    public function render_event_meta_box($post) {
        wp_nonce_field('event_meta_box', 'event_meta_box_nonce');

        $event_date = get_post_meta($post->ID, '_event_date', true);
        $event_location = get_post_meta($post->ID, '_event_location', true);
        $event_capacity = get_post_meta($post->ID, '_event_capacity', true);

        // Convert stored datetime to datetime-local format (Y-m-d\TH:i)
        $datetime_value = '';
        if (!empty($event_date)) {
            $timestamp = strtotime($event_date);
            if ($timestamp) {
                $datetime_value = date('Y-m-d\TH:i', $timestamp);
            }
        }

        ?>
        <div class="event-meta-fields">
            <p>
                <label for="event_date"><strong><?php _e('Event Date:', 'event-registration-system'); ?></strong></label><br>
                <input type="datetime-local" id="event_date" name="event_date" value="<?php echo esc_attr($datetime_value); ?>" style="width: 100%;">
            </p>

            <p>
                <label for="event_location"><strong><?php _e('Event Location:', 'event-registration-system'); ?></strong></label><br>
                <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($event_location); ?>" placeholder="Enter event location" style="width: 100%;">
            </p>

            <p>
                <label for="event_capacity"><strong><?php _e('Event Capacity:', 'event-registration-system'); ?></strong></label><br>
                <input type="number" id="event_capacity" name="event_capacity" value="<?php echo esc_attr($event_capacity); ?>" placeholder="Enter maximum capacity" min="1" style="width: 100%;">
            </p>
        </div>
        <?php
    }

    /**
     * Save Event Meta Data
     */
    public function save_event_meta($post_id, $post) {
        // Check nonce
        if (!isset($_POST['event_meta_box_nonce']) || !wp_verify_nonce($_POST['event_meta_box_nonce'], 'event_meta_box')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save event date
        if (isset($_POST['event_date'])) {
            $date_input = sanitize_text_field($_POST['event_date']);

            if (!empty($date_input)) {
                // Convert datetime-local format (2025-12-25T18:00) to MySQL datetime format
                // Replace 'T' with space for proper conversion
                $date_input = str_replace('T', ' ', $date_input);
                $timestamp = strtotime($date_input);

                if ($timestamp !== false && $timestamp > 0) {
                    $mysql_date = date('Y-m-d H:i:s', $timestamp);
                    update_post_meta($post_id, '_event_date', $mysql_date);
                }
            } else {
                // If date is empty, delete the meta
                delete_post_meta($post_id, '_event_date');
            }
        }

        // Save event location
        if (isset($_POST['event_location'])) {
            update_post_meta($post_id, '_event_location', sanitize_text_field($_POST['event_location']));
        }

        // Save event capacity
        if (isset($_POST['event_capacity'])) {
            update_post_meta($post_id, '_event_capacity', absint($_POST['event_capacity']));
        }
    }

    /**
     * Add Duplicate Link to Event Row Actions
     */
    public function add_duplicate_link($actions, $post) {
        if ($post->post_type === 'event' && current_user_can('edit_posts')) {
            $url = wp_nonce_url(
                admin_url('admin.php?action=duplicate_event&post=' . $post->ID),
                'duplicate_event_' . $post->ID,
                'duplicate_nonce'
            );
            $actions['duplicate'] = '<a href="' . esc_url($url) . '">' . __('Duplicate', 'event-registration-system') . '</a>';
        }
        return $actions;
    }

    /**
     * Duplicate Event Function
     */
    public function duplicate_event() {
        if (!isset($_GET['post']) || !isset($_GET['duplicate_nonce'])) {
            wp_die(__('Invalid request', 'event-registration-system'));
        }

        $post_id = absint($_GET['post']);

        if (!wp_verify_nonce($_GET['duplicate_nonce'], 'duplicate_event_' . $post_id)) {
            wp_die(__('Security check failed', 'event-registration-system'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to duplicate events', 'event-registration-system'));
        }

        $original_post = get_post($post_id);

        if (!$original_post || $original_post->post_type !== 'event') {
            wp_die(__('Event not found', 'event-registration-system'));
        }

        // Find next available number for duplicate
        $base_title = $original_post->post_title;
        $counter = 1;

        // Check if title already has a number
        if (preg_match('/(.*)\s+(\d+)$/', $base_title, $matches)) {
            $base_title = $matches[1];
            $counter = intval($matches[2]) + 1;
        }

        // Find unique title
        global $wpdb;
        $new_title = $base_title . ' ' . $counter;

        while ($wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'event' AND post_status != 'trash'",
            $new_title
        ))) {
            $counter++;
            $new_title = $base_title . ' ' . $counter;
        }

        // Create duplicate post
        $new_post = array(
            'post_title'    => $new_title,
            'post_content'  => $original_post->post_content,
            'post_status'   => 'draft',
            'post_type'     => 'event',
            'post_author'   => get_current_user_id(),
        );

        $new_post_id = wp_insert_post($new_post);

        if ($new_post_id) {
            // Copy meta data
            $event_date = get_post_meta($post_id, '_event_date', true);
            $event_location = get_post_meta($post_id, '_event_location', true);
            $event_capacity = get_post_meta($post_id, '_event_capacity', true);

            if ($event_date) update_post_meta($new_post_id, '_event_date', $event_date);
            if ($event_location) update_post_meta($new_post_id, '_event_location', $event_location);
            if ($event_capacity) update_post_meta($new_post_id, '_event_capacity', $event_capacity);

            // Copy featured image
            $thumbnail_id = get_post_thumbnail_id($post_id);
            if ($thumbnail_id) {
                set_post_thumbnail($new_post_id, $thumbnail_id);
            }

            // Redirect to edit new event
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        } else {
            wp_die(__('Failed to duplicate event', 'event-registration-system'));
        }
    }
}

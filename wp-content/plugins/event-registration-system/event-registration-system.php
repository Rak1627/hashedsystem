<?php
/**
 * Plugin Name: Event Registration System
 * Plugin URI: https://hashedsystem.com
 * Description: Custom event management and registration system with admin panel and CSV export functionality.
 * Version: 1.0.0
 * Author: HashedSystem
 * Author URI: https://hashedsystem.com
 * License: GPL v2 or later
 * Text Domain: event-registration-system
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ERS_VERSION', '1.0.0');
define('ERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ERS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ERS_PLUGIN_DIR . 'includes/class-event-post-type.php';
require_once ERS_PLUGIN_DIR . 'includes/class-event-database.php';
require_once ERS_PLUGIN_DIR . 'includes/class-event-registration.php';
require_once ERS_PLUGIN_DIR . 'includes/class-event-admin.php';

// Activation hook - Create database table
register_activation_hook(__FILE__, array('ERS_Database', 'create_table'));

// Initialize plugin
function ers_init() {
    // Initialize custom post type
    new ERS_Event_Post_Type();

    // Initialize registration functionality
    new ERS_Registration();

    // Initialize admin panel
    if (is_admin()) {
        new ERS_Admin();
    }
}
add_action('plugins_loaded', 'ers_init');

// Enqueue plugin styles and scripts
function ers_enqueue_scripts() {
    wp_enqueue_style('ers-styles', ERS_PLUGIN_URL . 'assets/css/styles.css', array(), ERS_VERSION);

    if (is_singular('event')) {
        wp_enqueue_script('ers-registration', ERS_PLUGIN_URL . 'assets/js/registration.js', array('jquery'), ERS_VERSION, true);

        wp_localize_script('ers-registration', 'ersAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ers_registration_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'ers_enqueue_scripts');

// Admin styles and scripts
function ers_admin_enqueue_scripts($hook) {
    if ('event_page_event-registrations' === $hook) {
        wp_enqueue_style('ers-admin-styles', ERS_PLUGIN_URL . 'assets/css/admin.css', array(), ERS_VERSION);
    }
}
add_action('admin_enqueue_scripts', 'ers_admin_enqueue_scripts');

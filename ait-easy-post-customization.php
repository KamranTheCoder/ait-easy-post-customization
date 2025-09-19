<?php
/**
 * Plugin Name: AIT Easy Post Customization
 * Description: This plugin is essential for the proper theme functionality, including expiry date and custom fields management.
 * Version: 1.0.3
 * Author: Muhammad Kamran
 * License: GPL-2.0-or-later
 * Text Domain: ait-easy-post-customization
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register activation hook
register_activation_hook(__FILE__, 'aitepc_expiry_date_plugin_activate');
function aitepc_expiry_date_plugin_activate() {
    if (!get_option('aitepc_expiry_date_post_types')) {
        update_option('aitepc_expiry_date_post_types', array('post'));
    }

    if (!wp_next_scheduled('aitepc_check_expiry_date_event')) {
        wp_schedule_event(time(), 'daily', 'aitepc_check_expiry_date_event');
    }
}

// Register deactivation hook to clear cron event
register_deactivation_hook(__FILE__, 'aitepc_expiry_date_plugin_deactivate');
function aitepc_expiry_date_plugin_deactivate() {
    $timestamp = wp_next_scheduled('aitepc_check_expiry_date_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'aitepc_check_expiry_date_event');
    }
}

// Hook into cron event to check for expired posts
add_action('aitepc_check_expiry_date_event', 'aitepc_check_expired_posts');

// Function to check for expired posts and set them to draft
function aitepc_check_expired_posts() {
    $post_types = get_option('aitepc_expiry_date_post_types', array('post'));
    $tomorrow = gmdate('Ymd', strtotime('+1 day'));

    $args = array(
        'post_type' => $post_types,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_aitepc_expiry_date',
                'value' => $tomorrow,
                'compare' => '<',
                'type' => 'DATE'
            )
        )
    );

    $expired_posts = new WP_Query($args);

    if ($expired_posts->have_posts()) {
        while ($expired_posts->have_posts()) {
            $expired_posts->the_post();
            $post_id = get_the_ID();

            $updated_post = array(
                'ID' => $post_id,
                'post_status' => 'draft'
            );
            wp_update_post($updated_post);
        }
        wp_reset_postdata();
    }
}

// Load translation
add_action('plugins_loaded', 'aitepc_load_textdomain');
function aitepc_load_textdomain() {
    load_plugin_textdomain('ait-easy-post-customization', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Register settings for Settings API
add_action('admin_init', 'aitepc_register_settings');
function aitepc_register_settings() {
    register_setting('aitepc_expiry_group', 'aitepc_expiry_date_post_types', array(
        'sanitize_callback' => 'aitepc_sanitize_post_types',
        'default' => array('post')
    ));

    add_settings_section(
        'aitepc_post_types_section',
        __('Select Post Types for Expiry Date Metabox', 'ait-easy-post-customization'),
        null,
        'aitepc-expiry-date-settings'
    );

    $all_post_types = get_post_types(array('public' => true), 'objects');
    $filtered_post_types = array_filter($all_post_types, function($post_type) {
        return !in_array($post_type->name, array('page', 'attachment'));
    });

    foreach ($filtered_post_types as $post_type) {
        add_settings_field(
            'aitepc_pt_' . $post_type->name,
            $post_type->label,
            'aitepc_pt_checkbox_cb',
            'aitepc-expiry-date-settings',
            'aitepc_post_types_section',
            array('pt' => $post_type->name)
        );
    }
}

function aitepc_sanitize_post_types($input) {
    return is_array($input) ? array_map('sanitize_text_field', $input) : array();
}

function aitepc_pt_checkbox_cb($args) {
    $options = get_option('aitepc_expiry_date_post_types', array());
    $checked = in_array($args['pt'], $options) ? 'checked' : '';
    printf(
        '<input type="checkbox" name="aitepc_expiry_date_post_types[]" value="%s" %s class="form-check-input" />',
        esc_attr($args['pt']),
        $checked
    );
}

// Files include
require_once plugin_dir_path(__FILE__) . 'functions.php';
require_once plugin_dir_path(__FILE__) . 'plugin-files/aitepc-settings.php';
require_once plugin_dir_path(__FILE__) . 'plugin-files/aitepc-expiry-metabox.php';
require_once plugin_dir_path(__FILE__) . 'plugin-files/aitepc-custom-fields.php';
<?php

/**
 * Plugin Name: AIT Easy Post Customization
 * Description: This plugin is essential for the proper theme functionality.
 * Version: 1.0.0
 * Author: kamranchannar
 * License: GPL2
 * Text Domain: ait-easy-post-customization
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register activation hook
register_activation_hook(__FILE__, 'aitepc_expiry_date_plugin_activate');
function aitepc_expiry_date_plugin_activate() {
    if (!get_option('expiry_date_post_types')) {
        update_option('expiry_date_post_types', array('post'));
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
    $tomorrow = gmdate('Ymd', strtotime('+1 day')); // Changed to gmdate()

    $args = array(
        'post_type' => $post_types,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_aitepc_expiry_date', // Ensure consistency with your meta key
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

// Files include
require_once plugin_dir_path(__FILE__) . 'functions.php';
require_once plugin_dir_path(__FILE__) . 'plugin-files/aitepc-settings.php';
require_once plugin_dir_path(__FILE__) . 'plugin-files/aitepc-expiry-metabox.php';

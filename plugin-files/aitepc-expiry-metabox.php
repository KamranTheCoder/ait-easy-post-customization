<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Add Expiry Date Metabox to selected post types
function aitepc_add_expiry_date_metabox_to_selected_post_types() {
    // Get selected post types from options
    $selected_post_types = get_option('aitepc_expiry_date_post_types', array());

    // Register the metabox for each selected post type
    foreach ($selected_post_types as $post_type) {
        add_meta_box(
            'aitepc_expiry_date_metabox',        
            'Expiry Date',                
            'aitepc_expiry_date_metabox_callback', 
            $post_type,                   
            'side',                       
            'default'                     
        );
    }
}
add_action('add_meta_boxes', 'aitepc_add_expiry_date_metabox_to_selected_post_types');

// Metabox callback function
function aitepc_expiry_date_metabox_callback($post) {
    wp_nonce_field('aitepc_save_expiry_date', 'aitepc_expiry_date_nonce');
    $expiry_date = get_post_meta($post->ID, '_aitepc_expiry_date', true);
    echo '<label for="aitepc_expiry_date">Expiry Date: </label>';
    echo '<input type="date" id="aitepc_expiry_date" name="aitepc_expiry_date" value="' . esc_attr($expiry_date) . '" />';
}

// Save the Expiry Date Metabox data
function aitepc_save_expiry_date_metabox($post_id) {
    if (!isset($_POST['aitepc_expiry_date_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aitepc_expiry_date_nonce'])), 'aitepc_save_expiry_date')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (isset($_POST['aitepc_expiry_date'])) {
        $expiry_date = sanitize_text_field(wp_unslash($_POST['aitepc_expiry_date']));
        update_post_meta($post_id, '_aitepc_expiry_date', $expiry_date);
    }
}
add_action('save_post', 'aitepc_save_expiry_date_metabox');

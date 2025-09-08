<?php
/**
 * AIT Easy Post Customization - Custom Fields Metabox
 *
 * @package AIT Easy Post Customization
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add Custom Fields Metabox to selected post types
function aitepc_add_custom_fields_metabox() {
    $custom_fields = get_option('aitepc_custom_fields', array());
    if (empty($custom_fields)) {
        return;
    }

    $fields_by_pt = array();
    foreach ($custom_fields as $key => $field) {
        foreach ($field['post_types'] as $pt) {
            if (!isset($fields_by_pt[$pt])) {
                $fields_by_pt[$pt] = array();
            }
            $fields_by_pt[$pt][$key] = $field;
        }
    }

    foreach ($fields_by_pt as $pt => $fields) {
        add_meta_box(
            'aitepc_custom_fields_metabox',
            __('Custom Fields', 'ait-easy-post-customization'),
            'aitepc_custom_fields_metabox_callback',
            $pt,
            'normal',
            'default',
            array('fields' => $fields)
        );
    }
}
add_action('add_meta_boxes', 'aitepc_add_custom_fields_metabox');

// Metabox callback function
function aitepc_custom_fields_metabox_callback($post, $metabox) {
    $fields = $metabox['args']['fields'];
    wp_nonce_field('aitepc_save_custom_fields', 'aitepc_custom_fields_nonce');

    foreach ($fields as $field) {
        $meta_key = '_aitepc_' . $field['key'];
        $value = get_post_meta($post->ID, $meta_key, true);
        ?>
        <div class="mb-3">
            <label for="aitepc_<?php echo esc_attr($field['key']); ?>" class="form-label"><?php echo esc_html($field['label']); ?></label>
            <?php if ($field['type'] === 'text') : ?>
                <input type="text" class="form-control" id="aitepc_<?php echo esc_attr($field['key']); ?>" name="aitepc_<?php echo esc_attr($field['key']); ?>" value="<?php echo esc_attr($value); ?>">
            <?php elseif ($field['type'] === 'textarea') : ?>
                <textarea class="form-control" id="aitepc_<?php echo esc_attr($field['key']); ?>" name="aitepc_<?php echo esc_attr($field['key']); ?>" rows="4"><?php echo esc_textarea($value); ?></textarea>
            <?php elseif ($field['type'] === 'date') : ?>
                <input type="date" class="form-control" id="aitepc_<?php echo esc_attr($field['key']); ?>" name="aitepc_<?php echo esc_attr($field['key']); ?>" value="<?php echo esc_attr($value); ?>">
            <?php endif; ?>
        </div>
        <?php
    }
}

// Save the Custom Fields Metabox data
function aitepc_save_custom_fields_metabox($post_id) {
    if (!isset($_POST['aitepc_custom_fields_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aitepc_custom_fields_nonce'])), 'aitepc_save_custom_fields')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    $custom_fields = get_option('aitepc_custom_fields', array());

    foreach ($custom_fields as $field) {
        $input_name = 'aitepc_' . $field['key'];
        $meta_key = '_aitepc_' . $field['key'];
        if (isset($_POST[$input_name])) {
            $value = wp_unslash($_POST[$input_name]);
            switch ($field['type']) {
                case 'text':
                case 'date':
                    $value = sanitize_text_field($value);
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field($value);
                    break;
            }
            update_post_meta($post_id, $meta_key, $value);
        } else {
            delete_post_meta($post_id, $meta_key);
        }
    }
}
add_action('save_post', 'aitepc_save_custom_fields_metabox');
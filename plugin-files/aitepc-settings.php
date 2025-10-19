<?php
/**
 * AIT Easy Post Customization - Settings Page
 *
 * @package AIT Easy Post Customization
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add a separate menu page in the WordPress dashboard
function aitepc_expiry_date_menu_page() {
    $icon_url = plugins_url('assets/images/icon.svg', dirname(__FILE__));
    add_menu_page(
        __('Easy Post Customization Settings', 'ait-easy-post-customization'),
        __('Easy Post Customization', 'ait-easy-post-customization'),
        'manage_options',
        'aitepc-expiry-date-settings',
        'aitepc_expiry_date_settings_page_html',
        $icon_url,
        20
    );
}
add_action('admin_menu', 'aitepc_expiry_date_menu_page');

// Render the settings page HTML
function aitepc_expiry_date_settings_page_html() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'ait-easy-post-customization'));
    }

    // Handle custom fields actions
    aitepc_handle_custom_fields_submission();

    // Retrieve filtered post types for custom fields form
    $all_post_types = get_post_types(array('public' => true), 'objects');
    $filtered_post_types = array_filter($all_post_types, function($post_type) {
        return !in_array($post_type->name, array('page', 'attachment'));
    });

    ?>
    <div class="wrap aitepc-wrap">
        <h1 class="aitepc-title"><span class="dashicons dashicons-admin-settings me-2"></span><?php _e('Easy Post Customization', 'ait-easy-post-customization'); ?></h1>
        <ul class="nav nav-tabs aitepc-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="post-types-tab" data-bs-toggle="tab" data-bs-target="#post-types" type="button" role="tab" aria-controls="post-types" aria-selected="true">
                    <span class="dashicons dashicons-post-status me-1"></span><?php _e('Assign Metabox', 'ait-easy-post-customization'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="custom-fields-tab" data-bs-toggle="tab" data-bs-target="#custom-fields" type="button" role="tab" aria-controls="custom-fields" aria-selected="false">
                    <span class="dashicons dashicons-edit me-1"></span><?php _e('Custom Fields', 'ait-easy-post-customization'); ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="coming-soon-tab" data-bs-toggle="tab" data-bs-target="#coming-soon" type="button" role="tab" aria-controls="coming-soon" aria-selected="false">
                    <span class="dashicons dashicons-clock me-1"></span><?php _e('Coming Soon', 'ait-easy-post-customization'); ?>
                </button>
            </li>
        </ul>

        <div class="tab-content mt-4" id="myTabContent">
            <div class="tab-pane fade show active" id="post-types" role="tabpanel" aria-labelledby="post-types-tab">
                <div class="card aitepc-card">
                    <div class="card-body">
                        <h2 class="card-title"><?php _e('Assign Expiry Date Metabox', 'ait-easy-post-customization'); ?></h2>
                        <p class="card-text text-muted"><?php _e('Select the post types where the expiry date metabox should appear.', 'ait-easy-post-customization'); ?></p>
                        <form method="post" action="options.php">
                            <?php
                            settings_fields('aitepc_expiry_group');
                            do_settings_sections('aitepc-expiry-date-settings');
                            submit_button(__('Save Changes', 'ait-easy-post-customization'), 'primary aitepc-btn', 'submit', true);
                            ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="custom-fields" role="tabpanel" aria-labelledby="custom-fields-tab">
                <?php aitepc_display_custom_fields_form($filtered_post_types); ?>
            </div>

            <div class="tab-pane fade" id="coming-soon" role="tabpanel" aria-labelledby="coming-soon-tab">
                <div class="card aitepc-card text-center">
                    <div class="card-body">
                        <h2 class="card-title"><?php _e('Coming Soon', 'ait-easy-post-customization'); ?></h2>
                        <p class="card-text"><?php _e('Exciting new features are under development. Stay tuned for updates!', 'ait-easy-post-customization'); ?></p>
                        <p><?php _e('Have ideas or feedback? We’d love to hear from you.', 'ait-easy-post-customization'); ?></p>
                        <p><?php _e('Contact:', 'ait-easy-post-customization'); ?> <a href="mailto:kamran.channar99@gmail.com" class="text-decoration-none">kamran.channar99@gmail.com</a></p>
                        <div class="alert alert-info"><?php _e('Thank you for your patience and support!', 'ait-easy-post-customization'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Function to handle custom fields form submissions
function aitepc_handle_custom_fields_submission() {
    if (!isset($_POST['aitepc_custom_fields_action'])) {
        return;
    }

    if (!isset($_POST['aitepc_custom_fields_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aitepc_custom_fields_nonce'])), 'aitepc_custom_fields_action')) {
        echo '<div class="alert alert-danger" role="alert">' . __('Security check failed. Please try again.', 'ait-easy-post-customization') . '</div>';
        return;
    }

    $custom_fields = get_option('aitepc_custom_fields', array());
    $action = sanitize_text_field(wp_unslash($_POST['aitepc_custom_fields_action']));

    if ($action === 'add' || $action === 'edit') {
        $key = sanitize_key(wp_unslash($_POST['field_key']));
        $label = sanitize_text_field(wp_unslash($_POST['field_label']));
        $type = sanitize_text_field(wp_unslash($_POST['field_type']));
        $post_types = isset($_POST['field_post_types']) ? array_map('sanitize_text_field', wp_unslash($_POST['field_post_types'])) : [];

        if (empty($key) || empty($label) || empty($type)) {
            echo '<div class="alert alert-danger" role="alert">' . __('All fields are required.', 'ait-easy-post-customization') . '</div>';
            return;
        }

        if ($action === 'add' && array_key_exists($key, $custom_fields)) {
            echo '<div class="alert alert-danger" role="alert">' . __('Field key already exists.', 'ait-easy-post-customization') . '</div>';
            return;
        }

        $custom_fields[$key] = array(
            'label' => $label,
            'type' => $type,
            'post_types' => $post_types
        );

        update_option('aitepc_custom_fields', $custom_fields);
        echo '<div class="alert alert-success" role="alert">' . __('Custom field ', 'ait-easy-post-customization') . ($action === 'add' ? __('added', 'ait-easy-post-customization') : __('updated', 'ait-easy-post-customization')) . __(' successfully!', 'ait-easy-post-customization') . '</div>';
    } elseif ($action === 'delete') {
        $key = sanitize_key(wp_unslash($_POST['field_key']));
        if (array_key_exists($key, $custom_fields)) {
            unset($custom_fields[$key]);
            update_option('aitepc_custom_fields', $custom_fields);
            echo '<div class="alert alert-success" role="alert">' . __('Custom field deleted successfully!', 'ait-easy-post-customization') . '</div>';
        }
    }
}

// Function to display custom fields management
function aitepc_display_custom_fields_form($filtered_post_types) {
    $custom_fields = get_option('aitepc_custom_fields', array());
    $edit_key = isset($_GET['edit']) ? sanitize_key($_GET['edit']) : '';
    $edit_field = $edit_key && isset($custom_fields[$edit_key]) ? $custom_fields[$edit_key] : null;
    ?>
    <div class="card aitepc-card">
        <div class="card-body">
            <h2 class="card-title mb-4"><?php echo $edit_field ? __('Edit Custom Field', 'ait-easy-post-customization') : __('Add New Custom Field', 'ait-easy-post-customization'); ?></h2>
            <form method="post" class="needs-validation" novalidate>
                <?php wp_nonce_field('aitepc_custom_fields_action', 'aitepc_custom_fields_nonce'); ?>
                <input type="hidden" name="aitepc_custom_fields_action" value="<?php echo $edit_field ? 'edit' : 'add'; ?>">
                <?php if ($edit_field) : ?>
                    <input type="hidden" name="field_key" value="<?php echo esc_attr($edit_key); ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label for="field_label" class="form-label"><?php _e('Field Label', 'ait-easy-post-customization'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="field_label" name="field_label" value="<?php echo $edit_field ? esc_attr($edit_field['label']) : ''; ?>" required>
                    <div class="invalid-feedback"><?php _e('Please enter a field label.', 'ait-easy-post-customization'); ?></div>
                </div>
                <div class="mb-3">
                    <label for="field_key" class="form-label"><?php _e('Field Key (slug)', 'ait-easy-post-customization'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="field_key" name="field_key" value="<?php echo $edit_field ? esc_attr($edit_key) : ''; ?>" <?php echo $edit_field ? 'readonly' : 'required'; ?> pattern="[a-z0-9_]+">
                    <small class="form-text text-muted"><?php _e('Use lowercase letters, numbers, and underscores only. Cannot be changed after creation.', 'ait-easy-post-customization'); ?></small>
                    <div class="invalid-feedback"><?php _e('Please enter a valid field key (lowercase letters, numbers, underscores only).', 'ait-easy-post-customization'); ?></div>
                </div>
                <div class="mb-3">
                    <label for="field_type" class="form-label"><?php _e('Field Type', 'ait-easy-post-customization'); ?> <span class="text-danger">*</span></label>
                    <select class="form-select" id="field_type" name="field_type" required>
                        <option value="text" <?php selected($edit_field ? $edit_field['type'] : '', 'text'); ?>><?php _e('Text', 'ait-easy-post-customization'); ?></option>
                        <option value="textarea" <?php selected($edit_field ? $edit_field['type'] : '', 'textarea'); ?>><?php _e('Textarea', 'ait-easy-post-customization'); ?></option>
                        <option value="date" <?php selected($edit_field ? $edit_field['type'] : '', 'date'); ?>><?php _e('Date', 'ait-easy-post-customization'); ?></option>
                    </select>
                    <div class="invalid-feedback"><?php _e('Please select a field type.', 'ait-easy-post-customization'); ?></div>
                </div>
                <div class="mb-4">
                    <h5 class="mb-3"><?php _e('Select Post Types', 'ait-easy-post-customization'); ?></h5>
                    <div class="row">
                        <?php foreach ($filtered_post_types as $post_type) : 
                            $checked = $edit_field && in_array($post_type->name, $edit_field['post_types']) ? 'checked="checked"' : ''; ?>
                            <div class="col-md-4 mb-3 d-flex align-items-center">
                                <input type="checkbox" class="form-check-input me-2" name="field_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php echo esc_attr($checked); ?> id="field_<?php echo esc_attr($post_type->name); ?>">
                                <label class="form-check-label" for="field_<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->label); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary aitepc-btn"><?php echo $edit_field ? __('Update Field', 'ait-easy-post-customization') : __('Add Field', 'ait-easy-post-customization'); ?></button>
                    <?php if ($edit_field) : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=aitepc-expiry-date-settings')); ?>" class="btn btn-outline-secondary"><?php _e('Cancel', 'ait-easy-post-customization'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card aitepc-card mt-4">
        <div class="card-body">
            <h2 class="card-title mb-4"><?php _e('Existing Custom Fields', 'ait-easy-post-customization'); ?></h2>
            <?php if (!empty($custom_fields)) : ?>
                <div class="table-responsive">
                    <table class="table table-hover aitepc-table">
                        <thead>
                            <tr>
                                <th><?php _e('Label', 'ait-easy-post-customization'); ?></th>
                                <th><?php _e('Key', 'ait-easy-post-customization'); ?></th>
                                <th><?php _e('Type', 'ait-easy-post-customization'); ?></th>
                                <th><?php _e('Post Types', 'ait-easy-post-customization'); ?></th>
                                <th><?php _e('Actions', 'ait-easy-post-customization'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custom_fields as $key => $field) : ?>
                                <tr>
                                    <td><?php echo esc_html($field['label']); ?></td>
                                    <td><?php echo esc_html($key); ?></td>
                                    <td><?php echo esc_html($field['type']); ?></td>
                                    <td><?php echo esc_html(implode(', ', $field['post_types'])); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(add_query_arg('edit', $key, admin_url('admin.php?page=aitepc-expiry-date-settings'))); ?>" class="btn btn-sm btn-primary aitepc-btn me-1"><?php _e('Edit', 'ait-easy-post-customization'); ?></a>
                                        <form method="post" style="display:inline;">
                                            <?php wp_nonce_field('aitepc_custom_fields_action', 'aitepc_custom_fields_nonce'); ?>
                                            <input type="hidden" name="aitepc_custom_fields_action" value="delete">
                                            <input type="hidden" name="field_key" value="<?php echo esc_attr($key); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger aitepc-btn" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this field?', 'ait-easy-post-customization'); ?>');"><?php _e('Delete', 'ait-easy-post-customization'); ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p class="text-muted"><?php _e('No custom fields added yet.', 'ait-easy-post-customization'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Bootstrap form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
    <?php
}
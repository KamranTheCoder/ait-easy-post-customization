<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Add a separate menu page in the WordPress dashboard
function aitepc_expiry_date_menu_page() {
    add_menu_page(
        'Expiry Date Settings',
        'Expiry Date',
        'manage_options',
        'aitepc-expiry-date-settings',
        'aitepc_expiry_date_settings_page_html',
        'dashicons-calendar-alt',
        20
    );
}
add_action('admin_menu', 'aitepc_expiry_date_menu_page');

// Render the settings page HTML
function aitepc_expiry_date_settings_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save options when the form is submitted
    if (isset($_POST['aitepc_expiry_date_post_types'])) {
        // Verify the nonce
        if (!isset($_POST['aitepc_expiry_date_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aitepc_expiry_date_nonce'])), 'aitepc_expiry_date_nonce_action')) {
            echo '<div class="alert alert-danger" role="alert">Security check failed. Please try again.</div>';
            return;
        }

        // Sanitize and validate input
        $post_types = isset($_POST['aitepc_expiry_date_post_types']) ? array_map('sanitize_text_field', wp_unslash($_POST['aitepc_expiry_date_post_types'])) : [];
        
        // Update the option with the validated post types
        update_option('aitepc_expiry_date_post_types', $post_types);
        echo '<div class="alert alert-success" role="alert">Settings saved successfully!</div>';
    }

    // Retrieve current settings
    $selected_post_types = get_option('aitepc_expiry_date_post_types', array());

    // Get all built-in public post types
    $all_post_types = get_post_types(array('public' => true), 'objects');

    // Exclude 'page' and 'attachment'
    $filtered_post_types = array_filter($all_post_types, function($post_type) {
        return !in_array($post_type->name, array('page', 'attachment'));
    });

    // Display the settings form with tabs
    ?>
    <div class="container mt-4">
        <h2 class="mb-4">Expiry Date Metabox Settings</h2>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="post-types-tab" data-bs-toggle="tab" href="#post-types" role="tab" aria-controls="post-types" aria-selected="true">Assign Metabox</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="coming-soon-tab" data-bs-toggle="tab" href="#coming-soon" role="tab" aria-controls="coming-soon" aria-selected="false">Coming Soon</a>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content mt-3" id="myTabContent">
            <!-- First tab for assigning post types -->
            <div class="tab-pane fade show active" id="post-types" role="tabpanel" aria-labelledby="post-types-tab">
                <form method="post" class="border p-4 rounded shadow-sm bg-light">
                    <h4 class="mb-3">Select Post Types for Expiry Date Metabox</h4>
                    
                    <!-- Nonce field for security -->
                    <?php wp_nonce_field('aitepc_expiry_date_nonce_action', 'aitepc_expiry_date_nonce'); ?>
                    
                    <div class="row">
                        <?php foreach ($filtered_post_types as $post_type) : 
                            $checked = in_array($post_type->name, $selected_post_types) ? 'checked="checked"' : ''; ?>
                            <div class="col-md-4 mb-3 d-flex align-items-center">
                                <input type="checkbox" class="form-check-input me-2" name="aitepc_expiry_date_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php echo esc_attr($checked); ?> id="aitepc_<?php echo esc_attr($post_type->name); ?>">
                                <label class="form-check-label" for="aitepc_<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->label); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                </form>
            </div>

            <!-- Second tab for coming soon -->
            <div class="tab-pane fade" id="coming-soon" role="tabpanel" aria-labelledby="coming-soon-tab">
                <div class="text-center">
                    <h4>Coming Soon</h4>
                    <p>This feature is currently under development. Stay tuned for updates!</p>
                    <p>We’d love to hear your feedback! Let us know what features you’d like to see in the upcoming release.</p>
                    <p>Contact: <a href="mailto:kamran.channar99@gmail.com">kamran.channar99@gmail.com</a></p>
                    <div class="alert alert-info" role="alert">
                        We appreciate your patience and support!
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

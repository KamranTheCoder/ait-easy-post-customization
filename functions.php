<?php 

function aitepc_enqueue_scripts() {
    if (isset($_GET['page']) && $_GET['page'] === 'aitepc-expiry-date-settings') {
        // Enqueue local Bootstrap CSS and JS
        wp_enqueue_style('ait-bootstrap-css', plugins_url('assets/css/bootstrap.min.css', __FILE__), array(), '5.3.3');
        wp_enqueue_style('aitepc-admin-css', plugins_url('assets/css/aitepc-admin.css', __FILE__), array(), '1.0.0');
        wp_enqueue_script('ait-bootstrap-js', plugins_url('assets/js/bootstrap.bundle.min.js', __FILE__), array('jquery'), '5.3.3', true);

        wp_enqueue_style('dashicons');
    }
}
add_action('admin_enqueue_scripts', 'aitepc_enqueue_scripts');

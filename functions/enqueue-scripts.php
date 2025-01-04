<?php
/**
 * Enqueue Styles and Scripts
 */

// Register and enqueue styles
function joomana_register_styles() {
    $version = wp_get_theme()->get('Version');

    // Enqueue Ionicons CSS
    wp_enqueue_style('ionicons', 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/5.5.2/ionicons.min.css', [], null);

    // Enqueue Font Awesome CSS
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], null);

    // Main theme stylesheet
    wp_enqueue_style('joomana-style', get_stylesheet_uri(), [], $version);

    // Enqueue additional portal-specific styles
    $styles = [
        'joomana-root-style'          => 'assets/css/root.css',
        'joomana-web-portal-style'     => 'assets/css/web-portal.css',
    ];

    foreach ($styles as $handle => $path) {
        wp_enqueue_style($handle, get_template_directory_uri() . '/' . $path, [], $version);
    }

    // Enqueue Elementor styles if Elementor is active
    if (did_action('elementor/loaded')) {
        wp_enqueue_style('elementor-frontend');
        if (class_exists('ElementorPro\Plugin')) {
            wp_enqueue_style('elementor-pro-frontend');
        }
    }
}
add_action('wp_enqueue_scripts', 'joomana_register_styles');

// Register and enqueue scripts
function joomana_register_scripts() {
    $version = wp_get_theme()->get('Version');

    // Enqueue jQuery (included with WordPress core)
    wp_enqueue_script('jquery');

    // Enqueue Ionicons JS (ES module for modern browsers)
    wp_enqueue_script('ionicons-esm', 'https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js', [], null, true);

    // Enqueue Ionicons JS (nomodule for older browsers)
    wp_enqueue_script('ionicons-nomodule', 'https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js', [], null, true);

    // Enqueue custom JavaScript files
    $scripts = [
        'joomana-mobile-menu' => 'assets/js/mobile-menu.js',
        'joomana-form-validation' => 'assets/js/form-validation.js',
    ];

    foreach ($scripts as $handle => $path) {
        wp_enqueue_script($handle, get_template_directory_uri() . '/' . $path, [], $version, true);
    }
}
add_action('wp_enqueue_scripts', 'joomana_register_scripts');
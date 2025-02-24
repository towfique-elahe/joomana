<?php
/**
 * Roles Management for the Joomana Theme
 */

// 1. Remove Default WordPress Roles
function joomana_remove_default_roles() {
    $default_roles = ['author', 'editor', 'contributor', 'subscriber'];

    foreach ($default_roles as $role) {
        if (get_role($role)) {
            remove_role($role);
        }
    }
}
add_action('init', 'joomana_remove_default_roles');

// 2. Remove WooCommerce Default Roles (Conditionally)
function joomana_remove_woocommerce_roles() {
    if (class_exists('WooCommerce')) {
        $woocommerce_roles = ['customer', 'shop_manager'];

        foreach ($woocommerce_roles as $role) {
            if (get_role($role)) {
                remove_role($role);
            }
        }
    }
}
add_action('init', 'joomana_remove_woocommerce_roles');

// 3. Add Custom LMS User Roles with Base Capabilities
function joomana_add_custom_roles() {
    // Define base capabilities for all roles
    $base_caps = [
        'read'            => true,
        'edit_posts'      => false,
        'delete_posts'    => false,
        'upload_files'    => false,
    ];

    // Define admin capabilities
    $admin_caps = array_merge($base_caps, [
        'manage_options'  => true,
        'edit_users'      => true,
        'delete_users'    => true,
        'create_users'    => true,
        'list_users'      => true,
        'promote_users'   => true,
    ]);

    // Add Admin Role
    add_role(
        'admin',
        __('Admin', 'joomana'),
        $admin_caps
    );

    // Add Teacher Role
    add_role(
        'teacher',
        __('Teacher', 'joomana'),
        $base_caps
    );

    // Add Student Role
    add_role(
        'student',
        __('Student', 'joomana'),
        $base_caps
    );

    // Add Parent Role
    add_role(
        'parent',
        __('Parent', 'joomana'),
        $base_caps
    );
}
add_action('init', 'joomana_add_custom_roles');

// 4. Hide Admin Toolbar for Specific Roles
function joomana_hide_admin_toolbar($show_toolbar) {
    $roles_to_hide_toolbar = ['admin', 'teacher', 'student', 'parent'];

    foreach ($roles_to_hide_toolbar as $role) {
        if (current_user_can($role)) {
            return false;
        }
    }

    return $show_toolbar;
}
add_filter('show_admin_bar', 'joomana_hide_admin_toolbar');

// 5. Restrict Non-Admin Users from Accessing the WordPress Admin Area
function joomana_restrict_admin_dashboard() {
    if (!current_user_can('manage_options') && is_admin() && !defined('DOING_AJAX')) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_init', 'joomana_restrict_admin_dashboard');

// 6. Clean Up Custom Roles on Theme Deactivation
function joomana_cleanup_custom_roles() {
    $custom_roles = ['admin', 'teacher', 'student', 'parent'];

    foreach ($custom_roles as $role) {
        if (get_role($role)) {
            remove_role($role);
        }
    }
}
add_action('switch_theme', 'joomana_cleanup_custom_roles');
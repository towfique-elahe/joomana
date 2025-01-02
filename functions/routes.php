<?php

// function for custom routing



// Admin Portal Routes

function admin_portal_rewrite_rules() {
    add_rewrite_rule('^admin/dashboard/?$', 'index.php?admin_page=dashboard', 'top');
    add_rewrite_rule('^admin/teacher-management/?$', 'index.php?admin_page=teacher-management', 'top');
    add_rewrite_rule('^admin/student-management/?$', 'index.php?admin_page=student-management', 'top');
    add_rewrite_rule('^admin/parent-management/?$', 'index.php?admin_page=parent-management', 'top');
    add_rewrite_rule('^admin/course-management/courses/?$', 'index.php?admin_page=course-management', 'top');
    add_rewrite_rule('^admin/course-management/courses/add-course/?$', 'index.php?admin_page=add-course', 'top');
    add_rewrite_rule('^admin/course-management/courses/edit-course/?$', 'index.php?admin_page=edit-course', 'top');
    add_rewrite_rule('^admin/course-management/categories/?$', 'index.php?admin_page=course-categories', 'top');
    add_rewrite_rule('^admin/course-management/categories/edit-category/?$', 'index.php?admin_page=edit-course-category', 'top');
    add_rewrite_rule('^admin/course-management/topics/?$', 'index.php?admin_page=course-topics', 'top');
    add_rewrite_rule('^admin/course-management/topics/edit-topic/?$', 'index.php?admin_page=edit-course-topic', 'top');
    add_rewrite_rule('^admin/course-management/grades/?$', 'index.php?admin_page=course-grades', 'top');
    add_rewrite_rule('^admin/course-management/grades/edit-grade/?$', 'index.php?admin_page=edit-course-grade', 'top');
    add_rewrite_rule('^admin/course-management/levels/?$', 'index.php?admin_page=course-levels', 'top');
    add_rewrite_rule('^admin/course-management/levels/edit-level/?$', 'index.php?admin_page=edit-course-level', 'top');
    add_rewrite_rule('^admin/payments/?$', 'index.php?admin_page=payments', 'top');
    add_rewrite_rule('^admin/teacher-payments/?$', 'index.php?admin_page=teacher-payments', 'top');
    add_rewrite_rule('^admin/settings/?$', 'index.php?admin_page=settings', 'top');
}
add_action('init', 'admin_portal_rewrite_rules');

function admin_portal_query_vars($vars) {
    $vars[] = 'admin_page';
    return $vars;
}
add_filter('query_vars', 'admin_portal_query_vars');

function load_admin_portal_templates($template) {
    $admin_page = get_query_var('admin_page');
    if ($admin_page) {
        $path = get_template_directory() . "/admin/pages/{$admin_page}.php";
        if (file_exists($path)) {
            return $path;
        }
    }
    return $template;
}
add_filter('template_include', 'load_admin_portal_templates');

// restrict admin dashboard pages
function restrict_admin_pages() {
    if (get_query_var('admin_page') && !current_user_can('admin')) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'restrict_admin_pages');



// Teacher Portal Routes

function teacher_portal_rewrite_rules() {
    add_rewrite_rule('^teacher/dashboard/?$', 'index.php?teacher_page=dashboard', 'top');
    add_rewrite_rule('^teacher/course-management/?$', 'index.php?teacher_page=course-management', 'top');
    add_rewrite_rule('^teacher/student-management/?$', 'index.php?teacher_page=student-management', 'top');
    add_rewrite_rule('^teacher/resources/?$', 'index.php?teacher_page=resources', 'top');
    add_rewrite_rule('^teacher/revenues/?$', 'index.php?teacher_page=revenues', 'top');
    add_rewrite_rule('^teacher/settings/?$', 'index.php?teacher_page=settings', 'top');
}
add_action('init', 'teacher_portal_rewrite_rules');

// Add 'teacher_page' to query vars
function teacher_portal_query_vars($vars) {
    $vars[] = 'teacher_page';
    return $vars;
}
add_filter('query_vars', 'teacher_portal_query_vars');

// Load custom teacher portal templates
function load_teacher_portal_templates($template) {
    $teacher_page = get_query_var('teacher_page');
    if ($teacher_page) {
        $path = get_template_directory() . "/teacher/pages/{$teacher_page}.php";
        if (file_exists($path)) {
            return $path;
        }
    }
    return $template;
}
add_filter('template_include', 'load_teacher_portal_templates');

// Restrict teacher portal pages (optional, if you want to limit access)
function restrict_teacher_pages() {
    if (get_query_var('teacher_page') && !current_user_can('teacher')) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'restrict_teacher_pages');



// Parent Portal Routes

function parent_portal_rewrite_rules() {
    add_rewrite_rule('^parent/dashboard/?$', 'index.php?parent_page=dashboard', 'top');
    add_rewrite_rule('^parent/child-management/?$', 'index.php?parent_page=child-management', 'top');
    add_rewrite_rule('^parent/credit-management/?$', 'index.php?parent_page=credit-management', 'top');
    add_rewrite_rule('^parent/payments/?$', 'index.php?parent_page=payments', 'top');
    add_rewrite_rule('^parent/settings/?$', 'index.php?parent_page=settings', 'top');
}
add_action('init', 'parent_portal_rewrite_rules');

// Add 'parent_page' to query vars
function parent_portal_query_vars($vars) {
    $vars[] = 'parent_page';
    return $vars;
}
add_filter('query_vars', 'parent_portal_query_vars');

// Load custom parent portal templates
function load_parent_portal_templates($template) {
    $parent_page = get_query_var('parent_page');
    if ($parent_page) {
        $path = get_template_directory() . "/parent/pages/{$parent_page}.php";
        if (file_exists($path)) {
            return $path;
        }
    }
    return $template;
}
add_filter('template_include', 'load_parent_portal_templates');

// Restrict parent portal pages (optional, if you want to limit access)
function restrict_parent_pages() {
    if (get_query_var('parent_page') && !current_user_can('parent')) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'restrict_parent_pages');



// Student Portal Routes

function student_portal_rewrite_rules() {
    add_rewrite_rule('^student/dashboard/?$', 'index.php?student_page=dashboard', 'top');
    add_rewrite_rule('^student/course-management/?$', 'index.php?student_page=course-management', 'top');
    add_rewrite_rule('^student/resources/?$', 'index.php?student_page=resources', 'top');
    add_rewrite_rule('^student/progress/?$', 'index.php?student_page=progress', 'top');
    add_rewrite_rule('^student/credit-management/?$', 'index.php?student_page=credit-management', 'top');
    add_rewrite_rule('^student/payments/?$', 'index.php?student_page=payments', 'top');
    add_rewrite_rule('^student/settings/?$', 'index.php?student_page=settings', 'top');
}
add_action('init', 'student_portal_rewrite_rules');

// Add 'student_page' to query vars
function student_portal_query_vars($vars) {
    $vars[] = 'student_page';
    return $vars;
}
add_filter('query_vars', 'student_portal_query_vars');

// Load custom student portal templates
function load_student_portal_templates($template) {
    $student_page = get_query_var('student_page');
    if ($student_page) {
        $path = get_template_directory() . "/student/pages/{$student_page}.php";
        if (file_exists($path)) {
            return $path;
        }
    }
    return $template;
}
add_filter('template_include', 'load_student_portal_templates');

// Restrict student portal pages (optional, if you want to limit access)
function restrict_student_pages() {
    if (get_query_var('student_page') && !current_user_can('student')) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'restrict_student_pages');
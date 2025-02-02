<?php

// Custom Database Tables --------------------------------

function create_custom_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $course_categories_table = $wpdb->prefix . 'course_categories';
    $course_topics_table = $wpdb->prefix . 'course_topics';
    $course_grades_table = $wpdb->prefix . 'course_grades';
    $course_levels_table = $wpdb->prefix . 'course_levels';
    $courses_table = $wpdb->prefix . 'courses';
    $student_courses_table = $wpdb->prefix . 'student_courses';
    $teacher_courses_table = $wpdb->prefix . 'teacher_courses';
    $students_table = $wpdb->prefix . 'students';
    $parents_table = $wpdb->prefix . 'parents';
    $teachers_table = $wpdb->prefix . 'teachers';
    $teacher_bank_details = $wpdb->prefix . 'teacher_bank_details ';
    $credits_table = $wpdb->prefix . 'credits';
    $payments_table = $wpdb->prefix . 'payments';
    $teacher_payments_table = $wpdb->prefix . 'teacher_payments';

    // course categories table
    $course_categories_sql = "CREATE TABLE $course_categories_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        category VARCHAR(255) NOT NULL,
        course_count INT(11) NOT NULL DEFAULT 0,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // course topics table
    $course_topics_sql = "CREATE TABLE $course_topics_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        topic VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        grade VARCHAR(255) NOT NULL,
        level VARCHAR(255) NOT NULL,
        course_count INT(11) NOT NULL DEFAULT 0,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // course grades table
    $course_grades_sql = "CREATE TABLE $course_grades_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        grade VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // course levels table
    $course_levels_sql = "CREATE TABLE $course_levels_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        level VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // courses table
    $courses_sql = "CREATE TABLE $courses_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(100) NOT NULL,
        topic VARCHAR(100) NOT NULL,
        grade VARCHAR(50) NOT NULL,
        level VARCHAR(50) NOT NULL,
        max_student_groups INT(11) NOT NULL DEFAULT 25,
        max_students_per_group INT(11) NOT NULL DEFAULT 6,
        max_teachers INT(11) NOT NULL DEFAULT 25,
        duration INT(11) NOT NULL DEFAULT 2,
        assigned_teachers TEXT DEFAULT NULL,
        status ENUM('available', 'unavailable', 'pending', 'completed') NOT NULL DEFAULT 'available',
        start_date VARCHAR(20) NOT NULL,
        time_slot VARCHAR(30) NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        required_credit DECIMAL(10) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // student courses table
    $student_courses_sql = "CREATE TABLE $student_courses_table (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id BIGINT(20) UNSIGNED NOT NULL,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    teacher_id BIGINT(20) UNSIGNED NOT NULL,
    enrollment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE
    ) $charset_collate;";

    // teacher courses table
    $teacher_courses_sql = "CREATE TABLE $teacher_courses_table (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    teacher_id BIGINT(20) UNSIGNED NOT NULL,
    course_id BIGINT(20) UNSIGNED NOT NULL,
    assigned_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE
    ) $charset_collate;";

    // students table
    $students_sql = "CREATE TABLE $students_table (
        id BIGINT(20) UNSIGNED NOT NULL,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender ENUM('Masculin', 'Féminin', 'Autre') NOT NULL,
        school VARCHAR(255) NOT NULL,
        grade VARCHAR(50) NOT NULL,
        level VARCHAR(50) NOT NULL,
        subject_of_interest TEXT NOT NULL,
        available_days TEXT NOT NULL,
        monday_timeslot TEXT NULL,
        tuesday_timeslot TEXT NULL,
        wednesday_timeslot TEXT NULL,
        thursday_timeslot TEXT NULL,
        friday_timeslot TEXT NULL,
        saturday_timeslot TEXT NULL,
        sunday_timeslot TEXT NULL,
        parent_consent TEXT NOT NULL,
        credit DECIMAL(10) NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // parents table
    $parents_sql = "CREATE TABLE $parents_table (
        id BIGINT(20) UNSIGNED NOT NULL,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        zipcode VARCHAR(20) NOT NULL,
        country VARCHAR(100) NOT NULL,
        image VARCHAR(255) NULL,
        credit DECIMAL(10) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // teachers table
    $teachers_sql = "CREATE TABLE $teachers_table (
        id BIGINT(20) UNSIGNED NOT NULL,
        status ENUM('En cours', 'En révision', 'Rejeté', 'Approuvé') NOT NULL DEFAULT 'En cours',
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NULL,
        country VARCHAR(255) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(255) NOT NULL,
        postal_code VARCHAR(50) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        degree VARCHAR(255) NOT NULL,
        institute VARCHAR(255) NOT NULL,
        graduation_year YEAR NOT NULL,
        subjects_of_interest TEXT NOT NULL,
        motivation_of_joining TEXT NOT NULL,
        available_days TEXT NOT NULL,
        monday_timeslot TEXT NULL,
        tuesday_timeslot TEXT NULL,
        wednesday_timeslot TEXT NULL,
        thursday_timeslot TEXT NULL,
        friday_timeslot TEXT NULL,
        saturday_timeslot TEXT NULL,
        sunday_timeslot TEXT NULL,
        upload_cv VARCHAR(255) NULL,
        upload_doc1 VARCHAR(255) NULL,
        upload_doc2 VARCHAR(255) NULL,
        upload_doc3 VARCHAR(255) NULL,
        upload_doc4 VARCHAR(255) NULL,
        upload_doc5 VARCHAR(255) NULL,
        upload_video VARCHAR(255) NULL,
        signature TEXT NOT NULL,
        signature_date DATE NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        due DECIMAL(10,2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // teacher_bank_details table
    $teacher_bank_details_sql = "CREATE TABLE $teacher_bank_details (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        teacher_id BIGINT(20) UNSIGNED NOT NULL,
        bank_name VARCHAR(255) NOT NULL,
        account_number VARCHAR(50) NOT NULL,
        account_holder VARCHAR(255) NOT NULL,
        account_type VARCHAR(50) NOT NULL,
        swift_code VARCHAR(50) NOT NULL,
        bank_address TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // Improved credits table
    $credits_sql = "CREATE TABLE $credits_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        credit DECIMAL(10) UNSIGNED NOT NULL,
        transaction_type ENUM('Crédité', 'Débité') NOT NULL,
        transaction_reason ENUM('Crédit acheté', 'Cours acheté', 'Autre') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (user_id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // payments table
    $payments_sql = "CREATE TABLE $payments_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        invoice_number VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        credit DECIMAL(10) NOT NULL,
        currency VARCHAR(10) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status ENUM('En attente', 'Complété', 'Échoué') NOT NULL DEFAULT 'En attente',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // teacher payments table
    $teacher_payments_sql = "CREATE TABLE $teacher_payments_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        invoice_number VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        currency VARCHAR(10) NOT NULL,
        due DECIMAL(10,2) NOT NULL,
        deposit DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status ENUM('En attente', 'Complété', 'Échoué') NOT NULL DEFAULT 'En attente',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($course_categories_sql);
    dbDelta($course_topics_sql);
    dbDelta($course_grades_sql);
    dbDelta($course_levels_sql);
    dbDelta($courses_sql);
    dbDelta($student_courses_sql);
    dbDelta($teacher_courses_sql);
    dbDelta($students_sql);
    dbDelta($parents_sql);
    dbDelta($teachers_sql);
    dbDelta($teacher_bank_details_sql);
    dbDelta($credits_sql);
    dbDelta($payments_sql);
    dbDelta($teacher_payments_sql);
}
add_action('after_setup_theme', 'create_custom_tables');
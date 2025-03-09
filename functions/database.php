<?php
/**
 * Custom Database Tables Setup
**/

function create_custom_tables() {
    global $wpdb;

    // Set your current custom schema version.
    $custom_tables_version = '1.1.1';
    $installed_version = get_option('custom_tables_version');

    // If the version is already current, do nothing.
    if ( $installed_version === $custom_tables_version ) {
        return;
    }

    $charset_collate = $wpdb->get_charset_collate();

    // Table names.
    $course_categories_table   = $wpdb->prefix . 'course_categories';
    $course_topics_table       = $wpdb->prefix . 'course_topics';
    $course_grades_table       = $wpdb->prefix . 'course_grades';
    $course_levels_table       = $wpdb->prefix . 'course_levels';
    $courses_table             = $wpdb->prefix . 'courses';
    $communications_table      = $wpdb->prefix . 'communications';
    $student_courses_table     = $wpdb->prefix . 'student_courses';
    $teacher_courses_table     = $wpdb->prefix . 'teacher_courses';
    $teacher_evaluations_table = $wpdb->prefix . 'teacher_evaluations';
    $course_class_links_table  = $wpdb->prefix . 'course_class_links';
    $course_assignments_table  = $wpdb->prefix . 'course_assignments';
    $course_slides_table       = $wpdb->prefix . 'course_slides';
    $students_table            = $wpdb->prefix . 'students';
    $student_reports_table     = $wpdb->prefix . 'student_reports';
    $student_submissions_table = $wpdb->prefix . 'student_submissions';
    $parents_table             = $wpdb->prefix . 'parents';
    $teachers_table            = $wpdb->prefix . 'teachers';
    $teacher_bank_details      = $wpdb->prefix . 'teacher_bank_details';
    $credits_table             = $wpdb->prefix . 'credits';
    $payments_table            = $wpdb->prefix . 'payments';
    $teacher_payments_table    = $wpdb->prefix . 'teacher_payments';

    // SQL for each table.
    $course_categories_sql = "CREATE TABLE $course_categories_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        category VARCHAR(255) NOT NULL,
        course_count INT(11) NOT NULL DEFAULT 0,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

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

    $course_grades_sql = "CREATE TABLE $course_grades_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        grade VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $course_levels_sql = "CREATE TABLE $course_levels_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        level VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

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
        course_material VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $communications_sql = "CREATE TABLE $communications_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        group_number INT(11) NOT NULL,
        message TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $student_courses_sql = "CREATE TABLE $student_courses_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        group_number INT(11) NOT NULL, 
        student_id BIGINT(20) UNSIGNED NOT NULL,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        teacher_id BIGINT(20) UNSIGNED NOT NULL,
        status ENUM('En cours', 'Complété') NOT NULL DEFAULT 'En cours',
        enrollment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $teacher_courses_sql = "CREATE TABLE $teacher_courses_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        group_number INT(11) NOT NULL,
        teacher_id BIGINT(20) UNSIGNED NOT NULL,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        status ENUM('En cours', 'Complété') NOT NULL DEFAULT 'En cours',
        assigned_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $teacher_evaluations_sql = "CREATE TABLE $teacher_evaluations_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        group_number INT(11) NOT NULL,
        teacher_id BIGINT(20) UNSIGNED NOT NULL,
        student_id BIGINT(20) UNSIGNED NOT NULL,
        rating INT(11) NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $course_class_links_sql = "CREATE TABLE $course_class_links_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        group_number INT(11) NOT NULL,
        class_link VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $course_assignments_sql = "CREATE TABLE $course_assignments_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        group_number INT(11) NOT NULL,
        teacher_id BIGINT(20) UNSIGNED NOT NULL,
        deadline DATETIME DEFAULT NULL,
        file VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $course_slides_sql = "CREATE TABLE $course_slides_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        group_number INT(11) NOT NULL,
        teacher_id BIGINT(20) UNSIGNED NOT NULL,
        file VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $students_sql = "CREATE TABLE $students_table (
        id BIGINT(20) UNSIGNED NOT NULL,
        parent_id BIGINT(20) UNSIGNED NULL,
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
        PRIMARY KEY (id)
    ) $charset_collate;";

    $student_reports_sql = "CREATE TABLE $student_reports_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        group_number INT(11) NOT NULL,
        teacher_id BIGINT(20) UNSIGNED NOT NULL,
        student_id BIGINT(20) UNSIGNED NOT NULL,
        comment ENUM('Excellent', 'Bon', 'Moyen', 'Faible') DEFAULT NULL,
        file VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $student_submissions_sql = "CREATE TABLE $student_submissions_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        course_id BIGINT(20) UNSIGNED NOT NULL,
        group_number INT(11) NOT NULL,
        student_id BIGINT(20) UNSIGNED NOT NULL,
        file VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

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
        PRIMARY KEY (id)
    ) $charset_collate;";

    $teachers_sql = "CREATE TABLE $teachers_table (
        id BIGINT(20) UNSIGNED NOT NULL,
        status ENUM('En cours', 'En révision', 'Rejeté', 'Approuvé') NOT NULL DEFAULT 'En cours',
        civility ENUM('Mr', 'Mrs') NOT NULL,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        date_of_birth DATE NOT NULL,
        email VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NULL,
        how_found VARCHAR(255) NULL,
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
        PRIMARY KEY (id)
    ) $charset_collate;";

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
        PRIMARY KEY (id)
    ) $charset_collate;";

    $credits_sql = "CREATE TABLE $credits_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        credit DECIMAL(10) UNSIGNED NOT NULL,
        transaction_type ENUM('Crédité', 'Débité') NOT NULL,
        transaction_reason ENUM('Crédit acheté', 'Cours acheté', 'Autre') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

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
        PRIMARY KEY (id)
    ) $charset_collate;";

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
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Run the table creation/upgrades.
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($course_categories_sql);
    dbDelta($course_topics_sql);
    dbDelta($course_grades_sql);
    dbDelta($course_levels_sql);
    dbDelta($courses_sql);
    dbDelta($communications_sql);
    dbDelta($student_courses_sql);
    dbDelta($teacher_courses_sql);
    dbDelta($teacher_evaluations_sql);
    dbDelta($course_class_links_sql);
    dbDelta($course_assignments_sql);
    dbDelta($course_slides_sql);
    dbDelta($students_sql);
    dbDelta($student_reports_sql);
    dbDelta($student_submissions_sql);
    dbDelta($parents_sql);
    dbDelta($teachers_sql);
    dbDelta($teacher_bank_details_sql);
    dbDelta($credits_sql);
    dbDelta($payments_sql);
    dbDelta($teacher_payments_sql);

    // Add foreign keys after tables are created.
    add_foreign_keys();

    // Update the stored version to indicate that our tables are current.
    update_option('custom_tables_version', $custom_tables_version);
}
add_action('after_setup_theme', 'create_custom_tables');


// Function to add foreign keys.
function add_foreign_keys() {
    global $wpdb;

    $constraints = [
        "{$wpdb->prefix}communications" => [
            "fk_communications_user_id"   => "ALTER TABLE {$wpdb->prefix}communications ADD CONSTRAINT fk_communications_user_id FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE;",
            "fk_communications_course_id" => "ALTER TABLE {$wpdb->prefix}communications ADD CONSTRAINT fk_communications_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}students" => [
            "fk_students_parent_id"  => "ALTER TABLE {$wpdb->prefix}students ADD CONSTRAINT fk_students_parent_id FOREIGN KEY (parent_id) REFERENCES {$wpdb->prefix}parents(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}student_courses" => [
            "fk_student_courses_student_id"  => "ALTER TABLE {$wpdb->prefix}student_courses ADD CONSTRAINT fk_student_courses_student_id FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}students(id) ON DELETE CASCADE;",
            "fk_student_courses_course_id"   => "ALTER TABLE {$wpdb->prefix}student_courses ADD CONSTRAINT fk_student_courses_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;",
            "fk_student_courses_teacher_id"  => "ALTER TABLE {$wpdb->prefix}student_courses ADD CONSTRAINT fk_student_courses_teacher_id FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}student_reports" => [
            "fk_student_reports_student_id"  => "ALTER TABLE {$wpdb->prefix}student_reports ADD CONSTRAINT fk_student_reports_student_id FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}students(id) ON DELETE CASCADE;",
            "fk_student_reports_course_id"   => "ALTER TABLE {$wpdb->prefix}student_reports ADD CONSTRAINT fk_student_reports_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;",
            "fk_student_reports_teacher_id"  => "ALTER TABLE {$wpdb->prefix}student_reports ADD CONSTRAINT fk_student_reports_teacher_id FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}teacher_courses" => [
            "fk_teacher_courses_teacher_id"  => "ALTER TABLE {$wpdb->prefix}teacher_courses ADD CONSTRAINT fk_teacher_courses_teacher_id FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE;",
            "fk_teacher_courses_course_id"   => "ALTER TABLE {$wpdb->prefix}teacher_courses ADD CONSTRAINT fk_teacher_courses_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}teacher_evaluations" => [
            "fk_teacher_evaluations_teacher_id"  => "ALTER TABLE {$wpdb->prefix}teacher_evaluations ADD CONSTRAINT fk_teacher_evaluations_teacher_id FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE;",
            "fk_teacher_evaluations_student_id"  => "ALTER TABLE {$wpdb->prefix}teacher_evaluations ADD CONSTRAINT fk_teacher_evaluations_student_id FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}students(id) ON DELETE CASCADE;",
            "fk_teacher_evaluations_course_id"   => "ALTER TABLE {$wpdb->prefix}teacher_evaluations ADD CONSTRAINT fk_teacher_evaluations_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}course_assignments" => [
            "fk_course_assignments_teacher_id"  => "ALTER TABLE {$wpdb->prefix}course_assignments ADD CONSTRAINT fk_course_assignments_teacher_id FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE;",
            "fk_course_assignments_course_id"   => "ALTER TABLE {$wpdb->prefix}course_assignments ADD CONSTRAINT fk_course_assignments_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}student_submissions" => [
            "fk_student_submissions_student_id"  => "ALTER TABLE {$wpdb->prefix}student_submissions ADD CONSTRAINT fk_student_submissions_student_id FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}students(id) ON DELETE CASCADE;",
            "fk_student_submissions_course_id"   => "ALTER TABLE {$wpdb->prefix}student_submissions ADD CONSTRAINT fk_student_submissions_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}course_class_links" => [
            "fk_course_class_links_course_id"   => "ALTER TABLE {$wpdb->prefix}course_class_links ADD CONSTRAINT fk_course_class_links_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;"
        ],
        "{$wpdb->prefix}course_slides" => [
            "fk_course_slides_teacher_id"  => "ALTER TABLE {$wpdb->prefix}course_slides ADD CONSTRAINT fk_course_slides_teacher_id FOREIGN KEY (teacher_id) REFERENCES {$wpdb->prefix}teachers(id) ON DELETE CASCADE;",
            "fk_course_slides_course_id"   => "ALTER TABLE {$wpdb->prefix}course_slides ADD CONSTRAINT fk_course_slides_course_id FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}courses(id) ON DELETE CASCADE;"
        ]
    ];

    foreach ($constraints as $table => $foreign_keys) {
        foreach ($foreign_keys as $constraint_name => $query) {
            // Check if the constraint already exists.
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND CONSTRAINT_NAME = %s",
                    $table,
                    $constraint_name
                )
            );

            if ( ! $exists ) {
                $wpdb->query($query);
            }
        }
    }
}
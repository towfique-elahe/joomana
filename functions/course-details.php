<?php
// Course Details Page

// shortcode [course_details]

function render_course_details_section() {
    global $wpdb;

    // Get the current user
    $user = wp_get_current_user();

    $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($course_id <= 0) {
        return '<p>Course not found.</p>';
    }

    $course = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}courses WHERE id = %d",
            $course_id
        )
    );

    // Check if the course exists
    if ($course) {
        // Extract the start date and time slot
        $start_date = strtotime($course->start_date); // Convert start date to a Unix timestamp
        $year = date('Y', $start_date); // Get the year
        $month = date('m', $start_date); // Get the month (numeric)
        $day = date('d', $start_date); // Get the day
        $time_slot = $course->time_slot; // The time slot

        // Define months in French for the month name
        $months = [
            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
            '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
            '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
        ];

        // Time slots in the format of the table
        $time_slots = [
            '8:00 AM - 10:00 AM', '10:00 AM - 12:00 PM', '12:00 PM - 2:00 PM',
            '2:00 PM - 4:00 PM', '4:00 PM - 6:00 PM', '6:00 PM - 8:00 PM',
            '8:00 PM - 10:00 PM', '10:00 PM - 12:00 AM', '12:00 AM - 2:00 AM',
            '2:00 AM - 4:00 AM', '4:00 AM - 6:00 AM', '6:00 AM - 8:00 AM'
        ];

        // Handle enrollment form submission for students
        if (in_array('student', (array) $user->roles)) {
            if (isset($_POST['enroll_student'])) {
                $student_id = get_current_user_id();
                $result = enroll_student_in_course($course_id, $student_id);
    
                if (is_wp_error($result)) {
                    $error_message = $result->get_error_message();
                    echo '<div class="course-error-message">' . esc_html($error_message) . '</div>';
                } else {
                    echo '<div class="course-success-message">Vous êtes inscrit avec succès à ce cours.</div>';
                    
                    // Redirect after a short delay to ensure the message is seen
                    echo '<script>
                        setTimeout(function () {
                            window.location.href = "' . home_url('/student/course-management') . '";
                        }, 2000); // Redirect after 2 seconds
                    </script>';
                }
            }
        }

        // Handle enrollment form submission for parents
        if (in_array('parent', (array) $user->roles)) {
            $parent_id = get_current_user_id(); // Get currently logged-in parent ID

            // Query the custom 'students' table for current parents children
            $childs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}students WHERE parent_id = $parent_id");
            
            if (isset($_POST['enroll_child'])) {
                $student_id = sanitize_text_field($_POST['student_id']);
                $result = enroll_child_in_course($course_id, $student_id, $parent_id);
    
                if (is_wp_error($result)) {
                    $error_message = $result->get_error_message();
                    echo '<div class="course-error-message">' . esc_html($error_message) . '</div>';
                } else {
                    echo '<div class="course-success-message">Vous êtes inscrit avec succès à ce cours.</div>';
                    
                    // Redirect after a short delay to ensure the message is seen
                    echo '<script>
                        setTimeout(function () {
                            window.location.href = "' . home_url('/student/course-management') . '";
                        }, 2000); // Redirect after 2 seconds
                    </script>';
                }
            }
        }

        ob_start();
        ?>
<div id="courseDetails" class="container row">
    <div class="left-column">
        <h2 class="title">
            <?php echo esc_html($course->title); ?>
        </h2>
        <div class="description">
            <?php echo $course->description; ?>
        </div>

        <!-- Calendar Section -->
        <div class="calendar col">
            <div class="calendar-header row">
                <div class="start-date">
                    <p class="start-month">
                        <?php echo esc_html($months[$month]); ?>
                    </p>
                    <p class="start-year">
                        <?php echo $year; ?>
                    </p>
                </div>
            </div>

            <table class="table calendar-table">
                <tbody>
                    <tr>
                        <td>Lundi</td>
                        <td>Mardi</td>
                        <td>Mercredi</td>
                        <td>Jeudi</td>
                        <td>Vendredi</td>
                        <td>Samedi</td>
                        <td>Dimanche</td>
                        <td rowspan="6" class="special-heading">Date de début</td>
                    </tr>
                    <?php
                    // Generate the calendar for the selected month
                    $days_in_month = date('t', $start_date); // Number of days in the month
                    $first_day_of_month = date('N', strtotime("{$year}-{$month}-01")); // First day of the month

                    $current_day = 1;
                    for ($i = 1; $i <= 6; $i++) {
                        echo "<tr>";

                        for ($j = 1; $j <= 7; $j++) {
                            if (($i == 1 && $j >= $first_day_of_month) || ($i > 1 && $current_day <= $days_in_month)) {
                                $day_class = ($current_day == $day) ? 'class="current-month selected-date"' : 'class="current-month"';
                                echo "<td {$day_class}>{$current_day}</td>";
                                $current_day++;
                            } else {
                                echo "<td></td>";
                            }
                        }

                        echo "</tr>";
                        if ($current_day > $days_in_month) {
                            break;
                        }
                    }
                    ?>
                </tbody>
            </table>

            <!-- Time Slot Table -->
            <div class="time-table-section">
                <table class="table time-table">
                    <tbody>
                        <?php
                // Loop through the time slots and display them in 3 rows with 4 columns
                $slot_chunks = array_chunk($time_slots, 4);
                foreach ($slot_chunks as $slots_row) {
                    echo '<tr>';
                    foreach ($slots_row as $slot) {
                        $selected_class = ($slot == $time_slot) ? 'class="selected-time"' : '';
                        echo "<td {$selected_class}>{$slot}</td>";
                    }
                    echo '</tr>';
                }
                ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Section -->
    <div class="right-column col">
        <div class="course-info">
            <img class="course-image"
                src="<?php echo esc_url( ! empty( $course->image ) ? $course->image : get_template_directory_uri() . '/assets/image/image-placeholder.png' ); ?>"
                alt="Course image" width="400" height="300" />
            <h3 class="info-heading">Le cours comprend:</h3>
            <ul class="info-list">
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-tag"></i> Prix:
                    </span>
                    <span class="item-value price">€ 10</span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <i class="far fa-credit-card"></i> Crédit:
                    </span>
                    <span class="item-value">
                        <?php echo esc_html($course->required_credit); ?> credit
                    </span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-hourglass-half"></i> Durée:
                    </span>
                    <span class="item-value">
                        <?php echo esc_html($course->duration); ?> heures
                    </span>
                </li>
                <?php
                    $teacher_courses_table = "{$wpdb->prefix}teacher_courses";
                    $teacher_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $teacher_courses_table WHERE course_id = %d",
                        $course_id
                    ));
                    if (!$teacher_count) {
                        $teacher_count = 'n/a';
                    }
                ?>
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-user-tie"></i> Enseignants:
                    </span>
                    <span class="item-value">
                        <?php echo esc_html($teacher_count); ?>
                    </span>
                </li>
                <?php
                    $student_courses_table = "{$wpdb->prefix}student_courses";
                    $student_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $student_courses_table WHERE course_id = %d",
                        $course_id
                    ));
                    if (!$student_count) {
                        $student_count = 'n/a';
                    }
                ?>
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-user-graduate"></i> Étudiants:
                    </span>
                    <span class="item-value">
                        <?php echo esc_html($student_count); ?>
                    </span>
                </li>
            </ul>
            <?php
                if (in_array('parent', (array) $user->roles)) {
            ?>
            <div class="buttons">
                <!-- Enrollment Form -->
                <form method="post" action="">
                    <input type="hidden" name="enroll_child" value="1">
                    <?php if (!empty($childs)) : ?>
                    <label for="student_id">Choisissez un enfant:</label>
                    <div class="custom-select-wrapper">
                        <select name="student_id" id="student_id">
                            <?php foreach ($childs as $child) : ?>
                            <option value="<?php echo esc_attr($child->id); ?>">
                                <?php echo esc_html($child->first_name) . ' ' . esc_html($child->last_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <ion-icon name="chevron-down-outline" class="custom-arrow"></ion-icon>
                    </div>
                    <?php else : ?>
                    <label for="student_id">Choisissez un enfant:</label>
                    <div class="custom-select-wrapper">
                        <select name="student_id" id="student_id">
                            <option value="" selected disabled>Aucun enfant trouvé</option>
                        </select>
                        <ion-icon name="chevron-down-outline" class="custom-arrow"></ion-icon>
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="button buy-now">
                        <ion-icon name="bag-handle-outline"></ion-icon> Inscrire un enfant
                    </button>
                </form>
            </div>
            <?php
                } else {
            ?>
            <div class="buttons">
                <!-- Enrollment Form -->
                <form method="post" action="">
                    <input type="hidden" name="enroll_student" value="1">
                    <button type="submit" class="button buy-now">
                        <ion-icon name="bag-handle-outline"></ion-icon> Inscrire
                    </button>
                </form>
            </div>
            <?php
                }
            ?>
            <div class="share">
                Partager sur:
                <div class="social-buttons">
                    <!-- Facebook Share -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
                        target="_blank" class="social-link" title="Share on Facebook">
                        <ion-icon name="logo-facebook"></ion-icon>
                    </a>

                    <!-- Instagram Share -->
                    <a href="https://www.instagram.com/" target="_blank" class="social-link" title="Share on Instagram">
                        <ion-icon name="logo-instagram"></ion-icon>
                    </a>

                    <!-- LinkedIn Share -->
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>&summary=<?php echo urlencode(get_the_excerpt()); ?>"
                        target="_blank" class="social-link" title="Share on LinkedIn">
                        <ion-icon name="logo-linkedin"></ion-icon>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome CSS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<?php
        return ob_get_clean();
    } else {
        return '<p>Course not found.</p>';
    }
}
add_shortcode('course_details', 'render_course_details_section');


// Student Enrollment Function
function enroll_student_in_course($course_id, $student_id) {
    global $wpdb;

    // Get course details
    $course = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}courses WHERE id = %d", $course_id)
    );

    if (!$course) {
        return new WP_Error('course_not_found', 'Cours non trouvé.');
    }

    // Get all teachers assigned to this course along with their groups, ordered by group_number
    $teachers = $wpdb->get_results(
        $wpdb->prepare("SELECT teacher_id, group_number FROM {$wpdb->prefix}teacher_courses WHERE course_id = %d ORDER BY group_number ASC", $course_id)
    );

    if (empty($teachers)) {
        
        return new WP_Error('no_teachers_assigned', "Aucun enseignant n'est affecté à ce cours.");
        
    } else {
        // Check if the student is already enrolled in this course
        $is_enrolled = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d", $student_id, $course_id)
        );

        if ($is_enrolled) {
            return new WP_Error('already_enrolled', 'Vous êtes déjà inscrit à ce cours.');
        }

        // Check if the student has sufficient credits
        $student_credit = $wpdb->get_var(
            $wpdb->prepare("SELECT credit FROM {$wpdb->prefix}students WHERE id = %d", $student_id)
        );

        if ($student_credit < $course->required_credit) {
            return new WP_Error('insufficient_credit', "Crédit insuffisant pour s'inscrire à ce cours.");
        }

        // Deduct credits from the student
        $wpdb->query(
            $wpdb->prepare("UPDATE {$wpdb->prefix}students SET credit = credit - %f WHERE id = %d", $course->required_credit, $student_id)
        );

        // Insert data into the credits table
        $credits_table = $wpdb->prefix . 'credits';
        $total_credit = $course->required_credit;
        $wpdb->insert(
            $credits_table,
            [
                'user_id' => $student_id,
                'credit' => $total_credit, // Total credit from all products in the order
                'transaction_type' => 'Débité', // Set transaction_type to 'Débité'
                'transaction_reason' => 'Cours acheté', // Set transaction_reason to 'Débité'
                'created_at' => current_time('mysql'), // Current timestamp
            ],
            [
                '%d', // user_id
                '%f', // credit
                '%s', // transaction_type
                '%s', // transaction_reason
                '%s', // created_at
            ]
        );
    }

    // Assign student to the next available group
    $assigned = false;

    foreach ($teachers as $teacher) {
        // Count students in the current group
        $student_count = $wpdb->get_var(
        $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}student_courses 
                WHERE teacher_id = %d AND course_id = %d AND group_number = %d",
                $teacher->teacher_id, $course_id, $teacher->group_number
            )
        );

        if ($student_count < $course->max_students_per_group) {
            // Assign student to this group
            $wpdb->insert(
                "{$wpdb->prefix}student_courses",
                array(
                    'student_id'   => $student_id,
                    'course_id'    => $course_id,
                    'teacher_id'   => $teacher->teacher_id,
                    'group_number' => $teacher->group_number
                ),
                array('%d', '%d', '%d', '%d')
            );
            $assigned = true;
            break;
        }

        if (!$assigned) {
            // If no group has space, return an error or handle it as per your requirement
            return new WP_Error('no_available_groups', "Tous les groupes sont complets. Impossible d'inscrire l'élève.");
        }
    }

    return true;
}

// Student Enrollment Function
function enroll_child_in_course($course_id, $student_id, $parent_id) {
    global $wpdb;

    // Get course details
    $course = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}courses WHERE id = %d", $course_id)
    );

    if (!$course) {
        return new WP_Error('course_not_found', 'Cours non trouvé.');
    }

    // Get all teachers assigned to this course along with their groups, ordered by group_number
    $teachers = $wpdb->get_results(
        $wpdb->prepare("SELECT teacher_id, group_number FROM {$wpdb->prefix}teacher_courses WHERE course_id = %d ORDER BY group_number ASC", $course_id)
    );

    if (empty($teachers)) {
        
        return new WP_Error('no_teachers_assigned', "Aucun enseignant n'est affecté à ce cours.");
        
    } else {
        
        // Check if the student is already enrolled in this course
        $is_enrolled = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d", $student_id, $course_id)
        );

        if ($is_enrolled) {
            return new WP_Error('already_enrolled', 'Vous êtes déjà inscrit à ce cours.');
        }

        // Check if the parent has sufficient credits
        $parent_credit = $wpdb->get_var(
            $wpdb->prepare("SELECT credit FROM {$wpdb->prefix}parents WHERE id = %d", $parent_id)
        );

        if ($parent_credit < $course->required_credit) {
            return new WP_Error('insufficient_credit', "Crédit insuffisant pour s'inscrire à ce cours.");
        }

        // Deduct credits from the parent
        $wpdb->query(
            $wpdb->prepare("UPDATE {$wpdb->prefix}parents SET credit = credit - %f WHERE id = %d", $course->required_credit, $parent_id)
        );

        // Insert data into the credits table
        $credits_table = $wpdb->prefix . 'credits';
        $total_credit = $course->required_credit;
        $wpdb->insert(
            $credits_table,
            [
                'user_id' => $parent_id,
                'credit' => $total_credit, // Total credit from all products in the order
                'transaction_type' => 'Débité', // Set transaction_type to 'Débité'
                'transaction_reason' => 'Cours acheté', // Set transaction_reason to 'Débité'
                'created_at' => current_time('mysql'), // Current timestamp
            ],
            [
                '%d', // user_id
                '%f', // credit
                '%s', // transaction_type
                '%s', // transaction_reason
                '%s', // created_at
            ]
        );

        // Assign student to the next available group
        $assigned = false;

        foreach ($teachers as $teacher) {
            // Count students in the current group
            $student_count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}student_courses 
                    WHERE teacher_id = %d AND course_id = %d AND group_number = %d",
                    $teacher->teacher_id, $course_id, $teacher->group_number
                )
            );

            if ($student_count < $course->max_students_per_group) {
                // Assign student to this group
                $wpdb->insert(
                    "{$wpdb->prefix}student_courses",
                    array(
                        'student_id'   => $student_id,
                        'course_id'    => $course_id,
                        'teacher_id'   => $teacher->teacher_id,
                        'group_number' => $teacher->group_number
                    ),
                    array('%d', '%d', '%d', '%d')
                );
                $assigned = true;
                break;
            }
        }

        if (!$assigned) {
            // If no group has space, return an error or handle it as per your requirement
            return new WP_Error('no_available_groups', "Tous les groupes sont complets. Impossible d'inscrire l'élève.");
        }
    }

    return true;
}
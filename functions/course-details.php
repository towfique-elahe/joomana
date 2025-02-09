<?php
// Course Details Page

// shortcode [course_details]

function render_course_details_section() {
    global $wpdb;

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

        // Handle enrollment form submission
        if (isset($_POST['enroll_student'])) {
            $student_id = get_current_user_id();
            $result = enroll_student_in_course($course_id, $student_id);

            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
                echo '<div class="error-message">' . esc_html($error_message) . '</div>';
            } else {
                echo '<div class="success-message">You have successfully enrolled in this course.</div>';
            }
        }

        ob_start();
        ?>
<div id="courseDetails" class="container row">
    <div class="left-column">
        <h2 class="title"><?php echo esc_html($course->title); ?></h2>
        <div class="description">
            <p><?php echo esc_html($course->description); ?></p>
        </div>

        <!-- Calendar Section -->
        <div class="calendar col">
            <div class="calendar-header row">
                <div class="start-date">
                    <p class="start-month"><?php echo esc_html($months[$month]); ?></p>
                    <p class="start-year"><?php echo $year; ?></p>
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
                src="<?php echo esc_url( ! empty( $course->image_url ) ? $course->image_url : get_template_directory_uri() . '/assets/image/image-placeholder.png' ); ?>"
                alt="Course image" width="400" height="300" />
            <h3 class="info-heading">Le cours comprend:</h3>
            <ul class="info-list">
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="pricetag-outline"></ion-icon> Prix:
                    </span>
                    <span class="item-value price">€ 10</span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="card-outline"></ion-icon> Crédit:
                    </span>
                    <span class="item-value"><?php echo esc_html($course->required_credit); ?> credit</span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="time-outline"></ion-icon> Durée:
                    </span>
                    <span class="item-value"><?php echo esc_html($course->duration); ?> heures</span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="people-outline"></ion-icon> Étudiants:
                    </span>
                    <span class="item-value">n/a</span>
                </li>
            </ul>
            <div class="buttons">
                <!-- Enrollment Form -->
                <form method="post" action="">
                    <input type="hidden" name="enroll_student" value="1">
                    <button type="submit" class="button buy-now">
                        <ion-icon name="bag-handle-outline"></ion-icon> S'INSCRIRE
                    </button>
                </form>
            </div>
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
<?php
        return ob_get_clean();
    } else {
        return '<p>Course not found.</p>';
    }
}
add_shortcode('course_details', 'render_course_details_section');

// Enrollment Function
function enroll_student_in_course($course_id, $student_id) {
    global $wpdb;

    // Get course details
    $course = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}courses WHERE id = %d", $course_id)
    );

    if (!$course) {
        return new WP_Error('course_not_found', 'Course not found.');
    }

    // Check if the student is already enrolled in this course
    $is_enrolled = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d", $student_id, $course_id)
    );

    if ($is_enrolled) {
        return new WP_Error('already_enrolled', 'You are already enrolled in this course.');
    }

    // Check if the student has sufficient credits
    $student_credit = $wpdb->get_var(
        $wpdb->prepare("SELECT credit FROM {$wpdb->prefix}students WHERE id = %d", $student_id)
    );

    if ($student_credit < $course->required_credit) {
        return new WP_Error('insufficient_credit', 'Insufficient credit to enroll in this course.');
    }

    // Deduct credits from the student
    $wpdb->query(
        $wpdb->prepare("UPDATE {$wpdb->prefix}students SET credit = credit - %f WHERE id = %d", $course->required_credit, $student_id)
    );

    // Get all teachers assigned to this course along with their groups
    $teachers = $wpdb->get_results(
        $wpdb->prepare("SELECT teacher_id, group_number FROM {$wpdb->prefix}teacher_courses WHERE course_id = %d", $course_id)
    );

    if (empty($teachers)) {
        return new WP_Error('no_teachers_assigned', 'No teachers are assigned to this course.');
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
    }

    if (!$assigned) {
        // If no group has space, assign to the first teacher's group
        $first_teacher = $teachers[0];
        $wpdb->insert(
            "{$wpdb->prefix}student_courses",
            array(
                'student_id'   => $student_id,
                'course_id'    => $course_id,
                'teacher_id'   => $first_teacher->teacher_id,
                'group_number' => $first_teacher->group_number
            ),
            array('%d', '%d', '%d', '%d')
        );
    }

    return true;
}
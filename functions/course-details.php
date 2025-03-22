<?php
// Course Details Page

// shortcode [course_details]

function render_course_details_section() {
    
    global $wpdb;

    // Get the current user
    $user = wp_get_current_user();

    $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($course_id <= 0) {
        return '<p>Cours non trouvé.</p>';
    }

    $course = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}courses WHERE id = %d",
            $course_id
        )
    );

    // Decode session days from JSON
    $session_dates = json_decode($course->session_days, true);

    // Get the current date
    $current_day = new DateTime();
    $current_day_str = $current_day->format('Y-m-d');

    // Find the next available session date
    $next_available_date = null;
    foreach ($session_dates as $session_date) {
        if ($session_date > $current_day_str) {
            $next_available_date = $session_date;
            break; // Get only the immediate next session
        }
    }

    // Check if a next session is found
    if (!$next_available_date) {
        $error_message = "Aucune session à venir disponible";
        echo '<div class="course-error-message">' . esc_html($error_message) . '</div>';
    }

    // Get the day name
    $next_date_obj = new DateTime($next_available_date);
    $day_name = $next_date_obj->format('l');

    // Fetch session time slots from course_sessions table using WPDB
    $session_times = $wpdb->get_row(
        $wpdb->prepare("
            SELECT slot1_start_time, slot1_end_time, slot2_start_time, slot2_end_time 
            FROM {$wpdb->prefix}course_sessions 
            WHERE course_id = %d AND session_date = %s",
            $course_id, $next_available_date
        ),
        ARRAY_A
    );

    // Check if the course exists
    if ($course) {
        // Extract the start date and time slot
        $start_date = strtotime($course->start_date); // Convert start date to a Unix timestamp
        $start_date = strtotime($course->start_date); // Convert start date to a Unix timestamp
        $year = date('Y', $start_date); // Get the year
        $month = date('m', $start_date); // Get the month (numeric)
        $day = date('d', $start_date); // Get the day
        $time_slot = null; // The time slot

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
                $result = enroll_in_course($course_id, $student_id);
    
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
                $result = enroll_in_course($course_id, $student_id, $parent_id);
    
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
                        <?php echo esc_html($course->required_credit); ?> Crédit
                    </span>
                </li>
                <?php
                    if ($next_available_date) {
                ?>
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-calendar-day"></i> Date du cours:
                    </span>
                    <span class="item-value">
                        <?php
                            $formatter = new IntlDateFormatter(
                                'fr_FR', // French locale
                                IntlDateFormatter::LONG,
                                IntlDateFormatter::NONE
                            );
                            echo $formatter->format(strtotime($next_available_date));
                        ?>
                    </span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-calendar-week"></i> Jour:
                    </span>
                    <span class="item-value">
                        <?php
                            $date = new DateTime($next_available_date);
                            $formatter = new IntlDateFormatter(
                                'fr_FR', // French locale
                                IntlDateFormatter::FULL,
                                IntlDateFormatter::NONE,
                                null,
                                null,
                                'EEEE' // Day name
                            );
                            echo ucfirst($formatter->format($date)); // ucfirst to capitalize first letter
                        ?>
                    </span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-hourglass-start"></i> Temps 1:
                    </span>
                    <span class="item-value">
                        <?php  
        echo esc_html(date("g:i A", strtotime($session_times['slot1_start_time']))) . " - " . 
             esc_html(date("g:i A", strtotime($session_times['slot1_end_time'])));
        ?>
                    </span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <i class="fas fa-hourglass-end"></i> Temps 2:
                    </span>
                    <span class="item-value">
                        <?php 
        echo esc_html(date("g:i A", strtotime($session_times['slot2_start_time']))) . " - " . 
             esc_html(date("g:i A", strtotime($session_times['slot2_end_time'])));
        ?>
                    </span>
                </li>
                <?php
                    }
                ?>
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
                        <i class="fas fa-chevron-down custom-arrow"></i>
                    </div>
                    <?php else : ?>
                    <label for="student_id">Choisissez un enfant:</label>
                    <div class="custom-select-wrapper">
                        <select name="student_id" id="student_id">
                            <option value="" selected disabled>Aucun enfant trouvé</option>
                        </select>
                        <i class="fas fa-chevron-down custom-arrow"></i>
                    </div>
                    <?php endif; ?>
                    <button type=" submit" class="button buy-now">
                        <i class="fas fa-shopping-bag"></i> Inscrire un enfant
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
                        <i class="fas fa-shopping-bag"></i> Inscrire
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
                        <i class="fab fa-facebook"></i>
                    </a>

                    <!-- Instagram Share -->
                    <a href="https://www.instagram.com/" target="_blank" class="social-link" title="Share on Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>

                    <!-- LinkedIn Share -->
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>&summary=<?php echo urlencode(get_the_excerpt()); ?>"
                        target="_blank" class="social-link" title="Share on LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
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


// Function to enroll students in the course
function enroll_in_course($course_id, $student_id, $parent_id = null) {
    global $wpdb;

    // Get course details
    $course = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}courses WHERE id = %d", $course_id)
    );

    if (!$course) {
        return new WP_Error('course_not_found', 'Cours non trouvé.');
    }

    // Get all teachers assigned to this course
    $assigned_teachers_array = $wpdb->get_var(
        $wpdb->prepare("SELECT assigned_teachers FROM {$wpdb->prefix}courses WHERE id = %d", $course_id)
    );

    if (empty($assigned_teachers_array)) {
        return new WP_Error('no_teachers_assigned', "Aucun enseignant n'est affecté à ce cours.");
    }

    // Check if the student is already enrolled in this course
    $enrolled_students = $wpdb->get_var(
        $wpdb->prepare("SELECT enrolled_students FROM {$wpdb->prefix}courses WHERE id = %d", $course_id)
    );
    $enrolled_students_array = json_decode($enrolled_students, true);

    if (in_array($student_id, $enrolled_students_array)) {
        return new WP_Error('already_enrolled', 'Vous êtes déjà inscrit à ce cours.');
    }

    // Determine if the enrollment is for a student or a parent's child
    if ($parent_id) {
        // Enrollment for a parent's child
        $user_table = $wpdb->prefix . 'parents';
        $user_id = $parent_id;
    } else {
        // Enrollment for a student
        $user_table = $wpdb->prefix . 'students';
        $user_id = $student_id;
    }

    // Check if the user (student or parent) has sufficient credits
    $user_credit = $wpdb->get_var(
        $wpdb->prepare("SELECT credit FROM {$user_table} WHERE id = %d", $user_id)
    );

    if ($user_credit < $course->required_credit) {
        return new WP_Error('insufficient_credit', "Crédit insuffisant pour s'inscrire à ce cours.");
    }

    // Deduct credits from the user (student or parent)
    $wpdb->query(
        $wpdb->prepare("UPDATE {$user_table} SET credit = credit - %f WHERE id = %d", $course->required_credit, $user_id)
    );

    // Insert data into the credits table
    $credits_table = $wpdb->prefix . 'credits';
    $wpdb->insert(
        $credits_table,
        [
            'user_id' => $user_id,
            'credit' => $course->required_credit,
            'transaction_type' => 'Débité',
            'transaction_reason' => 'Cours acheté',
            'created_at' => current_time('mysql'),
        ],
        [
            '%d', '%f', '%s', '%s', '%s'
        ]
    );

    // Enroll student to the next available group
    $enrolled = false;
    $course_sessions_table = $wpdb->prefix . 'course_sessions';
    $course_slots_table = $wpdb->prefix . 'course_slots'; // Table for course slots
    $assigned_teachers = json_decode($assigned_teachers_array, true);
    $max_students_per_group = $course->max_students_per_group;

    // Get the current date
    $current_day = new DateTime();
    $current_day_str = $current_day->format('Y-m-d');

    // Calculate session days
    $start_date_obj = new DateTime($course->start_date);
    $end_date_obj = new DateTime($course->end_date);
    $recurring_days = json_decode($course->days, true);
    $session_dates = [];

    while ($start_date_obj <= $end_date_obj) {
        $current_day_name = $start_date_obj->format('l'); // Get day name (e.g., 'Tuesday')
        if (in_array($current_day_name, $recurring_days)) {
            $session_dates[] = $start_date_obj->format('Y-m-d'); // Store session date
        }
        $start_date_obj->modify('+1 day');
    }

    // Iterate through session days
    foreach ($session_dates as $session_date) {
        // Skip past or current session days
        if ($session_date <= $current_day_str) {
            continue;
        }

        // Get the day of the session date (e.g., 'Tuesday')
        $session_day = (new DateTime($session_date))->format('l');

        // Fetch slot times from course_slots table
        $course_slot = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$course_slots_table} WHERE course_id = %d AND session_day = %s", $course_id, $session_day)
        );

        if (!$course_slot) {
            continue; // Skip if no slot is found for this session day
        }

        // Fetch existing groups for this session day
        $existing_groups = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$course_sessions_table} WHERE course_id = %d AND session_date = %s ORDER BY group_number", $course_id, $session_date)
        );

        // Track assigned teachers for this session day
        $assigned_teachers_for_day = [];
        foreach ($existing_groups as $group) {
            $assigned_teachers_for_day[] = $group->teacher_id;
        }

        // Check if the student can be added to an existing group
        foreach ($existing_groups as $group) {
            $group_students = json_decode($group->enrolled_students, true);
            if (count($group_students) < $max_students_per_group) {
                $group_students[] = $student_id;
                $wpdb->update(
                    $course_sessions_table,
                    ['enrolled_students' => json_encode($group_students)],
                    ['id' => $group->id],
                    ['%s'],
                    ['%d']
                );
                $enrolled = true;
                break 2; // Exit both loops
            }
        }

        // If not enrolled yet, create a new group
        if (!$enrolled) {
            // Shuffle the list of assigned teachers to randomize the order
            shuffle($assigned_teachers);

            // Find the next available teacher who is not already assigned to a group for this session day
            $available_teacher = null;
            foreach ($assigned_teachers as $teacher_id) {
                if (!in_array($teacher_id, $assigned_teachers_for_day)) {
                    $available_teacher = $teacher_id;
                    break;
                }
            }

            if (!$available_teacher) {
                continue; // Skip to the next session day
            }

            // Determine the next group number
            $next_group_number = 1;
            if (!empty($existing_groups)) {
                $next_group_number = count($existing_groups) + 1;
            }

            // Assign the available teacher to the new group
            $wpdb->insert(
                $course_sessions_table,
                [
                    'course_id'   => $course_id,
                    'session_date' => $session_date,
                    'teacher_id'  => $available_teacher,
                    'group_number' => $next_group_number,
                    'enrolled_students' => json_encode([$student_id]),
                    'slot1_start_time' => $course_slot->slot1_start_time,
                    'slot1_end_time'   => $course_slot->slot1_end_time,
                    'slot2_start_time' => $course_slot->slot2_start_time,
                    'slot2_end_time'   => $course_slot->slot2_end_time,
                    'class_link'       => null,
                    'status'           => 'upcoming',
                    'cancelled_reason' => null
                ],
                [
                    '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
                ]
            );

            $session_id = $wpdb->insert_id; // Get the ID of the newly inserted session

            // Insert payment for the teacher
            $teacher_table = $wpdb->prefix . 'teachers';
            $payments_table = $wpdb->prefix . 'teacher_payments';

            // Fetch teacher data
            $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $available_teacher));

            // Validate teacher payment details
            if (!$teacher || !isset($teacher->due) || !isset($teacher->country)) {
                return new WP_Error('teacher_payment_error', "Les informations de paiement de l'enseignant sont manquantes ou invalides.");
            }

            // Generate a unique invoice number
            do {
                $invoice_number = 'JMI-' . uniqid() . '-' . bin2hex(random_bytes(4));
                $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $payments_table WHERE invoice_number = %s", $invoice_number));
            } while ($exists > 0);

            // Set payment details
            $currency = 'EUR';
            $payment_method = 'Bank';
            $status = 'in progress';
            $deposit = 0;
            $old_due = floatval($teacher->due); // Get past due amount
            $due = ($teacher->country === 'France') ? 26 : 13; // Set due amount based on teacher's country
            $new_due = $old_due + $due;

            // Insert payment into the database
            $inserted_payment = $wpdb->insert(
                $payments_table,
                [
                    'invoice_number'        => $invoice_number,
                    'teacher_id'           => $available_teacher,
                    'session_id'           => $session_id,
                    'due'                  => $due,
                    'deposit'             => $deposit,
                    'currency'            => $currency,
                    'payment_method'      => $payment_method,
                    'status'              => $status,
                ],
                [
                    '%s', '%d', '%d', '%f', '%f', '%s', '%s', '%s'
                ]
            );

            if (!$inserted_payment) {
                return new WP_Error('payment_failed', "Échec de l'enregistrement du paiement pour l'enseignant.");
            }

            // Update the 'due' column in the teachers table
            $updated_due = $wpdb->update(
                $teacher_table, // Table name
                [ 'due' => $new_due ], // Data to update
                [ 'id' => $available_teacher ], // Condition (where clause)
                [ '%f' ], // Format for the updated value
                [ '%d' ]  // Format for the where condition (teacher ID)
            );

            $enrolled = true;
            break; // Exit the loop
        }
    }

    if (!$enrolled) {
        return new WP_Error('no_available_groups', "Tous les groupes sont complets. Impossible d'inscrire l'élève.");
    }

    // Add the student to the course's enrolled_students list
    $enrolled_students_array[] = $student_id;
    $wpdb->update(
        $wpdb->prefix . 'courses',
        ['enrolled_students' => json_encode($enrolled_students_array)],
        ['id' => $course_id],
        ['%s'],
        ['%d']
    );

    return true;
}
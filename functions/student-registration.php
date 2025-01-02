<?php

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



// Student's Database Table

function create_students_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students'; // Table name with prefix
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender ENUM('Male', 'Female', 'Other') NOT NULL,
        school VARCHAR(255) NOT NULL,
        grade_level VARCHAR(50) NOT NULL,
        math_level VARCHAR(50) NOT NULL,
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
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_students_table');




// Student Registration Form

// shortcode [student_registration_form]

function custom_student_registration_form() {
    ob_start(); // Start output buffering

    // Check for form errors or success messages
    $error_message = isset($_SESSION['registration_error']) ? $_SESSION['registration_error'] : '';
    $success_message = isset($_SESSION['registration_success']) ? $_SESSION['registration_success'] : '';

    // Clear session messages after displaying
    unset($_SESSION['registration_error'], $_SESSION['registration_success']);

    $csrf_token = wp_create_nonce('student_registration_form');
    ?>
<form class="student-registration-form" method="post" action="">

    <input type="hidden" name="csrf_token" value="<?php echo esc_attr($csrf_token); ?>">

    <!-- Display error message -->
    <?php if ($error_message): ?>
    <div class="form-error">
        <p><?php echo esc_html($error_message); ?></p>
    </div>
    <?php endif; ?>

    <!-- Display success message -->
    <?php if ($success_message): ?>
    <div class="form-success">
        <p><?php echo esc_html($success_message); ?></p>
    </div>
    <?php endif; ?>

    <!-- Personal Information -->
    <section class="section col personal-information">
        <h3 class="section-heading">Informations pour les étudiants</h3>

        <div class="row">
            <div class="col">
                <label for="first_name">Prénom <span class="required">*</span></label>
                <input type="text" id="first_name" name="first_name" placeholder="Votre prénom" required>
            </div>
            <div class="col">
                <label for="last_name">Nom de famille <span class="required">*</span></label>
                <input type="text" id="last_name" name="last_name" placeholder="Votre nom de famille" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="date_of_birth">Date de naissance <span class="required">*</span></label>
                <input type="date" id="date_of_birth" name="date_of_birth" required>
            </div>
            <div class="col">
                <label for="gender">Genre <span class="required">*</span></label>
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>Sélectionnez le sexe</option>
                    <option value="Male">Mâle</option>
                    <option value="Female">Femelle</option>
                    <option value="Other">Autre</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Academic Details -->
    <section class="section col academic-details">
        <h3 class="section-heading">Informations académiques</h3>

        <div class="row">
            <div class="col">
                <label for="school">École actuelle <span class="required">*</span></label>
                <input type="text" id="school" name="school" placeholder="Le nom de votre école" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="grade_level">Niveau scolaire actuel <span class="required">*</span></label>
                <select id="grade_level" name="grade_level" required>
                    <option value="" disabled selected>Sélectionnez votre niveau scolaire</option>
                    <option value="Third">Troisième</option>
                    <option value="Fourth">Quatrième</option>
                    <option value="Fifth">Cinquième</option>
                    <option value="Sixth">Sixième</option>
                    <!-- <option value="Seventh">Seventh</option> -->
                </select>
            </div>

            <div class="col">
                <label for="math_level">Niveau en Mathématiques <span class="required">*</span></label>
                <select id="math_level" name="math_level" required>
                    <option value="" disabled selected>Sélectionnez votre niveau en mathématiques</option>
                    <option value="Beginner">Débutant</option>
                    <option value="Intermediate">Intermédiaire</option>
                    <option value="Advanced">Avancé</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Modules of Interest -->
    <section class="section col interested-modules">
        <h3 class="section-heading">Modules d'intérêt</h3>

        <div id="subject_of_interest" class="checkbox-group">
            <label class="row"><input type="checkbox" name="subject_of_interest[]" value="Algebra"> Algèbre</label>
            <label class="row"><input type="checkbox" name="subject_of_interest[]" value="Geometry"> Géométrie</label>
            <label class="row"><input type="checkbox" name="subject_of_interest[]" value="Trigonometry">
                Trigonométrie</label>
            <label class="row"><input type="checkbox" name="subject_of_interest[]" value="Calculus"> Calcul</label>
            <label class="row"><input type="checkbox" name="subject_of_interest[]" value="Statistics">
                Statistiques</label>
        </div>
    </section>

    <!-- Weekly Availability -->
    <section class="section col weekly-availability">
        <h3 class="section-heading">Disponibilité hebdomadaire (par créneau de 2 heures)</h3>

        <div class="row">
            <div class="col days">
                <!-- Days of the Week (Checkboxes) -->
                <label for="available_days">Jours</label>
                <div class="col checkbox-group">
                    <label class="row"><input type="checkbox" name="available_days[]" value="Monday">
                        Lundi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Tuesday">
                        Mardi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Wednesday">
                        Mercredi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Thursday">
                        Jeudi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Friday">
                        Vendredi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Saturday">
                        Samedi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Sunday">
                        Dimanche</label>
                </div>
            </div>

            <div class="col time-slots">
                <label for="">Créneaux horaires</label>

                <!-- Monday Time Slots -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="monday_timeslot" value="8am-10am"> 8am-10am</label>
                    <label class="row"><input type="radio" name="monday_timeslot" value="10am-12pm"> 10am-12pm</label>
                    <label class="row"><input type="radio" name="monday_timeslot" value="12pm-2pm"> 12pm-2pm</label>
                    <label class="row"><input type="radio" name="monday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="monday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Tuesday Time Slots -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="tuesday_timeslot" value="8am-10am"> 8am-10am</label>
                    <label class="row"><input type="radio" name="tuesday_timeslot" value="10am-12pm">
                        10am-12pm</label>
                    <label class="row"><input type="radio" name="tuesday_timeslot" value="12pm-2pm"> 12pm-2pm</label>
                    <label class="row"><input type="radio" name="tuesday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="tuesday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Wednesday Time Slots -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="8am-10am">
                        8am-10am</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="10am-12pm">
                        10am-12pm</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="12pm-2pm">
                        12pm-2pm</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Thursday Time Slots -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="thursday_timeslot" value="8am-10am"> 8am-10am</label>
                    <label class="row"><input type="radio" name="thursday_timeslot" value="10am-12pm">
                        10am-12pm</label>
                    <label class="row"><input type="radio" name="thursday_timeslot" value="12pm-2pm"> 12pm-2pm</label>
                    <label class="row"><input type="radio" name="thursday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="thursday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Friday Time Slots -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="friday_timeslot" value="8am-10am"> 8am-10am</label>
                    <label class="row"><input type="radio" name="friday_timeslot" value="10am-12pm"> 10am-12pm</label>
                    <label class="row"><input type="radio" name="friday_timeslot" value="12pm-2pm"> 12pm-2pm</label>
                    <label class="row"><input type="radio" name="friday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="friday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Saturday Time Slots -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="saturday_timeslot" value="8am-10am"> 8am-10am</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="10am-12pm">
                        10am-12pm</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="12pm-2pm"> 12pm-2pm</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Sunday Time Slots -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="sunday_timeslot" value="8am-10am"> 8am-10am</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="10am-12pm"> 10am-12pm</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="12pm-2pm"> 12pm-2pm</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>
            </div>
        </div>
    </section>


    <!-- Account Details -->
    <section class="section col account-details">
        <h3 class="section-heading">Informations sur le compte</h3>

        <div class="row">
            <div class="col">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" placeholder="Votre nom d'utilisateur">
            </div>
            <div class="col">
                <label for="email">E-mail <span class="required">*</span></label>
                <input type="email" id="email" name="email" placeholder="Votre email" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="password">Mot de passe <span class="required">*</span></label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
            </div>
            <div class="col">
                <label for="confirm_password">Confirmez le mot de passe <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password"
                    placeholder="Confirmez votre mot de passe" required>
            </div>
        </div>
    </section>

    <!-- Parent/Guardian Consent -->
    <div class="col guardian-consent">
        <h3 class="section-heading">Consentement des parents/tuteurs</h3>
        <input type="text" name="parent_consent" id="parent_consent" class="declaration-input signature"
            placeholder="Consentement" required>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="submit-button" name="submit_student_registration">Registre</button>
</form>
<?php
    return ob_get_clean(); // Return the form's HTML
}
add_shortcode('student_registration_form', 'custom_student_registration_form');







// Student Registration Backend

// Handle student registration form submission
function handle_student_registration_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student_registration'])) {
        global $wpdb;

        // Sanitize and validate form inputs
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
        $gender = sanitize_text_field($_POST['gender']);
        $school = sanitize_text_field($_POST['school']);
        $grade_level = sanitize_text_field($_POST['grade_level']);
        $math_level = sanitize_text_field($_POST['math_level']);
        $subject_of_interest = !empty($_POST['subject_of_interest']) ? implode(',', array_map('sanitize_text_field', $_POST['subject_of_interest'])) : '';
        $available_days = !empty($_POST['available_days']) ? implode(',', array_map('sanitize_text_field', $_POST['available_days'])) : '';
        $monday_timeslot = isset($_POST['monday_timeslot']) ? sanitize_text_field($_POST['monday_timeslot']) : null;
        $tuesday_timeslot = isset($_POST['tuesday_timeslot']) ? sanitize_text_field($_POST['tuesday_timeslot']) : null;
        $wednesday_timeslot = isset($_POST['wednesday_timeslot']) ? sanitize_text_field($_POST['wednesday_timeslot']) : null;
        $thursday_timeslot = isset($_POST['thursday_timeslot']) ? sanitize_text_field($_POST['thursday_timeslot']) : null;
        $friday_timeslot = isset($_POST['friday_timeslot']) ? sanitize_text_field($_POST['friday_timeslot']) : null;
        $saturday_timeslot = isset($_POST['saturday_timeslot']) ? sanitize_text_field($_POST['saturday_timeslot']) : null;
        $sunday_timeslot = isset($_POST['sunday_timeslot']) ? sanitize_text_field($_POST['sunday_timeslot']) : null;        
        $parent_consent = sanitize_text_field($_POST['parent_consent']);
        $email = sanitize_email($_POST['email']);
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check required fields
        if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) || empty($school) ||
            empty($grade_level) || empty($math_level) || empty($email) || empty($parent_consent) || empty($password)) {
            $_SESSION['registration_error'] = 'All required fields must be filled.';
            return;
        }

        // Generate a username if empty
        if (empty($username)) {
            $base_username = sanitize_user(strtolower($first_name . '_' . $last_name));
            $username = $base_username;
            $suffix = 1;

            while (username_exists($username)) {
                $username = $base_username . '_' . $suffix;
                $suffix++;
            }
        }

        // Check password confirmation
        if ($password !== $confirm_password) {
            $_SESSION['registration_error'] = 'Passwords do not match.';
            return;
        }

        // Ensure email is unique
        if (email_exists($email)) {
            $_SESSION['registration_error'] = 'The email is already registered.';
            return;
        }

        // Create the user in WordPress
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            $_SESSION['registration_error'] = $user_id->get_error_message();
            return;
        }

        // Assign 'student' role to the user
        $user = get_user_by('ID', $user_id);
        $user->set_role('student');

        // Verify if the user has the 'student' role
        if (in_array('student', $user->roles)) {
            // Add first and last name to WordPress user meta
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);

            // Save data to the custom 'students' table
            $table_name = $wpdb->prefix . 'students';
            $wpdb->insert($table_name, [
                'id' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'school' => $school,
                'grade_level' => $grade_level,
                'math_level' => $math_level,
                'subject_of_interest' => $subject_of_interest,
                'available_days' => $available_days,
                'monday_timeslot' => $monday_timeslot,
                'tuesday_timeslot' => $tuesday_timeslot,
                'wednesday_timeslot' => $wednesday_timeslot,
                'thursday_timeslot' => $thursday_timeslot,
                'friday_timeslot' => $friday_timeslot,
                'saturday_timeslot' => $saturday_timeslot,
                'sunday_timeslot' => $sunday_timeslot,
                'parent_consent' => $parent_consent,
                'created_at' => current_time('mysql'),
            ]);

            // Set success message
            $_SESSION['registration_success'] = 'Registration successful. Welcome!';
        } else {
            // If the role is not 'student', display an error (should not happen in this context)
            $_SESSION['registration_error'] = 'Failed to assign the student role. Registration aborted.';
        }
    }
}
add_action('init', 'handle_student_registration_form');
<?php

/* Template Name: Parent | Add Child */

// page title
global $pageTitle;
$pageTitle = 'Ajouter un enfant';

require_once(get_template_directory() . '/parent/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$parent_id = get_current_user_id(); // Get currently logged-in parent ID

// Handle child registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_child_registration'])) {
    global $wpdb;
    $parent_id = get_current_user_id(); // Get currently logged-in parent ID
    
    // Sanitize and validate form inputs
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
    $gender = sanitize_text_field($_POST['gender']);
    $school = sanitize_text_field($_POST['school']);
    $grade = sanitize_text_field($_POST['grade']);
    $level = sanitize_text_field($_POST['level']);
    $subject_of_interest = !empty($_POST['subject_of_interest']) ? implode(',', array_map('sanitize_text_field', $_POST['subject_of_interest'])) : '';
    $available_days = !empty($_POST['available_days']) ? implode(',', array_map('sanitize_text_field', $_POST['available_days'])) : '';
    $monday_timeslot = !empty($_POST['monday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['monday_timeslot'])) : '';
    $tuesday_timeslot = !empty($_POST['tuesday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['tuesday_timeslot'])) : '';
    $wednesday_timeslot = !empty($_POST['wednesday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['wednesday_timeslot'])) : '';
    $thursday_timeslot = !empty($_POST['thursday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['thursday_timeslot'])) : '';
    $friday_timeslot = !empty($_POST['friday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['friday_timeslot'])) : '';
    $saturday_timeslot = !empty($_POST['saturday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['saturday_timeslot'])) : '';
    $sunday_timeslot = !empty($_POST['sunday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['sunday_timeslot'])) : '';
    $parent_consent = sanitize_text_field($_POST['parent_consent']);
    $email = sanitize_email($_POST['email']);
    $username = sanitize_user($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check required fields
    if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender) || empty($school) ||
        empty($grade) || empty($level) || empty($email) || empty($parent_consent) || empty($password)) {
        $_SESSION['registration_error'] = 'Tous les champs obligatoires doivent être remplis.';
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
        $_SESSION['registration_error'] = 'Les mots de passe ne correspondent pas.';
        return;
    }

    // Ensure email is unique
    if (email_exists($email)) {
        $_SESSION['registration_error'] = "L'email est déjà enregistré.";
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
            'parent_id' => $parent_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'school' => $school,
            'grade' => $grade,
            'level' => $level,
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
        $_SESSION['registration_success'] = 'Inscription réussie. Bienvenue sur Joomaths !';

        // Send email to the student
        $to = $email;
        $subject = 'Bienvenue sur Joomaths !';
        $message = "Bonjour $first_name,\n\nMerci de votre inscription. Votre compte a bien été créé.\n\n";
        $message .= "Nom d'utilisateur: $username\n";
        $message .= "Mot de passe: (le mot de passe que vous avez saisi lors de l'inscription)\n\n";
        $message .= "Nous avons hâte de vous voir sur notre plateforme !\n\nCordialement,\nL'équipe Joomaths";

        // Send the email
        wp_mail($to, $subject, $message);

        // Redirect to the child management page to prevent form resubmission
        wp_safe_redirect(home_url('/parent/child-management'));
        exit;
    } else {
        // If the role is not 'student', display an error (should not happen in this context)
        $_SESSION['registration_error'] = "Échec de l'attribution du rôle d'étudiant. Inscription interrompue.";
    }
}

// Check for form errors or success messages
$error_message = isset($_SESSION['registration_error']) ? $_SESSION['registration_error'] : '';
$success_message = isset($_SESSION['registration_success']) ? $_SESSION['registration_success'] : '';

// Clear session messages after displaying
unset($_SESSION['registration_error'], $_SESSION['registration_success']);

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="parentAddChild" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Ajouter un enfant</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/parent/child-management'); ?>" class="breadcrumb-link">Gestion Des
                    Enfants</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Ajouter un enfant</span>
            </div>
        </div>

        <div class="content-section">
            <form class="student-registration-form" method="post" action="">

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
                    <h3 class="section-heading">Informations générales</h3>

                    <div class="row">
                        <div class="col">
                            <label for="first_name">Prénom <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name"
                                placeholder="Ton prénom, c’est plus sympa pour te connaître !" required>
                        </div>
                        <div class="col">
                            <label for="last_name">Nom de famille <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name"
                                placeholder="Eh oui, il nous faut ton nom aussi 😄" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="date_of_birth">Date de naissance <span class="required">*</span></label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required>
                        </div>
                        <div class="col">
                            <label for="gender">Sexe</label>
                            <div class="custom-select-wrapper">
                                <select id="gender" name="gender">
                                    <option value="" disabled selected>Sélectionnez le sexe</option>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                                <i class="fas fa-chevron-down custom-arrow"></i>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Academic Details -->
                <section class="section col academic-details">
                    <h3 class="section-heading">Parles-nous de ta scolarité</h3>

                    <div class="row">
                        <div class="col">
                            <label for="school">École actuelle <span class="required">*</span></label>
                            <input type="text" id="school" name="school"
                                placeholder="Le nom de ton école – même si c’est l’école de la vie 😎" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="grade">Classe actuelle <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="grade" name="grade" required>
                                    <option value="" disabled selected>Dis-nous où tu en es dans l’aventure scolaire.
                                    </option>

                                    <?php
                        global $wpdb; // Access the global $wpdb object for database queries

                        // Query the custom 'course_grades' table
                        $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");
            
                        // Check if grades are available
                        if ($grades) {
                            foreach ($grades as $grade) {
                                echo '<option value="' . esc_attr($grade->grade) . '">' . esc_html($grade->grade) . '</option>';
                            }
                        } else {
                            echo '<option disabled>No grade found</option>';
                        }
                    ?>

                                </select>
                                <i class="fas fa-chevron-down custom-arrow"></i>
                            </div>
                        </div>

                        <div class="col">
                            <label for="level">Ta moyenne en maths <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <!-- Hidden select element to store the actual level (Fort/Débutant) -->
                                <select id="level" name="level" required style="display: none;">
                                    <option value="" disabled selected>Select a level</option>
                                    <option value="Fort">Fort</option>
                                    <option value="Débutant">Débutant</option>
                                </select>

                                <!-- Visible select element for user input (1-20) -->
                                <select id="level-input" name="level-input" required>
                                    <option value="" disabled selected>On t’accueille quel que soit ton niveau – de
                                        débutant à fort
                                        💡</option>
                                    <?php
        for ($i = 1; $i <= 20; $i++) {
            echo '<option value="' . $i . '">' . $i . '</option>';
        }
        ?>
                                </select>
                                <i class="fas fa-chevron-down custom-arrow"></i>
                            </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const levelInput = document.getElementById('level-input');
                            const levelSelect = document.getElementById('level');

                            levelInput.addEventListener('change', function() {
                                const selectedValue = parseInt(levelInput.value, 10);

                                // Determine the level based on the selected value
                                if (selectedValue < 12) {
                                    levelSelect.value = 'Débutant'; // Weak (Débutant)
                                } else {
                                    levelSelect.value = 'Fort'; // Strong (Fort)
                                }
                            });

                            // Ensure the form submits the correct level value
                            document.querySelector('form').addEventListener('submit', function(e) {
                                if (!levelSelect.value) {
                                    e
                                        .preventDefault(); // Prevent form submission if no level is selected
                                    alert('Please select a valid level.');
                                }
                            });
                        });
                        </script>

                    </div>
                </section>

                <!-- Modules of Interest -->
                <section class="section col interested-modules">
                    <h3 class="section-heading">Choisis ce qui t’intéresse</h3>

                    <div id="subject_of_interest" class="checkbox-group">
                        <?php
            global $wpdb;
            $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");
            if ($categories) {
                foreach ($categories as $category) {
                    echo '<label class="row"><input type="checkbox" name="subject_of_interest[]" value="' . esc_attr($category->category) . '">' . esc_html($category->category) . '</label>';
                }
            } else {
                echo '<option disabled>No category found</option>';
            }
        ?>
                    </div>
                </section>

                <!-- Weekly Availability -->
                <section class="section col weekly-availability">
                    <h3 class="section-heading">Quand es-tu dispo ?</h3>

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
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="monday_timeslot[]" value="8am-10am">
                                    8am-10am</label>
                                <label class="row"><input type="checkbox" name="monday_timeslot[]" value="10am-12pm">
                                    10am-12pm</label>
                                <label class="row"><input type="checkbox" name="monday_timeslot[]" value="12pm-2pm">
                                    12pm-2pm</label>
                                <label class="row"><input type="checkbox" name="monday_timeslot[]" value="2pm-4pm">
                                    2pm-4pm</label>
                                <label class="row"><input type="checkbox" name="monday_timeslot[]" value="4pm-6pm">
                                    4pm-6pm</label>
                            </div>

                            <!-- Tuesday Time Slots -->
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="tuesday_timeslot[]" value="8am-10am">
                                    8am-10am</label>
                                <label class="row"><input type="checkbox" name="tuesday_timeslot[]" value="10am-12pm">
                                    10am-12pm</label>
                                <label class="row"><input type="checkbox" name="tuesday_timeslot[]" value="12pm-2pm">
                                    12pm-2pm</label>
                                <label class="row"><input type="checkbox" name="tuesday_timeslot[]" value="2pm-4pm">
                                    2pm-4pm</label>
                                <label class="row"><input type="checkbox" name="tuesday_timeslot[]" value="4pm-6pm">
                                    4pm-6pm</label>
                            </div>

                            <!-- Wednesday Time Slots -->
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="wednesday_timeslot[]" value="8am-10am">
                                    8am-10am</label>
                                <label class="row"><input type="checkbox" name="wednesday_timeslot[]" value="10am-12pm">
                                    10am-12pm</label>
                                <label class="row"><input type="checkbox" name="wednesday_timeslot[]" value="12pm-2pm">
                                    12pm-2pm</label>
                                <label class="row"><input type="checkbox" name="wednesday_timeslot[]" value="2pm-4pm">
                                    2pm-4pm</label>
                                <label class="row"><input type="checkbox" name="wednesday_timeslot[]" value="4pm-6pm">
                                    4pm-6pm</label>
                            </div>

                            <!-- Thursday Time Slots -->
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="thursday_timeslot[]" value="8am-10am">
                                    8am-10am</label>
                                <label class="row"><input type="checkbox" name="thursday_timeslot[]" value="10am-12pm">
                                    10am-12pm</label>
                                <label class="row"><input type="checkbox" name="thursday_timeslot[]" value="12pm-2pm">
                                    12pm-2pm</label>
                                <label class="row"><input type="checkbox" name="thursday_timeslot[]" value="2pm-4pm">
                                    2pm-4pm</label>
                                <label class="row"><input type="checkbox" name="thursday_timeslot[]" value="4pm-6pm">
                                    4pm-6pm</label>
                            </div>

                            <!-- Friday Time Slots -->
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="friday_timeslot[]" value="8am-10am">
                                    8am-10am</label>
                                <label class="row"><input type="checkbox" name="friday_timeslot[]" value="10am-12pm">
                                    10am-12pm</label>
                                <label class="row"><input type="checkbox" name="friday_timeslot[]" value="12pm-2pm">
                                    12pm-2pm</label>
                                <label class="row"><input type="checkbox" name="friday_timeslot[]" value="2pm-4pm">
                                    2pm-4pm</label>
                                <label class="row"><input type="checkbox" name="friday_timeslot[]" value="4pm-6pm">
                                    4pm-6pm</label>
                            </div>

                            <!-- Saturday Time Slots -->
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="8am-10am">
                                    8am-10am</label>
                                <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="10am-12pm">
                                    10am-12pm</label>
                                <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="12pm-2pm">
                                    12pm-2pm</label>
                                <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="2pm-4pm">
                                    2pm-4pm</label>
                                <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="4pm-6pm">
                                    4pm-6pm</label>
                            </div>

                            <!-- Sunday Time Slots -->
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="8am-10am">
                                    8am-10am</label>
                                <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="10am-12pm">
                                    10am-12pm</label>
                                <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="12pm-2pm">
                                    12pm-2pm</label>
                                <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="2pm-4pm">
                                    2pm-4pm</label>
                                <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="4pm-6pm">
                                    4pm-6pm</label>
                            </div>
                        </div>
                    </div>
                </section>


                <!-- Account Details -->
                <section class="section col account-details">
                    <h3 class="section-heading">Crée ton propre compte</h3>

                    <div class="row">
                        <div class="col">
                            <label for="username">Nom d’utilisateur</label>
                            <input type="text" id="username" name="username"
                                placeholder="Un pseudo sympa pour te connecter facilement.">
                        </div>
                        <div class="col">
                            <label for="email">E-mail <span class="required">*</span></label>
                            <input type="email" id="email" name="email"
                                placeholder="On t’écrit seulement pour des infos importantes, promis !" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="password">Mot de passe <span class="required">*</span></label>
                            <input type="password" id="password" name="password"
                                placeholder="Simple mais sécurisé – comme les maths chez nous !" required>
                        </div>
                        <div class="col">
                            <label for="confirm_password">Confirmation du mot de passe <span
                                    class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                placeholder="Confirmez votre mot de passe" required>
                        </div>
                    </div>
                </section>

                <!-- Parent/Guardian Consent -->
                <div class="col guardian-consent">
                    <h3 class="section-heading">Consentement des parents/tuteurs</h3>
                    <div class="col checkbox-group">
                        <label class="row"><input type="checkbox" id="parent_consent" name="parent_consent"
                                value="Consent" required>
                            X Je certifie être le parent ou le représentant légal de l'enfant inscrit et donne mon
                            consentement explicite à son inscription et à sa participation aux cours de maths proposés
                            par Joomaths
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-button" name="submit_child_registration">Inscrivez votre
                    enfant</button>
            </form>

        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>
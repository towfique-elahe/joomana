<?php

/* Template Name: Student | Settings */

// page title
global $pageTitle;
$pageTitle = 'Paramètres';

require_once(get_template_directory() . '/student/templates/header.php');

// profile update backend
ob_start();

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students';
    $user_id = get_current_user_id(); // Assuming we get the current user ID

    // Sanitize user inputs
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirmation = isset($_POST['password_confirmation']) ? $_POST['password_confirmation'] : '';

    // Validate email format
    if (!empty($new_password) || !empty($old_password)) {
        // Check if passwords are provided and validate them
        if (empty($old_password)) {
            $error_message = 'Old password is required to change the password.';
        } elseif (empty($new_password) || empty($password_confirmation)) {
            $error_message = 'New password and confirmation are required.';
        } elseif ($new_password !== $password_confirmation) {
            $error_message = 'New password and confirmation do not match.';
        } else {
            // Validate old password
            $user = get_userdata($user_id);
            if (!wp_check_password($old_password, $user->user_pass, $user_id)) {
                $error_message = 'Old password is incorrect.';
            } else {
                // Update the password
                $user_data = array(
                    'ID' => $user_id,
                    'user_pass' => $new_password
                );

                $password_update_result = wp_update_user($user_data);

                if (is_wp_error($password_update_result)) {
                    $error_message = 'Failed to update the password: ' . $password_update_result->get_error_message();
                } else {
                    $success_message = 'Password updated successfully.';
                }
            }
        }
    }

    if (empty($error_message)) {
        // Update WordPress user's first name and last name
        $user_data = array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name
        );

        $user_update_result = wp_update_user($user_data);

        if (is_wp_error($user_update_result)) {
            $error_message = 'Failed to update user profile: ' . $user_update_result->get_error_message();
        } elseif (empty($success_message)) {
            $success_message = 'Profile updated successfully.';
        }
    }
}

ob_end_clean();
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>
    <div id="adminSettings" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Paramètres</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Paramètres</span>
            </div>
        </div>

        <div class="content-section">
            <!-- <h3 class="section-heading"><i class="fa fa-info-circle" aria-hidden="true"></i> heading</h3> -->

            <div class="section-body">
                <!-- profile update form -->
                <form class="parent-profile-update-form" method="post" action="">

                    <input type="hidden" name="csrf_token" value="<?php echo esc_attr($csrf_token); ?>">

                    <!-- Display error message -->
                    <?php if ($error_message): ?>
                    <div class="form-error">
                        <p>
                            <?php echo esc_html($error_message); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Display success message -->
                    <?php if ($success_message): ?>
                    <div class="form-success">
                        <p>
                            <?php echo esc_html($success_message); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Personal Information -->
                    <section class="section col personal-information">
                        <h3 class="section-heading">Renseignements Personnels</h3>

                        <div class="row">
                            <div class="col">
                                <label for="first_name">Prénom <span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" placeholder="Votre prénom"
                                    value="<?php echo esc_attr($first_name); ?>" required>
                            </div>
                            <div class="col">
                                <label for="last_name">Nom de famille <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" placeholder="Votre nom de famille"
                                    value="<?php echo esc_attr($last_name); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="email">Username <span class="required">*</span></label>
                                <input type="email" id="email" name="email" placeholder="Username"
                                    value="<?php echo esc_attr($user_login); ?>" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="email">E-mail <span class="required">*</span></label>
                                <input type="email" id="email" name="email" placeholder="Votre e-mail"
                                    value="<?php echo esc_attr($user_email); ?>" readonly>
                            </div>
                        </div>
                    </section>

                    <!-- Change Password -->
                    <section class="section col change-password">
                        <h3 class="section-heading">Changer le mot de passe</h3>

                        <div class="row">
                            <div class="col">
                                <label for="old_password">Ancien mot de passe <span class="required">*</span></label>
                                <input type="password" id="old_password" name="old_password"
                                    placeholder="Votre ancien mot de passe">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="password">Nouveau mot de passe <span class="required">*</span></label>
                                <input type="password" id="password" name="password"
                                    placeholder="Votre nouveau mot de passe">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="password_confirmation">Confirmer le nouveau mot de passe <span
                                        class="required">*</span></label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    placeholder="Confirmez votre nouveau mot de passe">
                            </div>
                        </div>
                    </section>

                    <button type="submit" class="submit-button" name="submit_parent_profile_update">Mise à jour</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>
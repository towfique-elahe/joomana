<?php

/* Template Name: Student | Settings */

// page title
global $pageTitle;
$pageTitle = 'Paramètres';

require_once(get_template_directory() . '/student/templates/header.php');

$error_message = '';
$success_message = '';

global $wpdb;

// Fetching the current user data from the teachers table
$user_id = get_current_user_id();
$table_name = $wpdb->prefix . 'students';
$user_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $user_id));

if ($user_data) {
    $image = $user_data->image;
    $first_name = $user_data->first_name;
    $last_name = $user_data->last_name;
    $email = $user_data->email;
} else {
    $first_name = $last_name = $email = '';
}

// Get WordPress user data
$wp_user = get_userdata($user_id);
$user_login = $wp_user->user_login;
$user_email = $wp_user->user_email;

// profile update backend
ob_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students';
    $user_id = get_current_user_id();

    // Sanitize user inputs
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirmation = isset($_POST['password_confirmation']) ? $_POST['password_confirmation'] : '';

    // Handle image deletion
    if (isset($_POST['delete_image'])) {
        if (!empty($image)) {
            // Get attachment ID from image URL
            $attachment_id = attachment_url_to_postid($image);
            
            if ($attachment_id) {
                // Delete the attachment from media library
                wp_delete_attachment($attachment_id, true);
            }
            
            // Update database with empty image
            $wpdb->update(
                $table_name,
                array('image' => ''),
                array('id' => $user_id)
            );
            
            $image = ''; // Update local variable
            $success_message = 'Image supprimée avec succès.';
        } else {
            $error_message = 'Aucune image à supprimer.';
        }
    }

    // Handle file upload to WordPress Media Library
    if (isset($_FILES['upload_image']) && $_FILES['upload_image']['error'] === UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $allowed_types = ['image/jpeg', 'image/png'];

        if (in_array($_FILES['upload_image']['type'], $allowed_types)) {
            if ($_FILES['upload_image']['size'] <= 2 * 1024 * 1024) {
                // Upload the file to WordPress Media Library
                $uploaded_file = wp_handle_upload($_FILES['upload_image'], ['test_form' => false]);
                
                if ($uploaded_file && !isset($uploaded_file['error'])) {
                    // Insert the uploaded file into the media library
                    $file = $uploaded_file['file'];
                    $attachment = array(
                        'guid'           => $uploaded_file['url'], 
                        'post_mime_type' => $_FILES['upload_image']['type'],
                        'post_title'     => sanitize_file_name($_FILES['upload_image']['name']),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    $attachment_id = wp_insert_attachment($attachment, $file);
                    
                    if (!is_wp_error($attachment_id)) {
                        // Generate metadata for the attachment
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
                        wp_update_attachment_metadata($attachment_id, $attachment_data);
                        
                        // Get the attachment URL
                        $uploaded_image_url = wp_get_attachment_url($attachment_id);
                        
                        // Delete old image if exists
                        if (!empty($image)) {
                            $old_attachment_id = attachment_url_to_postid($image);
                            if ($old_attachment_id) {
                                wp_delete_attachment($old_attachment_id, true);
                            }
                        }
                        
                        // Update database with new image
                        $wpdb->update(
                            $table_name,
                            array('image' => $uploaded_image_url),
                            array('id' => $user_id)
                        );
                        
                        $image = $uploaded_image_url; // Update local variable
                        $success_message = 'Image téléchargée avec succès.';
                    } else {
                        $error_message = 'Erreur lors de l\'insertion de l\'image dans la bibliothèque de médias.';
                    }
                } else {
                    $error_message = 'Erreur lors du téléchargement de l\'image : ' . $uploaded_file['error'];
                }
            } else {
                $error_message = 'La taille de l\'image ne doit pas dépasser 2 Mo.';
            }
        } else {
            $error_message = 'Format d\'image non valide. Seuls JPEG et PNG sont autorisés.';
        }
    }

    // Validate password changes
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
        // Update user information in the custom table
        $update_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name
        );
        
        // Only include image if it was updated
        if (isset($uploaded_image_url)) {
            $update_data['image'] = $uploaded_image_url;
        }
        
        $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $user_id)
        );

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

        // Redirect to prevent duplicate submission
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
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
            <div class="section-body">
                <!-- profile update form -->
                <form class="student-profile-update-form" method="post" action="" enctype="multipart/form-data">

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

                    <div class="profile-card">
                        <img alt=""
                            src="<?php echo esc_url( $image ? $image : get_template_directory_uri() . '/assets/image/user.png' ); ?>" />

                        <div class="overlay">
                            <div class="buttons">
                                <div class="button edit">
                                    <label for="upload_image" class="button edit">
                                        <i class="fas fa-upload"></i>
                                    </label>
                                    <input type="file" id="upload_image" name="upload_image"
                                        accept="image/jpeg, image/png" class="upload-input">
                                </div>
                                <button type="submit" name="delete_image" class="button delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text">(Images uniquement, JPEG/PNG, max 2 Mo)</p>
                    <p class="image-file-name">Aucun fichier sélectionné</p>

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
                                <input type="text" id="email" name="email" placeholder="Username"
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
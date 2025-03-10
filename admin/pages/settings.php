<?php

/* Template Name: Admin | Settings */

// page title
global $pageTitle;
$pageTitle = 'Paramètres';

require_once(get_template_directory() . '/admin/templates/header.php');

// profile update backend
ob_start();

$error_message = '';
$success_message = '';

$user_id = get_current_user_id(); // Assuming we get the current user ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize user inputs
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    
    $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirmation = isset($_POST['password_confirmation']) ? $_POST['password_confirmation'] : '';

    // Handle image upload
    // if (isset($_FILES['upload_image']) && !empty($_FILES['upload_image']['name'])) {
    //     $file = $_FILES['upload_image'];
        
    //     $allowed_file_types = array('image/jpeg', 'image/png');
        
    //     // Validate the file type
    //     $file_type = wp_check_filetype($file['name']);
    //     if (!in_array($file_type['type'], $allowed_file_types)) {
    //         $error_message = 'Only JPEG and PNG images are allowed.';
    //     } else {
    //         $upload_dir = wp_upload_dir();
    //         $filename = sanitize_file_name($file['name']);
    //         $upload_path = $upload_dir['path'] . '/' . $filename;

    //         if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    //             $upload_url = $upload_dir['url'] . '/' . $filename;
    //             update_user_meta($user_id, 'user_image', $upload_url);
    //             $success_message = 'Image uploaded successfully!';
    //         } else {
    //             $error_message = 'Failed to upload image.';
    //         }
    //     }
    // }

    // Handle image deletion
    // if (isset($_POST['delete_image'])) {
    //     $user_image_url = get_user_meta($user_id, 'user_image', true);
        
    //     if ($user_image_url) {
    //         $upload_dir = wp_upload_dir();
    //         $image_path = str_replace($upload_dir['url'], $upload_dir['path'], $user_image_url);
            
    //         if (file_exists($image_path)) {
    //             unlink($image_path);
    //             delete_user_meta($user_id, 'user_image');
    //             $success_message = 'Profile image deleted successfully.';
    //         }
    //     }
    // }

    // Update user profile and password (same as your existing code)
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
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminSettings" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Paramètres</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Paramètres</span>
            </div>
        </div>

        <div class="content-section">
            <div class="section-body">
                <!-- profile update form -->
                <form class="parent-profile-update-form" method="post" action="" enctype="multipart/form-data">

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

                    <!-- Profile Picture -->
                    <section class="section col personal-information">
                        <h3 class="section-heading">Renseignements Personnels</h3>

                        <div class="row">
                            <!-- <div class="col img-col">
                                <div class="profile-card">
                                    <img alt=""
                                        src="<?php echo esc_url(get_user_meta($user_id, 'user_image', true) ?: get_template_directory_uri() . '/assets/image/user.png'); ?>" />
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
                            </div> -->

                            <div class="col info-col">
                                <div class="row">
                                    <div class="col">
                                        <label for="first_name">Prénom <span class="required">*</span></label>
                                        <input type="text" id="first_name" name="first_name" placeholder="Votre prénom"
                                            value="<?php echo esc_attr($first_name); ?>" required>
                                    </div>
                                    <div class="col">
                                        <label for="last_name">Nom de famille <span class="required">*</span></label>
                                        <input type="text" id="last_name" name="last_name"
                                            placeholder="Votre nom de famille"
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

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>
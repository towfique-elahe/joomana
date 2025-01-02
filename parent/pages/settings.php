<?php

/* Template Name: Parent | Settings */

// page title
global $pageTitle;
$pageTitle = 'Paramètres';

require_once(get_template_directory() . '/parent/templates/header.php');
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="parentSettings" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Paramètres</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Paramètres</span>
            </div>
        </div>
        <div class="content-section account-info">
            <h3 class="section-heading">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                Information sur le compte
            </h3>
            <div class="section-body account-info">
                <!-- <div class="profile-card">
                    <img alt="" src="<?php echo get_template_directory_uri() . '/assets/image/user.png'; ?>" />
                    <div class="overlay">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                    </div>
                </div> -->

                <?php
                // Initialize variables to avoid undefined variable warnings
$error_message = '';
$success_message = '';


// Assuming $wpdb is the global WordPress database object
global $wpdb;

// Fetching the current user data from the parents table
$user_id = get_current_user_id(); // Assuming we get the current user ID
$table_name = $wpdb->prefix . 'parents'; // Table name with prefix
$user_data = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name, email, phone, address, city, zipcode, country FROM $table_name WHERE id = %d", $user_id));

if ($user_data) {
    $first_name = $user_data->first_name;
    $last_name = $user_data->last_name;
    $email = $user_data->email;
    $phone = $user_data->phone;
    $address = $user_data->address;
    $city = $user_data->city;
    $zipcode = $user_data->zipcode;
    $country = $user_data->country;
} else {
    // Handle the case where no data is found (e.g., user not found)
    $first_name = $last_name = $email = $phone = $address = $city = $zipcode = $country = '';
}




// profile update backend
ob_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'parents';
    $user_id = get_current_user_id(); // Assuming we get the current user ID

    // Sanitize user inputs
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $zipcode = isset($_POST['zipcode']) ? sanitize_text_field($_POST['zipcode']) : '';
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';

    $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirmation = isset($_POST['password_confirmation']) ? $_POST['password_confirmation'] : '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format';
    } elseif (!empty($new_password) || !empty($old_password)) {
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
        $wpdb->update(
            $table_name,
            array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'zipcode' => $zipcode,
                'country' => $country
            ),
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
    }
}
ob_end_clean();


?>

                <!-- profile update form -->
                <form class="parent-profile-update-form" method="post" action="">

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
                                <label for="email">E-mail <span class="required">*</span></label>
                                <input type="email" id="email" name="email" placeholder="Votre e-mail"
                                    value="<?php echo esc_attr($email); ?>" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="phone">Téléphone <span class="required">*</span></label>
                                <input type="text" id="phone" name="phone" placeholder="Votre téléphone"
                                    value="<?php echo esc_attr($phone); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="address">Adresse <span class="required">*</span></label>
                                <input type="text" id="address" name="address" placeholder="Votre adresse"
                                    value="<?php echo esc_attr($address); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="city">ville/quartier <span class="required">*</span></label>
                                <input type="text" id="city" name="city" placeholder="Votre ville/quartier"
                                    value="<?php echo esc_attr($city); ?>" required>
                            </div>
                            <div class="col">
                                <label for="zipcode">Zip/code postal <span class="required">*</span></label>
                                <input type="text" id="zipcode" name="zipcode" placeholder="Votre code postal"
                                    value="<?php echo esc_attr($zipcode); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="country">Pays/Région <span class="required">*</span></label>
                                <div class="custom-select-wrapper">
                                    <select id="country" name="country" required>
                                        <option value="" disabled>Sélectionnez un pays/une région</option>
                                        <option value="AF" <?php echo ($country == 'AF') ? 'selected' : ''; ?>>
                                            Afghanistan</option>
                                        <option value="AL" <?php echo ($country == 'AL') ? 'selected' : ''; ?>>Albania
                                        </option>
                                        <option value="DZ" <?php echo ($country == 'DZ') ? 'selected' : ''; ?>>Algeria
                                        </option>
                                        <option value="AS" <?php echo ($country == 'AS') ? 'selected' : ''; ?>>American
                                            Samoa</option>
                                        <option value="AD" <?php echo ($country == 'AD') ? 'selected' : ''; ?>>Andorra
                                        </option>
                                        <option value="AO" <?php echo ($country == 'AO') ? 'selected' : ''; ?>>Angola
                                        </option>
                                        <option value="AI" <?php echo ($country == 'AI') ? 'selected' : ''; ?>>Anguilla
                                        </option>
                                        <option value="AQ" <?php echo ($country == 'AQ') ? 'selected' : ''; ?>>
                                            Antarctica</option>
                                        <option value="AG" <?php echo ($country == 'AG') ? 'selected' : ''; ?>>Antigua
                                            and Barbuda</option>
                                        <option value="AR" <?php echo ($country == 'AR') ? 'selected' : ''; ?>>Argentina
                                        </option>
                                        <option value="AM" <?php echo ($country == 'AM') ? 'selected' : ''; ?>>Armenia
                                        </option>
                                        <option value="AW" <?php echo ($country == 'AW') ? 'selected' : ''; ?>>Aruba
                                        </option>
                                        <option value="AU" <?php echo ($country == 'AU') ? 'selected' : ''; ?>>Australia
                                        </option>
                                        <option value="AT" <?php echo ($country == 'AT') ? 'selected' : ''; ?>>Austria
                                        </option>
                                        <option value="AZ" <?php echo ($country == 'AZ') ? 'selected' : ''; ?>>
                                            Azerbaijan</option>
                                        <option value="BS" <?php echo ($country == 'BS') ? 'selected' : ''; ?>>Bahamas
                                        </option>
                                        <option value="BH" <?php echo ($country == 'BH') ? 'selected' : ''; ?>>Bahrain
                                        </option>
                                        <option value="BD" <?php echo ($country == 'BD') ? 'selected' : ''; ?>>
                                            Bangladesh</option>
                                        <option value="BB" <?php echo ($country == 'BB') ? 'selected' : ''; ?>>Barbados
                                        </option>
                                        <option value="BY" <?php echo ($country == 'BY') ? 'selected' : ''; ?>>Belarus
                                        </option>
                                        <option value="BE" <?php echo ($country == 'BE') ? 'selected' : ''; ?>>Belgium
                                        </option>
                                        <option value="BZ" <?php echo ($country == 'BZ') ? 'selected' : ''; ?>>Belize
                                        </option>
                                        <option value="BJ" <?php echo ($country == 'BJ') ? 'selected' : ''; ?>>Benin
                                        </option>
                                        <option value="BM" <?php echo ($country == 'BM') ? 'selected' : ''; ?>>Bermuda
                                        </option>
                                        <option value="BT" <?php echo ($country == 'BT') ? 'selected' : ''; ?>>Bhutan
                                        </option>
                                        <option value="BO" <?php echo ($country == 'BO') ? 'selected' : ''; ?>>Bolivia
                                        </option>
                                        <option value="BA" <?php echo ($country == 'BA') ? 'selected' : ''; ?>>Bosnia
                                            and Herzegovina</option>
                                        <option value="BW" <?php echo ($country == 'BW') ? 'selected' : ''; ?>>Botswana
                                        </option>
                                        <option value="BR" <?php echo ($country == 'BR') ? 'selected' : ''; ?>>Brazil
                                        </option>
                                        <option value="BN" <?php echo ($country == 'BN') ? 'selected' : ''; ?>>Brunei
                                            Darussalam</option>
                                        <option value="BG" <?php echo ($country == 'BG') ? 'selected' : ''; ?>>Bulgaria
                                        </option>
                                        <option value="BF" <?php echo ($country == 'BF') ? 'selected' : ''; ?>>Burkina
                                            Faso</option>
                                        <option value="BI" <?php echo ($country == 'BI') ? 'selected' : ''; ?>>Burundi
                                        </option>
                                        <option value="KH" <?php echo ($country == 'KH') ? 'selected' : ''; ?>>Cambodia
                                        </option>
                                        <option value="CM" <?php echo ($country == 'CM') ? 'selected' : ''; ?>>Cameroon
                                        </option>
                                        <option value="CA" <?php echo ($country == 'CA') ? 'selected' : ''; ?>>Canada
                                        </option>
                                        <option value="CV" <?php echo ($country == 'CV') ? 'selected' : ''; ?>>Cape
                                            Verde</option>
                                        <option value="KY" <?php echo ($country == 'KY') ? 'selected' : ''; ?>>Cayman
                                            Islands</option>
                                        <option value="CF" <?php echo ($country == 'CF') ? 'selected' : ''; ?>>Central
                                            African Republic</option>
                                        <option value="TD" <?php echo ($country == 'TD') ? 'selected' : ''; ?>>Chad
                                        </option>
                                        <option value="CL" <?php echo ($country == 'CL') ? 'selected' : ''; ?>>Chile
                                        </option>
                                        <option value="CN" <?php echo ($country == 'CN') ? 'selected' : ''; ?>>China
                                        </option>
                                        <option value="CO" <?php echo ($country == 'CO') ? 'selected' : ''; ?>>Colombia
                                        </option>
                                        <option value="KM" <?php echo ($country == 'KM') ? 'selected' : ''; ?>>Comoros
                                        </option>
                                        <option value="CG" <?php echo ($country == 'CG') ? 'selected' : ''; ?>>Congo
                                        </option>
                                        <option value="CD" <?php echo ($country == 'CD') ? 'selected' : ''; ?>>Congo
                                            (Democratic Republic)</option>
                                        <option value="CR" <?php echo ($country == 'CR') ? 'selected' : ''; ?>>Costa
                                            Rica</option>
                                        <option value="HR" <?php echo ($country == 'HR') ? 'selected' : ''; ?>>Croatia
                                        </option>
                                        <option value="CU" <?php echo ($country == 'CU') ? 'selected' : ''; ?>>Cuba
                                        </option>
                                        <option value="CY" <?php echo ($country == 'CY') ? 'selected' : ''; ?>>Cyprus
                                        </option>
                                        <option value="CZ" <?php echo ($country == 'CZ') ? 'selected' : ''; ?>>Czech
                                            Republic</option>
                                        <option value="CI" <?php echo ($country == 'CI') ? 'selected' : ''; ?>>Côte
                                            d'Ivoire</option>
                                        <option value="DK" <?php echo ($country == 'DK') ? 'selected' : ''; ?>>Denmark
                                        </option>
                                        <option value="DJ" <?php echo ($country == 'DJ') ? 'selected' : ''; ?>>Djibouti
                                        </option>
                                        <option value="DM" <?php echo ($country == 'DM') ? 'selected' : ''; ?>>Dominica
                                        </option>
                                        <option value="DO" <?php echo ($country == 'DO') ? 'selected' : ''; ?>>Dominican
                                            Republic</option>
                                        <option value="EC" <?php echo ($country == 'EC') ? 'selected' : ''; ?>>Ecuador
                                        </option>
                                        <option value="EG" <?php echo ($country == 'EG') ? 'selected' : ''; ?>>Egypt
                                        </option>
                                        <option value="SV" <?php echo ($country == 'SV') ? 'selected' : ''; ?>>El
                                            Salvador</option>
                                        <option value="GQ" <?php echo ($country == 'GQ') ? 'selected' : ''; ?>>
                                            Equatorial Guinea</option>
                                        <option value="ER" <?php echo ($country == 'ER') ? 'selected' : ''; ?>>Eritrea
                                        </option>
                                        <option value="EE" <?php echo ($country == 'EE') ? 'selected' : ''; ?>>Estonia
                                        </option>
                                        <option value="SZ" <?php echo ($country == 'SZ') ? 'selected' : ''; ?>>Eswatini
                                        </option>
                                        <option value="ET" <?php echo ($country == 'ET') ? 'selected' : ''; ?>>Ethiopia
                                        </option>
                                        <option value="FK" <?php echo ($country == 'FK') ? 'selected' : ''; ?>>Falkland
                                            Islands</option>
                                        <option value="FO" <?php echo ($country == 'FO') ? 'selected' : ''; ?>>Faroe
                                            Islands</option>
                                        <option value="FJ" <?php echo ($country == 'FJ') ? 'selected' : ''; ?>>Fiji
                                        </option>
                                        <option value="FI" <?php echo ($country == 'FI') ? 'selected' : ''; ?>>Finland
                                        </option>
                                        <option value="FR" <?php echo ($country == 'FR') ? 'selected' : ''; ?>>France
                                        </option>
                                        <option value="GF" <?php echo ($country == 'GF') ? 'selected' : ''; ?>>French
                                            Guiana</option>
                                        <option value="PF" <?php echo ($country == 'PF') ? 'selected' : ''; ?>>French
                                            Polynesia</option>
                                        <option value="GA" <?php echo ($country == 'GA') ? 'selected' : ''; ?>>Gabon
                                        </option>
                                        <option value="GM" <?php echo ($country == 'GM') ? 'selected' : ''; ?>>Gambia
                                        </option>
                                        <option value="GE" <?php echo ($country == 'GE') ? 'selected' : ''; ?>>Georgia
                                        </option>
                                        <option value="DE" <?php echo ($country == 'DE') ? 'selected' : ''; ?>>Germany
                                        </option>
                                        <option value="GH" <?php echo ($country == 'GH') ? 'selected' : ''; ?>>Ghana
                                        </option>
                                        <option value="GI" <?php echo ($country == 'GI') ? 'selected' : ''; ?>>Gibraltar
                                        </option>
                                        <option value="GR" <?php echo ($country == 'GR') ? 'selected' : ''; ?>>Greece
                                        </option>
                                        <option value="GL" <?php echo ($country == 'GL') ? 'selected' : ''; ?>>Greenland
                                        </option>
                                        <option value="GD" <?php echo ($country == 'GD') ? 'selected' : ''; ?>>Grenada
                                        </option>
                                        <option value="GU" <?php echo ($country == 'GU') ? 'selected' : ''; ?>>Guam
                                        </option>
                                        <option value="GT" <?php echo ($country == 'GT') ? 'selected' : ''; ?>>Guatemala
                                        </option>
                                        <option value="GN" <?php echo ($country == 'GN') ? 'selected' : ''; ?>>Guinea
                                        </option>
                                        <option value="GW" <?php echo ($country == 'GW') ? 'selected' : ''; ?>>
                                            Guinea-Bissau</option>
                                        <option value="GY" <?php echo ($country == 'GY') ? 'selected' : ''; ?>>Guyana
                                        </option>
                                        <option value="HT" <?php echo ($country == 'HT') ? 'selected' : ''; ?>>Haiti
                                        </option>
                                        <option value="HN" <?php echo ($country == 'HN') ? 'selected' : ''; ?>>Honduras
                                        </option>
                                        <option value="HK" <?php echo ($country == 'HK') ? 'selected' : ''; ?>>Hong Kong
                                        </option>
                                        <option value="HU" <?php echo ($country == 'HU') ? 'selected' : ''; ?>>Hungary
                                        </option>
                                        <option value="IS" <?php echo ($country == 'IS') ? 'selected' : ''; ?>>Iceland
                                        </option>
                                        <option value="IN" <?php echo ($country == 'IN') ? 'selected' : ''; ?>>India
                                        </option>
                                        <option value="ID" <?php echo ($country == 'ID') ? 'selected' : ''; ?>>Indonesia
                                        </option>
                                        <option value="IR" <?php echo ($country == 'IR') ? 'selected' : ''; ?>>Iran
                                        </option>
                                        <option value="IQ" <?php echo ($country == 'IQ') ? 'selected' : ''; ?>>Iraq
                                        </option>
                                        <option value="IE" <?php echo ($country == 'IE') ? 'selected' : ''; ?>>Ireland
                                        </option>
                                        <option value="IL" <?php echo ($country == 'IL') ? 'selected' : ''; ?>>Israel
                                        </option>
                                        <option value="IT" <?php echo ($country == 'IT') ? 'selected' : ''; ?>>Italy
                                        </option>
                                        <option value="JM" <?php echo ($country == 'JM') ? 'selected' : ''; ?>>Jamaica
                                        </option>
                                        <option value="JP" <?php echo ($country == 'JP') ? 'selected' : ''; ?>>Japan
                                        </option>
                                        <option value="JO" <?php echo ($country == 'JO') ? 'selected' : ''; ?>>Jordan
                                        </option>
                                        <option value="KZ" <?php echo ($country == 'KZ') ? 'selected' : ''; ?>>
                                            Kazakhstan</option>
                                        <option value="KE" <?php echo ($country == 'KE') ? 'selected' : ''; ?>>Kenya
                                        </option>
                                        <option value="KI" <?php echo ($country == 'KI') ? 'selected' : ''; ?>>Kiribati
                                        </option>
                                        <option value="KR" <?php echo ($country == 'KR') ? 'selected' : ''; ?>>Korea
                                            (South)</option>
                                        <option value="KW" <?php echo ($country == 'KW') ? 'selected' : ''; ?>>Kuwait
                                        </option>
                                        <option value="KG" <?php echo ($country == 'KG') ? 'selected' : ''; ?>>
                                            Kyrgyzstan</option>
                                        <option value="LA" <?php echo ($country == 'LA') ? 'selected' : ''; ?>>Laos
                                        </option>
                                        <option value="LV" <?php echo ($country == 'LV') ? 'selected' : ''; ?>>Latvia
                                        </option>
                                        <option value="LB" <?php echo ($country == 'LB') ? 'selected' : ''; ?>>Lebanon
                                        </option>
                                        <option value="LS" <?php echo ($country == 'LS') ? 'selected' : ''; ?>>Lesotho
                                        </option>
                                        <option value="LR" <?php echo ($country == 'LR') ? 'selected' : ''; ?>>Liberia
                                        </option>
                                        <option value="LY" <?php echo ($country == 'LY') ? 'selected' : ''; ?>>Libya
                                        </option>
                                        <option value="LI" <?php echo ($country == 'LI') ? 'selected' : ''; ?>>
                                            Liechtenstein</option>
                                        <option value="LT" <?php echo ($country == 'LT') ? 'selected' : ''; ?>>Lithuania
                                        </option>
                                        <option value="LU" <?php echo ($country == 'LU') ? 'selected' : ''; ?>>
                                            Luxembourg</option>
                                        <option value="MO" <?php echo ($country == 'MO') ? 'selected' : ''; ?>>Macao
                                        </option>
                                        <option value="MG" <?php echo ($country == 'MG') ? 'selected' : ''; ?>>
                                            Madagascar</option>
                                        <option value="MW" <?php echo ($country == 'MW') ? 'selected' : ''; ?>>Malawi
                                        </option>
                                        <option value="MY" <?php echo ($country == 'MY') ? 'selected' : ''; ?>>Malaysia
                                        </option>
                                        <option value="MV" <?php echo ($country == 'MV') ? 'selected' : ''; ?>>Maldives
                                        </option>
                                        <option value="ML" <?php echo ($country == 'ML') ? 'selected' : ''; ?>>Mali
                                        </option>
                                        <option value="MT" <?php echo ($country == 'MT') ? 'selected' : ''; ?>>Malta
                                        </option>
                                        <option value="MH" <?php echo ($country == 'MH') ? 'selected' : ''; ?>>Marshall
                                            Islands</option>
                                        <option value="MR" <?php echo ($country == 'MR') ? 'selected' : ''; ?>>
                                            Mauritania</option>
                                        <option value="MU" <?php echo ($country == 'MU') ? 'selected' : ''; ?>>Mauritius
                                        </option>
                                        <option value="MX" <?php echo ($country == 'MX') ? 'selected' : ''; ?>>Mexico
                                        </option>
                                        <option value="FM" <?php echo ($country == 'FM') ? 'selected' : ''; ?>>
                                            Micronesia</option>
                                        <option value="MD" <?php echo ($country == 'MD') ? 'selected' : ''; ?>>Moldova
                                        </option>
                                        <option value="MC" <?php echo ($country == 'MC') ? 'selected' : ''; ?>>Monaco
                                        </option>
                                        <option value="MN" <?php echo ($country == 'MN') ? 'selected' : ''; ?>>Mongolia
                                        </option>
                                        <option value="ME" <?php echo ($country == 'ME') ? 'selected' : ''; ?>>
                                            Montenegro</option>
                                        <option value="MA" <?php echo ($country == 'MA') ? 'selected' : ''; ?>>Morocco
                                        </option>
                                        <option value="MZ" <?php echo ($country == 'MZ') ? 'selected' : ''; ?>>
                                            Mozambique</option>
                                        <option value="MM" <?php echo ($country == 'MM') ? 'selected' : ''; ?>>Myanmar
                                        </option>
                                        <option value="NA" <?php echo ($country == 'NA') ? 'selected' : ''; ?>>Namibia
                                        </option>
                                        <option value="NR" <?php echo ($country == 'NR') ? 'selected' : ''; ?>>Nauru
                                        </option>
                                        <option value="NP" <?php echo ($country == 'NP') ? 'selected' : ''; ?>>Nepal
                                        </option>
                                        <option value="NL" <?php echo ($country == 'NL') ? 'selected' : ''; ?>>
                                            Netherlands</option>
                                        <option value="NZ" <?php echo ($country == 'NZ') ? 'selected' : ''; ?>>New
                                            Zealand</option>
                                        <option value="NI" <?php echo ($country == 'NI') ? 'selected' : ''; ?>>Nicaragua
                                        </option>
                                        <option value="NE" <?php echo ($country == 'NE') ? 'selected' : ''; ?>>Niger
                                        </option>
                                        <option value="NG" <?php echo ($country == 'NG') ? 'selected' : ''; ?>>Nigeria
                                        </option>
                                        <option value="KP" <?php echo ($country == 'KP') ? 'selected' : ''; ?>>North
                                            Korea</option>
                                        <option value="MK" <?php echo ($country == 'MK') ? 'selected' : ''; ?>>North
                                            Macedonia</option>
                                        <option value="NO" <?php echo ($country == 'NO') ? 'selected' : ''; ?>>Norway
                                        </option>
                                        <option value="OM" <?php echo ($country == 'OM') ? 'selected' : ''; ?>>Oman
                                        </option>
                                        <option value="PK" <?php echo ($country == 'PK') ? 'selected' : ''; ?>>Pakistan
                                        </option>
                                        <option value="PW" <?php echo ($country == 'PW') ? 'selected' : ''; ?>>Palau
                                        </option>
                                        <option value="PA" <?php echo ($country == 'PA') ? 'selected' : ''; ?>>Panama
                                        </option>
                                        <option value="PG" <?php echo ($country == 'PG') ? 'selected' : ''; ?>>Papua New
                                            Guinea</option>
                                        <option value="PY" <?php echo ($country == 'PY') ? 'selected' : ''; ?>>Paraguay
                                        </option>
                                        <option value="PE" <?php echo ($country == 'PE') ? 'selected' : ''; ?>>Peru
                                        </option>
                                        <option value="PH" <?php echo ($country == 'PH') ? 'selected' : ''; ?>>
                                            Philippines</option>
                                        <option value="PL" <?php echo ($country == 'PL') ? 'selected' : ''; ?>>Poland
                                        </option>
                                        <option value="PT" <?php echo ($country == 'PT') ? 'selected' : ''; ?>>Portugal
                                        </option>
                                        <option value="QA" <?php echo ($country == 'QA') ? 'selected' : ''; ?>>Qatar
                                        </option>
                                        <option value="RO" <?php echo ($country == 'RO') ? 'selected' : ''; ?>>Romania
                                        </option>
                                        <option value="RU" <?php echo ($country == 'RU') ? 'selected' : ''; ?>>Russia
                                        </option>
                                        <option value="RW" <?php echo ($country == 'RW') ? 'selected' : ''; ?>>Rwanda
                                        </option>
                                        <option value="KN" <?php echo ($country == 'KN') ? 'selected' : ''; ?>>Saint
                                            Kitts and Nevis</option>
                                        <option value="LC" <?php echo ($country == 'LC') ? 'selected' : ''; ?>>Saint
                                            Lucia</option>
                                        <option value="VC" <?php echo ($country == 'VC') ? 'selected' : ''; ?>>Saint
                                            Vincent and the Grenadines</option>
                                        <option value="WS" <?php echo ($country == 'WS') ? 'selected' : ''; ?>>Samoa
                                        </option>
                                        <option value="SM" <?php echo ($country == 'SM') ? 'selected' : ''; ?>>San
                                            Marino</option>
                                        <option value="SA" <?php echo ($country == 'SA') ? 'selected' : ''; ?>>Saudi
                                            Arabia</option>
                                        <option value="SN" <?php echo ($country == 'SN') ? 'selected' : ''; ?>>Senegal
                                        </option>
                                        <option value="RS" <?php echo ($country == 'RS') ? 'selected' : ''; ?>>Serbia
                                        </option>
                                        <option value="SC" <?php echo ($country == 'SC') ? 'selected' : ''; ?>>
                                            Seychelles</option>
                                        <option value="SL" <?php echo ($country == 'SL') ? 'selected' : ''; ?>>Sierra
                                            Leone</option>
                                        <option value="SG" <?php echo ($country == 'SG') ? 'selected' : ''; ?>>Singapore
                                        </option>
                                        <option value="SK" <?php echo ($country == 'SK') ? 'selected' : ''; ?>>Slovakia
                                        </option>
                                        <option value="SI" <?php echo ($country == 'SI') ? 'selected' : ''; ?>>Slovenia
                                        </option>
                                        <option value="SB" <?php echo ($country == 'SB') ? 'selected' : ''; ?>>Solomon
                                            Islands</option>
                                        <option value="SO" <?php echo ($country == 'SO') ? 'selected' : ''; ?>>Somalia
                                        </option>
                                        <option value="ZA" <?php echo ($country == 'ZA') ? 'selected' : ''; ?>>South
                                            Africa</option>
                                        <option value="KR" <?php echo ($country == 'KR') ? 'selected' : ''; ?>>South
                                            Korea</option>
                                        <option value="SS" <?php echo ($country == 'SS') ? 'selected' : ''; ?>>South
                                            Sudan</option>
                                        <option value="ES" <?php echo ($country == 'ES') ? 'selected' : ''; ?>>Spain
                                        </option>
                                        <option value="LK" <?php echo ($country == 'LK') ? 'selected' : ''; ?>>Sri Lanka
                                        </option>
                                        <option value="SD" <?php echo ($country == 'SD') ? 'selected' : ''; ?>>Sudan
                                        </option>
                                        <option value="SR" <?php echo ($country == 'SR') ? 'selected' : ''; ?>>Suriname
                                        </option>
                                        <option value="SE" <?php echo ($country == 'SE') ? 'selected' : ''; ?>>Sweden
                                        </option>
                                        <option value="CH" <?php echo ($country == 'CH') ? 'selected' : ''; ?>>
                                            Switzerland</option>
                                        <option value="SY" <?php echo ($country == 'SY') ? 'selected' : ''; ?>>Syria
                                        </option>
                                        <option value="TW" <?php echo ($country == 'TW') ? 'selected' : ''; ?>>Taiwan
                                        </option>
                                        <option value="TJ" <?php echo ($country == 'TJ') ? 'selected' : ''; ?>>
                                            Tajikistan</option>
                                        <option value="TZ" <?php echo ($country == 'TZ') ? 'selected' : ''; ?>>Tanzania
                                        </option>
                                        <option value="TH" <?php echo ($country == 'TH') ? 'selected' : ''; ?>>Thailand
                                        </option>
                                        <option value="TL" <?php echo ($country == 'TL') ? 'selected' : ''; ?>>
                                            Timor-Leste</option>
                                        <option value="TG" <?php echo ($country == 'TG') ? 'selected' : ''; ?>>Togo
                                        </option>
                                        <option value="TO" <?php echo ($country == 'TO') ? 'selected' : ''; ?>>Tonga
                                        </option>
                                        <option value="TT" <?php echo ($country == 'TT') ? 'selected' : ''; ?>>Trinidad
                                            and Tobago</option>
                                        <option value="TN" <?php echo ($country == 'TN') ? 'selected' : ''; ?>>Tunisia
                                        </option>
                                        <option value="TR" <?php echo ($country == 'TR') ? 'selected' : ''; ?>>Turkey
                                        </option>
                                        <option value="TM" <?php echo ($country == 'TM') ? 'selected' : ''; ?>>
                                            Turkmenistan</option>
                                        <option value="TV" <?php echo ($country == 'TV') ? 'selected' : ''; ?>>Tuvalu
                                        </option>
                                        <option value="UG" <?php echo ($country == 'UG') ? 'selected' : ''; ?>>Uganda
                                        </option>
                                        <option value="UA" <?php echo ($country == 'UA') ? 'selected' : ''; ?>>Ukraine
                                        </option>
                                        <option value="AE" <?php echo ($country == 'AE') ? 'selected' : ''; ?>>United
                                            Arab Emirates</option>
                                        <option value="GB" <?php echo ($country == 'GB') ? 'selected' : ''; ?>>United
                                            Kingdom</option>
                                        <option value="US" <?php echo ($country == 'US') ? 'selected' : ''; ?>>United
                                            States</option>
                                        <option value="UY" <?php echo ($country == 'UY') ? 'selected' : ''; ?>>Uruguay
                                        </option>
                                        <option value="UZ" <?php echo ($country == 'UZ') ? 'selected' : ''; ?>>
                                            Uzbekistan</option>
                                        <option value="VU" <?php echo ($country == 'VU') ? 'selected' : ''; ?>>Vanuatu
                                        </option>
                                        <option value="VE" <?php echo ($country == 'VE') ? 'selected' : ''; ?>>Venezuela
                                        </option>
                                        <option value="VN" <?php echo ($country == 'VN') ? 'selected' : ''; ?>>Vietnam
                                        </option>
                                        <option value="YE" <?php echo ($country == 'YE') ? 'selected' : ''; ?>>Yemen
                                        </option>
                                        <option value="ZM" <?php echo ($country == 'ZM') ? 'selected' : ''; ?>>Zambia
                                        </option>
                                        <option value="ZW" <?php echo ($country == 'ZW') ? 'selected' : ''; ?>>Zimbabwe
                                        </option>
                                    </select>
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

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>
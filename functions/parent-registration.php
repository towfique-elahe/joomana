<?php

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Parent Registration Form

// shortcode [parent_registration_form]

function custom_parent_registration_form() {
    ob_start();

    // Check for form errors or success messages
    $error_message = isset($_SESSION['registration_error']) ? $_SESSION['registration_error'] : '';
    $success_message = isset($_SESSION['registration_success']) ? $_SESSION['registration_success'] : '';

    // Clear session messages after displaying
    unset($_SESSION['registration_error'], $_SESSION['registration_success']);

    $csrf_token = wp_create_nonce('parent_registration_form');
    ?>
<form class="parent-registration-form" method="post" action="">

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
        <h3 class="section-heading">Renseignements personnels</h3>

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
                <label for="email">E-mail <span class="required">*</span></label>
                <input type="email" id="email" name="email" placeholder="Votre email" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="phone">Téléphone <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" placeholder="Votre téléphone" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="address">Adresse <span class="required">*</span></label>
                <input type="text" id="address" name="address" placeholder="Votre adresse" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="city">ville/quartier <span class="required">*</span></label>
                <input type="text" id="city" name="city" placeholder="Votre ville/quartier" required>
            </div>
            <div class="col">
                <label for="zipcode">Zip/code postal <span class="required">*</span></label>
                <input type="text" id="zipcode" name="zipcode" placeholder="Votre code postal" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="country">Pays/Région <span class="required">*</span></label>
                <div class="custom-select-wrapper">
                    <select id="country" name="country" required>
                        <option value="" disabled selected>Sélectionnez un pays/une région</option>
                        <option value="AF">Afghanistan</option>
                        <option value="AL">Albania</option>
                        <option value="DZ">Algeria</option>
                        <option value="AS">American Samoa</option>
                        <option value="AD">Andorra</option>
                        <option value="AO">Angola</option>
                        <option value="AI">Anguilla</option>
                        <option value="AQ">Antarctica</option>
                        <option value="AG">Antigua and Barbuda</option>
                        <option value="AR">Argentina</option>
                        <option value="AM">Armenia</option>
                        <option value="AW">Aruba</option>
                        <option value="AU">Australia</option>
                        <option value="AT">Austria</option>
                        <option value="AZ">Azerbaijan</option>
                        <option value="BS">Bahamas</option>
                        <option value="BH">Bahrain</option>
                        <option value="BD">Bangladesh</option>
                        <option value="BB">Barbados</option>
                        <option value="BY">Belarus</option>
                        <option value="BE">Belgium</option>
                        <option value="BZ">Belize</option>
                        <option value="BJ">Benin</option>
                        <option value="BM">Bermuda</option>
                        <option value="BT">Bhutan</option>
                        <option value="BO">Bolivia</option>
                        <option value="BA">Bosnia and Herzegovina</option>
                        <option value="BW">Botswana</option>
                        <option value="BR">Brazil</option>
                        <option value="BN">Brunei Darussalam</option>
                        <option value="BG">Bulgaria</option>
                        <option value="BF">Burkina Faso</option>
                        <option value="BI">Burundi</option>
                        <option value="KH">Cambodia</option>
                        <option value="CM">Cameroon</option>
                        <option value="CA">Canada</option>
                        <option value="CV">Cape Verde</option>
                        <option value="KY">Cayman Islands</option>
                        <option value="CF">Central African Republic</option>
                        <option value="TD">Chad</option>
                        <option value="CL">Chile</option>
                        <option value="CN">China</option>
                        <option value="CO">Colombia</option>
                        <option value="KM">Comoros</option>
                        <option value="CG">Congo</option>
                        <option value="CD">Congo (Democratic Republic)</option>
                        <option value="CR">Costa Rica</option>
                        <option value="HR">Croatia</option>
                        <option value="CU">Cuba</option>
                        <option value="CY">Cyprus</option>
                        <option value="CZ">Czech Republic</option>
                        <option value="CI">Côte d'Ivoire</option>
                        <option value="DK">Denmark</option>
                        <option value="DJ">Djibouti</option>
                        <option value="DM">Dominica</option>
                        <option value="DO">Dominican Republic</option>
                        <option value="EC">Ecuador</option>
                        <option value="EG">Egypt</option>
                        <option value="SV">El Salvador</option>
                        <option value="GQ">Equatorial Guinea</option>
                        <option value="ER">Eritrea</option>
                        <option value="EE">Estonia</option>
                        <option value="SZ">Eswatini</option>
                        <option value="ET">Ethiopia</option>
                        <option value="FK">Falkland Islands</option>
                        <option value="FO">Faroe Islands</option>
                        <option value="FJ">Fiji</option>
                        <option value="FI">Finland</option>
                        <option value="FR">France</option>
                        <option value="GF">French Guiana</option>
                        <option value="PF">French Polynesia</option>
                        <option value="GA">Gabon</option>
                        <option value="GM">Gambia</option>
                        <option value="GE">Georgia</option>
                        <option value="DE">Germany</option>
                        <option value="GH">Ghana</option>
                        <option value="GI">Gibraltar</option>
                        <option value="GR">Greece</option>
                        <option value="GL">Greenland</option>
                        <option value="GD">Grenada</option>
                        <option value="GU">Guam</option>
                        <option value="GT">Guatemala</option>
                        <option value="GN">Guinea</option>
                        <option value="GW">Guinea-Bissau</option>
                        <option value="GY">Guyana</option>
                        <option value="HT">Haiti</option>
                        <option value="HN">Honduras</option>
                        <option value="HK">Hong Kong</option>
                        <option value="HU">Hungary</option>
                        <option value="IS">Iceland</option>
                        <option value="IN">India</option>
                        <option value="ID">Indonesia</option>
                        <option value="IR">Iran</option>
                        <option value="IQ">Iraq</option>
                        <option value="IE">Ireland</option>
                        <option value="IL">Israel</option>
                        <option value="IT">Italy</option>
                        <option value="JM">Jamaica</option>
                        <option value="JP">Japan</option>
                        <option value="JO">Jordan</option>
                        <option value="KZ">Kazakhstan</option>
                        <option value="KE">Kenya</option>
                        <option value="KI">Kiribati</option>
                        <option value="KR">Korea (South)</option>
                        <option value="KW">Kuwait</option>
                        <option value="KG">Kyrgyzstan</option>
                        <option value="LA">Laos</option>
                        <option value="LV">Latvia</option>
                        <option value="LB">Lebanon</option>
                        <option value="LS">Lesotho</option>
                        <option value="LR">Liberia</option>
                        <option value="LY">Libya</option>
                        <option value="LI">Liechtenstein</option>
                        <option value="LT">Lithuania</option>
                        <option value="LU">Luxembourg</option>
                        <option value="MO">Macau</option>
                        <option value="MG">Madagascar</option>
                        <option value="MW">Malawi</option>
                        <option value="MY">Malaysia</option>
                        <option value="MV">Maldives</option>
                        <option value="ML">Mali</option>
                        <option value="MT">Malta</option>
                        <option value="MH">Marshall Islands</option>
                        <option value="MQ">Martinique</option>
                        <option value="MR">Mauritania</option>
                        <option value="MU">Mauritius</option>
                        <option value="YT">Mayotte</option>
                        <option value="MX">Mexico</option>
                        <option value="FM">Micronesia</option>
                        <option value="MD">Moldova</option>
                        <option value="MC">Monaco</option>
                        <option value="MN">Mongolia</option>
                        <option value="ME">Montenegro</option>
                        <option value="MS">Montserrat</option>
                        <option value="MA">Morocco</option>
                        <option value="MZ">Mozambique</option>
                        <option value="MM">Myanmar</option>
                        <option value="NA">Namibia</option>
                        <option value="NR">Nauru</option>
                        <option value="NP">Nepal</option>
                        <option value="NL">Netherlands</option>
                        <option value="NC">New Caledonia</option>
                        <option value="NZ">New Zealand</option>
                        <option value="NI">Nicaragua</option>
                        <option value="NE">Niger</option>
                        <option value="NG">Nigeria</option>
                        <option value="NU">Niue</option>
                        <option value="NF">Norfolk Island</option>
                        <option value="MP">Northern Mariana Islands</option>
                        <option value="NO">Norway</option>
                        <option value="OM">Oman</option>
                        <option value="PK">Pakistan</option>
                        <option value="PW">Palau</option>
                        <option value="PA">Panama</option>
                        <option value="PG">Papua New Guinea</option>
                        <option value="PY">Paraguay</option>
                        <option value="PE">Peru</option>
                        <option value="PH">Philippines</option>
                        <option value="PL">Poland</option>
                        <option value="PT">Portugal</option>
                        <option value="PR">Puerto Rico</option>
                        <option value="QA">Qatar</option>
                        <option value="RE">Réunion</option>
                        <option value="RO">Romania</option>
                        <option value="RU">Russia</option>
                        <option value="RW">Rwanda</option>
                        <option value="BL">Saint Barthélemy</option>
                        <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                        <option value="KN">Saint Kitts and Nevis</option>
                        <option value="LC">Saint Lucia</option>
                        <option value="MF">Saint Martin</option>
                        <option value="PM">Saint Pierre and Miquelon</option>
                        <option value="VC">Saint Vincent and the Grenadines</option>
                        <option value="WS">Samoa</option>
                        <option value="SM">San Marino</option>
                        <option value="ST">Sao Tome and Principe</option>
                        <option value="SA">Saudi Arabia</option>
                        <option value="SN">Senegal</option>
                        <option value="RS">Serbia</option>
                        <option value="SC">Seychelles</option>
                        <option value="SL">Sierra Leone</option>
                        <option value="SG">Singapore</option>
                        <option value="SX">Sint Maarten</option>
                        <option value="SK">Slovakia</option>
                        <option value="SI">Slovenia</option>
                        <option value="SB">Solomon Islands</option>
                        <option value="SO">Somalia</option>
                        <option value="ZA">South Africa</option>
                        <option value="GS">South Georgia and the South Sandwich Islands</option>
                        <option value="ES">Spain</option>
                        <option value="LK">Sri Lanka</option>
                        <option value="SD">Sudan</option>
                        <option value="SR">Suriname</option>
                        <option value="SJ">Svalbard and Jan Mayen</option>
                        <option value="SE">Sweden</option>
                        <option value="CH">Switzerland</option>
                        <option value="SY">Syrian Arab Republic</option>
                        <option value="TW">Taiwan</option>
                        <option value="TJ">Tajikistan</option>
                        <option value="TZ">Tanzania</option>
                        <option value="TH">Thailand</option>
                        <option value="TL">Timor-Leste</option>
                        <option value="TG">Togo</option>
                        <option value="TK">Tokelau</option>
                        <option value="TO">Tonga</option>
                        <option value="TT">Trinidad and Tobago</option>
                        <option value="TN">Tunisia</option>
                        <option value="TR">Turkey</option>
                        <option value="TM">Turkmenistan</option>
                        <option value="TC">Turks and Caicos Islands</option>
                        <option value="TV">Tuvalu</option>
                        <option value="UG">Uganda</option>
                        <option value="UA">Ukraine</option>
                        <option value="AE">United Arab Emirates</option>
                        <option value="GB">United Kingdom</option>
                        <option value="US">United States of America</option>
                        <option value="UY">Uruguay</option>
                        <option value="UZ">Uzbekistan</option>
                        <option value="VU">Vanuatu</option>
                        <option value="VE">Venezuela</option>
                        <option value="VN">Viet Nam</option>
                        <option value="WF">Wallis and Futuna</option>
                        <option value="EH">Western Sahara</option>
                        <option value="YE">Yemen</option>
                        <option value="ZM">Zambia</option>
                        <option value="ZW">Zimbabwe</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" placeholder="Votre nom d'utilisateur">
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="password">Mot de passe <span class="required">*</span></label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="password_confirmation">Confirmez le mot de passe <span class="required">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    placeholder="Confirmez votre mot de passe" required>
            </div>
        </div>
    </section>

    <button type="submit" class="submit-button" name="submit_registration">Valider</button>
</form>
<?php
    return ob_get_clean();
}
add_shortcode('parent_registration_form', 'custom_parent_registration_form');






// Handle form submission
function handle_parent_registration_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
        global $wpdb;

        // Sanitize and validate form inputs
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $zipcode = sanitize_text_field($_POST['zipcode']);
        $country = sanitize_text_field($_POST['country']);
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];

        // Check required fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address) || 
            empty($city) || empty($zipcode) || empty($country) || empty($password)) {
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
        if ($password !== $password_confirmation) {
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

        // Assign 'parent' role to the user
        $user = get_user_by('ID', $user_id);
        $user->set_role('parent');

        // Verify if the user has the 'parent' role
        if (in_array('parent', $user->roles)) {
            // Add first and last name to WordPress user meta
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);

            // Save data to the custom 'parents' table
            $table_name = $wpdb->prefix . 'parents';
            $wpdb->insert($table_name, [
                'id' => $user_id, // Link WordPress user ID
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'zipcode' => $zipcode,
                'country' => $country,
                'created_at' => current_time('mysql'),
            ]);

            // Set success message
            $_SESSION['registration_success'] = 'Inscription réussie. Bienvenue sur Joomaths !';

            // Redirect to the current page to prevent form resubmission
            wp_safe_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            // If the role is not 'parent', display an error (should not happen in this context)
            $_SESSION['registration_error'] = "Échec de l'attribution du rôle parent. Inscription interrompue.";
        }
    }
}
add_action('init', 'handle_parent_registration_form');
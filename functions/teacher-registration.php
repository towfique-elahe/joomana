<?php

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



// Teacher's Database Table

function create_teachers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teachers'; // Table name with prefix
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL,
        status ENUM('in review', 'on hold', 'rejected', 'approved') NOT NULL DEFAULT 'in review',
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NULL,
        country VARCHAR(255) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(255) NOT NULL,
        postal_code VARCHAR(50) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        degree VARCHAR(255) NOT NULL,
        institute VARCHAR(255) NOT NULL,
        graduation_year YEAR NOT NULL,
        subjects_of_interest TEXT NOT NULL,
        motivation_of_joining TEXT NOT NULL,
        available_days TEXT NOT NULL,
        monday_timeslot TEXT NULL,
        tuesday_timeslot TEXT NULL,
        wednesday_timeslot TEXT NULL,
        thursday_timeslot TEXT NULL,
        friday_timeslot TEXT NULL,
        saturday_timeslot TEXT NULL,
        sunday_timeslot TEXT NULL,
        upload_cv VARCHAR(255) NULL,
        upload_doc1 VARCHAR(255) NULL,
        upload_doc2 VARCHAR(255) NULL,
        upload_doc3 VARCHAR(255) NULL,
        upload_doc4 VARCHAR(255) NULL,
        upload_doc5 VARCHAR(255) NULL,
        upload_video VARCHAR(255) NULL,
        signature TEXT NOT NULL,
        signature_date DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_teachers_table');





// Teacher Registration Form

// shortcode [teacher_registration_form]

function custom_teacher_registration_form() {
    ob_start(); // Start output buffering

        // Check for form errors or success messages
        $error_message = isset($_SESSION['registration_error']) ? $_SESSION['registration_error'] : '';
        $success_message = isset($_SESSION['registration_success']) ? $_SESSION['registration_success'] : '';
    
        // Clear session messages after displaying
        unset($_SESSION['registration_error'], $_SESSION['registration_success']);
    
        $csrf_token = wp_create_nonce('teacher_registration_form');
    ?>
<form class="teacher-registration-form" method="post" action="">

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
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" placeholder="Nom d'utilisateur">
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
                <label for="password">Mot de passe <span class="required">*</span></label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="confirm_password">Confirmez le mot de passe <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password"
                    placeholder="Confirmez votre mot de passe" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="company_name">Nom de l'entreprise</label>
                <input type="text" id="company_name" name="company_name" placeholder="Votre dernier nom d'entreprise"
                    required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="country">Pays/Région <span class="required">*</span></label>
                <div class="custom-select-wrapper">
                    <select id="country" name="country" required>
                        <option value="" disabled selected>Sélectionnez votre pays/région</option>
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
                        <option value="PN">Pitcairn Islands</option>
                        <option value="PL">Poland</option>
                        <option value="PT">Portugal</option>
                        <option value="PR">Puerto Rico</option>
                        <option value="QA">Qatar</option>
                        <option value="RE">Réunion</option>
                        <option value="RO">Romania</option>
                        <option value="RU">Russia</option>
                        <option value="RW">Rwanda</option>
                        <option value="BL">Saint Barthélemy</option>
                        <option value="SH">Saint Helena</option>
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
                        <option value="SY">Syria</option>
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
                        <option value="US">United States</option>
                        <option value="UY">Uruguay</option>
                        <option value="UZ">Uzbekistan</option>
                        <option value="VU">Vanuatu</option>
                        <option value="VE">Venezuela</option>
                        <option value="VN">Vietnam</option>
                        <option value="WF">Wallis and Futuna</option>
                        <option value="EH">Western Sahara</option>
                        <option value="YE">Yemen</option>
                        <option value="ZM">Zambia</option>
                        <option value="ZW">Zimbabwe</option>
                    </select>
                    <ion-icon name="chevron-down-outline" class="custom-arrow"></ion-icon>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col">
                <label for="address">Adresse de la rue <span class="required">*</span></label>
                <input type="text" id="address" name="address" placeholder="Votre adresse postale" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="city">ville/quartier <span class="required">*</span></label>
                <input type="text" id="city" name="city" placeholder="Votre ville/quartier">
            </div>
            <div class="col">
                <label for="postal_code">Code Postal <span class="required">*</span></label>
                <input type="text" id="postal_code" name="postal_code" placeholder="Votre code postal" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="phone">Téléphone <span class="required">*</span></label>
                <input type="text" id="phone" name="phone" placeholder="Votre téléphone" required>
            </div>
        </div>
    </section>

    <!-- Qualifications -->
    <section class="section col qualifications">
        <h3 class="section-heading">Formation et qualifications</h3>

        <div class="row">
            <div class="col">
                <label for="degree">Dernier Degré <span class="required">*</span></label>
                <input type="text" id="degree" name="degree" placeholder="Votre dernier diplôme" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="institute">Institution d'obtention <span class="required">*</span></label>
                <input type="text" id="institute" name="institute" placeholder="Nom de l'établissement" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="graduation_year">Année d'obtention du diplôme <span class="required">*</span></label>
                <input type="number" id="graduation_year" name="graduation_year"
                    placeholder="Année d'obtention du diplôme" required min="2000" max="2024" step="1">
            </div>
        </div>

    </section>

    <!-- Subjects of Interest -->
    <section class="section col interested-subjects">
        <h3 class="section-heading">Sujets d'intérêt</h3>

        <div class="row">
            <div class="col">
                <div id="subjects_of_interest" class="checkbox-group">
                    <label class="row">
                        <input type="checkbox" name="subjects_of_interest[]" value="Classical Maths">
                        Mathématiques classiques
                    </label>
                    <label class="row">
                        <input type="checkbox" name="subjects_of_interest[]" value="Math & Programming"> Mathématiques
                        et programmation
                    </label>
                    <label class="row">
                        <input type="checkbox" name="subjects_of_interest[]" value="Math & AI"> Mathématiques et IA
                    </label>
                    <label class="row">
                        <input type="checkbox" name="subjects_of_interest[]" value="Math & Cybersecurity"> Mathématiques
                        et cybersécurité
                    </label>
                    <label class="row">
                        <input type="checkbox" name="subjects_of_interest[]" value="Maths & Ecology"> Mathématiques et
                        écologie
                    </label>
                    <label class="row">
                        <input type="checkbox" name="subjects_of_interest[]" value="Math & Financial Education">
                        Mathématiques et éducation financière
                    </label>
                </div>
            </div>
        </div>

    </section>

    <!-- Motivation -->
    <section class="section col motivation">
        <h3 class="section-heading">Veuillez décrire votre motivation pour rejoindre Joomana</h3>

        <div class="row">
            <div class="col">
                <textarea id="motivation_of_joining" name="motivation_of_joining"
                    placeholder="Décrivez votre motivation"></textarea>
                <p class="text">Décrivez votre motivation pour rejoindre Joomana Class en tant que
                    Professeur/Coach de Mathématiques (3 lignes maximum)</p>
            </div>
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
                    <label class="row"><input type="checkbox" name="available_days[]" value="Monday"> Lundi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Tuesday"> Mardi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Wednesday">
                        Mercredi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Thursday">
                        Jeudi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Friday"> Vendredi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Saturday">
                        Samedi</label>
                    <label class="row"><input type="checkbox" name="available_days[]" value="Sunday"> Dimanche</label>
                </div>
            </div>

            <div class="col time-slots">
                <label for="">Créneaux horaires</label>

                <!-- Monday Time Slots (Radio Buttons) -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="monday_timeslot" value="5pm-7pm"> 5pm-7pm</label>
                </div>

                <!-- Tuesday Time Slots (Radio Buttons) -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="tuesday_timeslot" value="5pm-7pm">
                        5pm-7pm</label>
                </div>

                <!-- Wednesday Time Slots (Radio Buttons) -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="8am-10am">
                        8am-10am</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="10am-12pm">
                        10am-12pm</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="12pm-2pm">
                        12pm-2pm</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="2pm-4pm">
                        2pm-4pm</label>
                    <label class="row"><input type="radio" name="wednesday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Thursday Time Slots (Radio Buttons) -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="thursday_timeslot" value="5pm-7pm">
                        5pm-7pm</label>
                </div>

                <!-- Friday Time Slots (Radio Buttons) -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="friday_timeslot" value="5pm-7pm"> 5pm-7pm</label>
                </div>

                <!-- Saturday Time Slots (Radio Buttons) -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="saturday_timeslot" value="8am-10am">
                        8am-10am</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="10am-12pm">
                        10am-12pm</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="12pm-2pm">
                        12pm-2pm</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="2pm-4pm">
                        2pm-4pm</label>
                    <label class="row"><input type="radio" name="saturday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>

                <!-- Sunday Time Slots (Radio Buttons) -->
                <div class="row radio-group">
                    <label class="row"><input type="radio" name="sunday_timeslot" value="8am-10am">
                        8am-10am</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="10am-12pm">
                        10am-12pm</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="12pm-2pm">
                        12pm-2pm</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="2pm-4pm"> 2pm-4pm</label>
                    <label class="row"><input type="radio" name="sunday_timeslot" value="4pm-6pm"> 4pm-6pm</label>
                </div>
            </div>
        </div>
    </section>

    <!-- Upload CV -->
    <section class="section col upload-cv">
        <h3 class="section-heading">Télécharger le CV mis à jour <span class="required">*</span></h3>

        <div class="row">
            <div class="col">
                <div class="upload-cv-button">
                    <label for="upload_cv" class="upload-cv-label">
                        Télécharger un CV<ion-icon name="document-attach-outline"></ion-icon>
                    </label>
                    <input type="file" id="upload_cv" name="upload_cv" accept=".pdf" class="upload-cv-input">
                </div>
                <p class="text">(PDF uniquement, max 2 Mo)</p>
                <p class="cv-file-name">Aucun fichier sélectionné</p>
            </div>
        </div>
    </section>

    <!-- Upload Other Document -->
    <section class="section col upload-document">
        <h3 class="section-heading">Télécharger une lettre de motivation, un certificat ou un autre document</h3>

        <div class="row">
            <div class="col">
                <div id="uploadDocContainer" class="col">
                    <!-- First document upload button -->
                    <div class="upload-group row">
                        <div class="upload-button-group">
                            <div class="upload-cv-button">
                                <label for="upload_doc1" class="upload-cv-label">
                                    Télécharger le document <ion-icon name="document-attach-outline"></ion-icon>
                                </label>
                                <input type="file" id="upload_doc1" name="upload_doc1" accept=".pdf"
                                    class="upload-cv-input">
                            </div>
                            <p class="text">(PDF uniquement, max 2 Mo)</p>
                            <p class="cv-file-name" id="file-name-1">Aucun fichier sélectionné</p>
                        </div>
                        <button type="button" id="addUploadButton" class="add-upload-button">
                            <ion-icon name="add-outline"></ion-icon>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- Upload Video -->
    <section class="section col upload-video">
        <h3 class="section-heading">Télécharger une vidéo de présentation</h3>

        <div class="row">
            <div class="col">
                <div class="upload-video-container col">
                    <div class="upload-video-box">
                        <label for="upload_video" class="upload-video-label">
                            Télécharger une vidéo <ion-icon name="videocam-outline"></ion-icon>
                        </label>
                        <input type="file" id="upload_video" name="upload_video" accept=".mp4,.mov"
                            class="upload-video-input">
                    </div>
                    <p class="text">(MP4, MOV uniquement, Max 20 Mo)</p>
                    <p class="video-file-name">Aucun fichier sélectionné</p>
                </div>
            </div>
        </div>

    </section>

    <div class="col declaration">
        <h3 class="declaration-heading">Déclaration</h3>
        <p class="declaration-text">
            Je certifie que les informations fournies dans ce formulaire sont exactes et complètes. Je comprends que
            toute information fausse
            ou trompeuse peut entraîner le rejet ou la résiliation de ma candidature si elle est découverte après mon
            embauche.
        </p>
        <input type="text" name="signature" id="signature" class="declaration-input signature" placeholder="Signature"
            required>
        <input type="date" name="signature_date" id="signature_date" class="declaration-input signature-date" required>
    </div>

    <button type="submit" class="submit-button" name="submit_teacher_registration">Registre</button>
</form>
<?php
        return ob_get_clean(); // Return the form's HTML
    }
    add_shortcode('teacher_registration_form', 'custom_teacher_registration_form');








    // teacher registration backend

    function handle_teacher_registration_form() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_teacher_registration'])) {
            global $wpdb;
    
            // Sanitize and validate form inputs
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $username = sanitize_user($_POST['username']);
            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $company_name = sanitize_text_field($_POST['company_name']);
            $country = sanitize_text_field($_POST['country']);
            $address = sanitize_text_field($_POST['address']);
            $city = sanitize_text_field($_POST['city']);
            $postal_code = sanitize_text_field($_POST['postal_code']);
            $phone = sanitize_text_field($_POST['phone']);
            $degree = sanitize_text_field($_POST['degree']);
            $institute = sanitize_text_field($_POST['institute']);
            $graduation_year = intval($_POST['graduation_year']);
            $subjects_of_interest = !empty($_POST['subjects_of_interest']) ? implode(',', array_map('sanitize_text_field', $_POST['subjects_of_interest'])) : '';
            $motivation_of_joining = sanitize_textarea_field($_POST['motivation_of_joining']);
            $available_days = !empty($_POST['available_days']) ? implode(',', array_map('sanitize_text_field', $_POST['available_days'])) : '';
            $monday_timeslot = isset($_POST['monday_timeslot']) ? sanitize_text_field($_POST['monday_timeslot']) : null;
            $tuesday_timeslot = isset($_POST['tuesday_timeslot']) ? sanitize_text_field($_POST['tuesday_timeslot']) : null;
            $wednesday_timeslot = isset($_POST['wednesday_timeslot']) ? sanitize_text_field($_POST['wednesday_timeslot']) : null;
            $thursday_timeslot = isset($_POST['thursday_timeslot']) ? sanitize_text_field($_POST['thursday_timeslot']) : null;
            $friday_timeslot = isset($_POST['friday_timeslot']) ? sanitize_text_field($_POST['friday_timeslot']) : null;
            $saturday_timeslot = isset($_POST['saturday_timeslot']) ? sanitize_text_field($_POST['saturday_timeslot']) : null;
            $sunday_timeslot = isset($_POST['sunday_timeslot']) ? sanitize_text_field($_POST['sunday_timeslot']) : null;        
            $signature = sanitize_text_field($_POST['signature']);
            $signature_date = sanitize_text_field($_POST['signature_date']);
    
            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['registration_error'] = 'All required fields must be filled.';
                return;
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
    
            // Generate a unique username if not provided
            if (empty($username)) {
                $base_username = sanitize_user(strtolower($first_name . '_' . $last_name));
                $username = $base_username;
                $suffix = 1;
    
                while (username_exists($username)) {
                    $username = $base_username . '_' . $suffix;
                    $suffix++;
                }
            }
    
            // Create the user in WordPress
            $user_id = wp_create_user($username, $password, $email);
            if (is_wp_error($user_id)) {
                $_SESSION['registration_error'] = $user_id->get_error_message();
                return;
            }
    
            // Assign 'teacher' role to the user
            $user = get_user_by('ID', $user_id);
            $user->set_role('teacher');
    
            // Verify if the user has the 'teacher' role
            if (in_array('teacher', $user->roles)) {
                // Save additional information in user meta
                update_user_meta($user_id, 'first_name', $first_name);
                update_user_meta($user_id, 'last_name', $last_name);

                // Handle file uploads
                $upload_dir = wp_upload_dir();
                $upload_path = $upload_dir['basedir'] . '/teacher/docs/';
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }

                $uploaded_files = [];
                $file_fields = [
                    'upload_cv',
                    'upload_doc1',
                    'upload_doc2',
                    'upload_doc3',
                    'upload_doc4',
                    'upload_doc5',
                    'upload_video'
                ];

                foreach ($file_fields as $file_key) {
                    if (!empty($_FILES[$file_key]['tmp_name'])) {
                        $file = $_FILES[$file_key];
                        $file_name = $username . '-' . sanitize_file_name($file['name']);
                        $file_path = $upload_path . $file_name;
                    
                        if (move_uploaded_file($file['tmp_name'], $file_path)) {
                            $uploaded_files[$file_key] = $upload_dir['baseurl'] . '/teacher/docs/' . $file_name;
                        } else {
                            $_SESSION['registration_error'] = 'Failed to upload file: ' . $file['tmp_name'] . ' to ' . $file_path;
                        }
                    }
                }

                // Save file paths to user meta
                // foreach ($uploaded_files as $key => $file_url) {
                //     update_user_meta($user_id, $key, $file_url);
                // }

                // Save data to the custom 'teachers' table
                $table_name = $wpdb->prefix . 'teachers';
                $wpdb->insert($table_name, [
                    'id' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'company_name' => $company_name,
                    'country' => $country,
                    'address' => $address,
                    'city' => $city,
                    'postal_code' => $postal_code,
                    'phone' => $phone,
                    'degree' => $degree,
                    'institute' => $institute,
                    'graduation_year' => $graduation_year,
                    'subjects_of_interest' => $subjects_of_interest,
                    'motivation_of_joining' => $motivation_of_joining,
                    'available_days' => $available_days,
                    'monday_timeslot' => $monday_timeslot,
                    'tuesday_timeslot' => $tuesday_timeslot,
                    'wednesday_timeslot' => $wednesday_timeslot,
                    'thursday_timeslot' => $thursday_timeslot,
                    'friday_timeslot' => $friday_timeslot,
                    'saturday_timeslot' => $saturday_timeslot,
                    'sunday_timeslot' => $sunday_timeslot,
                    'upload_cv' => $uploaded_files['upload_cv'] ?? null,
                    'upload_doc1' => $uploaded_files['upload_doc1'] ?? null,
                    'upload_doc2' => $uploaded_files['upload_doc2'] ?? null,
                    'upload_doc3' => $uploaded_files['upload_doc3'] ?? null,
                    'upload_doc4' => $uploaded_files['upload_doc4'] ?? null,
                    'upload_doc5' => $uploaded_files['upload_doc5'] ?? null,
                    'upload_video' => $uploaded_files['upload_video'] ?? null,
                    'signature' => $signature,
                    'signature_date' => $signature_date,
                    'created_at' => current_time('mysql'),
                ]);

                $_SESSION['registration_success'] = 'Registration successful. Welcome to Joomana!';

                // Redirect to the current page to prevent form resubmission
                wp_safe_redirect($_SERVER['REQUEST_URI']);
                exit;
            } else {
                $_SESSION['registration_error'] = 'Failed to assign the teacher role. Registration aborted.';
            }
        }
    }
    add_action('init', 'handle_teacher_registration_form');
    
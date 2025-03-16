<?php

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
<form class="teacher-registration-form" method="post" enctype="multipart/form-data" action="">

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
                <label for="civility">Ajouter civilité <span class="required">*</span></label>
                <div class="custom-select-wrapper">
                    <select id="civility" name="civility" required>
                        <option value="" disabled selected>M. ou Mme</option>
                        <option value="Mr">M</option>
                        <option value="Mrs">Mme</option>
                    </select>
                    <ion-icon name="chevron-down-outline" class="custom-arrow"></ion-icon>
                </div>
            </div>
            <div class="col">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" placeholder="Nom d'utilisateur">
            </div>
        </div>

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
                <label for="how_found">Comment as-tu connu Joomaths ?</label>
                <div class="custom-select-wrapper">
                    <select id="how_found" name="how_found">
                        <option value="" disabled selected>Sélectionnez comment vous avez trouvé</option>
                        <option value="Internet">Internet</option>
                        <option value="Personal Network">Réseau personnel</option>
                        <option value="TikTok">TikTok</option>
                        <option value="Facebook">Facebook</option>
                        <option value="LinkedIn">LinkedIn</option>
                        <option value="Other">Autre</option>
                    </select>
                    <ion-icon name="chevron-down-outline" class="custom-arrow"></ion-icon>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="country">Pays/Région <span class="required">*</span></label>
                <div class="custom-select-wrapper">
                    <select id="country" name="country" required>
                        <option value="" disabled selected>Sélectionnez votre pays/région</option>
                        <option value="Afghanistan">Afghanistan</option>
                        <option value="Albania">Albania</option>
                        <option value="Algeria">Algeria</option>
                        <option value="American Samoa">American Samoa</option>
                        <option value="Andorra">Andorra</option>
                        <option value="Angola">Angola</option>
                        <option value="Anguilla">Anguilla</option>
                        <option value="Antarctica">Antarctica</option>
                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                        <option value="Argentina">Argentina</option>
                        <option value="Armenia">Armenia</option>
                        <option value="Aruba">Aruba</option>
                        <option value="Australia">Australia</option>
                        <option value="Austria">Austria</option>
                        <option value="Azerbaijan">Azerbaijan</option>
                        <option value="Bahamas">Bahamas</option>
                        <option value="Bahrain">Bahrain</option>
                        <option value="Bangladesh">Bangladesh</option>
                        <option value="Barbados">Barbados</option>
                        <option value="Belarus">Belarus</option>
                        <option value="Belgium">Belgium</option>
                        <option value="Belize">Belize</option>
                        <option value="Benin">Benin</option>
                        <option value="Bermuda">Bermuda</option>
                        <option value="Bhutan">Bhutan</option>
                        <option value="Bolivia">Bolivia</option>
                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                        <option value="Botswana">Botswana</option>
                        <option value="Brazil">Brazil</option>
                        <option value="Brunei Darussalam">Brunei Darussalam</option>
                        <option value="Bulgaria">Bulgaria</option>
                        <option value="Burkina Faso">Burkina Faso</option>
                        <option value="Burundi">Burundi</option>
                        <option value="Cambodia">Cambodia</option>
                        <option value="Cameroon">Cameroon</option>
                        <option value="Canada">Canada</option>
                        <option value="Cape Verde">Cape Verde</option>
                        <option value="Cayman Islands">Cayman Islands</option>
                        <option value="Central African Republic">Central African Republic</option>
                        <option value="Chad">Chad</option>
                        <option value="Chile">Chile</option>
                        <option value="China">China</option>
                        <option value="Colombia">Colombia</option>
                        <option value="Comoros">Comoros</option>
                        <option value="Congo">Congo</option>
                        <option value="Congo (Democratic Republic)">Congo (Democratic Republic)</option>
                        <option value="Costa Rica">Costa Rica</option>
                        <option value="Croatia">Croatia</option>
                        <option value="Cuba">Cuba</option>
                        <option value="Cyprus">Cyprus</option>
                        <option value="Czech Republic">Czech Republic</option>
                        <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                        <option value="Denmark">Denmark</option>
                        <option value="Djibouti">Djibouti</option>
                        <option value="Dominica">Dominica</option>
                        <option value="Dominican Republic">Dominican Republic</option>
                        <option value="Ecuador">Ecuador</option>
                        <option value="Egypt">Egypt</option>
                        <option value="El Salvador">El Salvador</option>
                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                        <option value="Eritrea">Eritrea</option>
                        <option value="Estonia">Estonia</option>
                        <option value="Eswatini">Eswatini</option>
                        <option value="Ethiopia">Ethiopia</option>
                        <option value="Falkland Islands">Falkland Islands</option>
                        <option value="Faroe Islands">Faroe Islands</option>
                        <option value="Fiji">Fiji</option>
                        <option value="Finland">Finland</option>
                        <option value="France">France</option>
                        <option value="French Guiana">French Guiana</option>
                        <option value="French Polynesia">French Polynesia</option>
                        <option value="Gabon">Gabon</option>
                        <option value="Gambia">Gambia</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Germany">Germany</option>
                        <option value="Ghana">Ghana</option>
                        <option value="Gibraltar">Gibraltar</option>
                        <option value="Greece">Greece</option>
                        <option value="Greenland">Greenland</option>
                        <option value="Grenada">Grenada</option>
                        <option value="Guam">Guam</option>
                        <option value="Guatemala">Guatemala</option>
                        <option value="Guinea">Guinea</option>
                        <option value="Guinea-Bissau">Guinea-Bissau</option>
                        <option value="Guyana">Guyana</option>
                        <option value="Haiti">Haiti</option>
                        <option value="Honduras">Honduras</option>
                        <option value="Hong Kong">Hong Kong</option>
                        <option value="Hungary">Hungary</option>
                        <option value="Iceland">Iceland</option>
                        <option value="India">India</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Iran">Iran</option>
                        <option value="Iraq">Iraq</option>
                        <option value="Ireland">Ireland</option>
                        <option value="Israel">Israel</option>
                        <option value="Italy">Italy</option>
                        <option value="Jamaica">Jamaica</option>
                        <option value="Japan">Japan</option>
                        <option value="Jordan">Jordan</option>
                        <option value="Kazakhstan">Kazakhstan</option>
                        <option value="Kenya">Kenya</option>
                        <option value="Kiribati">Kiribati</option>
                        <option value="Korea (South)">Korea (South)</option>
                        <option value="Kuwait">Kuwait</option>
                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                        <option value="Laos">Laos</option>
                        <option value="Latvia">Latvia</option>
                        <option value="Lebanon">Lebanon</option>
                        <option value="Lesotho">Lesotho</option>
                        <option value="Liberia">Liberia</option>
                        <option value="Libya">Libya</option>
                        <option value="Liechtenstein">Liechtenstein</option>
                        <option value="Lithuania">Lithuania</option>
                        <option value="Luxembourg">Luxembourg</option>
                        <option value="Macau">Macau</option>
                        <option value="Madagascar">Madagascar</option>
                        <option value="Malawi">Malawi</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="Maldives">Maldives</option>
                        <option value="Mali">Mali</option>
                        <option value="Malta">Malta</option>
                        <option value="Marshall Islands">Marshall Islands</option>
                        <option value="Martinique">Martinique</option>
                        <option value="Mauritania">Mauritania</option>
                        <option value="Mauritius">Mauritius</option>
                        <option value="Mayotte">Mayotte</option>
                        <option value="Mexico">Mexico</option>
                        <option value="Micronesia">Micronesia</option>
                        <option value="Moldova">Moldova</option>
                        <option value="Monaco">Monaco</option>
                        <option value="Mongolia">Mongolia</option>
                        <option value="Montenegro">Montenegro</option>
                        <option value="Montserrat">Montserrat</option>
                        <option value="Morocco">Morocco</option>
                        <option value="Mozambique">Mozambique</option>
                        <option value="Myanmar">Myanmar</option>
                        <option value="Namibia">Namibia</option>
                        <option value="Nauru">Nauru</option>
                        <option value="Nepal">Nepal</option>
                        <option value="Netherlands">Netherlands</option>
                        <option value="New Caledonia">New Caledonia</option>
                        <option value="New Zealand">New Zealand</option>
                        <option value="Nicaragua">Nicaragua</option>
                        <option value="Niger">Niger</option>
                        <option value="Nigeria">Nigeria</option>
                        <option value="Niue">Niue</option>
                        <option value="Norfolk Island">Norfolk Island</option>
                        <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                        <option value="Norway">Norway</option>
                        <option value="Oman">Oman</option>
                        <option value="Pakistan">Pakistan</option>
                        <option value="Palau">Palau</option>
                        <option value="Panama">Panama</option>
                        <option value="Papua New Guinea">Papua New Guinea</option>
                        <option value="Paraguay">Paraguay</option>
                        <option value="Peru">Peru</option>
                        <option value="Philippines">Philippines</option>
                        <option value="Pitcairn Islands">Pitcairn Islands</option>
                        <option value="Poland">Poland</option>
                        <option value="Portugal">Portugal</option>
                        <option value="Puerto Rico">Puerto Rico</option>
                        <option value="Qatar">Qatar</option>
                        <option value="Réunion">Réunion</option>
                        <option value="Romania">Romania</option>
                        <option value="Russia">Russia</option>
                        <option value="Rwanda">Rwanda</option>
                        <option value="Saint Barthélemy">Saint Barthélemy</option>
                        <option value="Saint Helena">Saint Helena</option>
                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                        <option value="Saint Lucia">Saint Lucia</option>
                        <option value="Saint Martin">Saint Martin</option>
                        <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                        <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                        <option value="Samoa">Samoa</option>
                        <option value="San Marino">San Marino</option>
                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                        <option value="Saudi Arabia">Saudi Arabia</option>
                        <option value="Senegal">Senegal</option>
                        <option value="Serbia">Serbia</option>
                        <option value="Seychelles">Seychelles</option>
                        <option value="Sierra Leone">Sierra Leone</option>
                        <option value="Singapore">Singapore</option>
                        <option value="Sint Maarten">Sint Maarten</option>
                        <option value="Slovakia">Slovakia</option>
                        <option value="Slovenia">Slovenia</option>
                        <option value="Solomon Islands">Solomon Islands</option>
                        <option value="Somalia">Somalia</option>
                        <option value="South Africa">South Africa</option>
                        <option value="South Georgia and the South Sandwich Islands">South Georgia and the South
                            Sandwich Islands</option>
                        <option value="Spain">Spain</option>
                        <option value="Sri Lanka">Sri Lanka</option>
                        <option value="Sudan">Sudan</option>
                        <option value="Suriname">Suriname</option>
                        <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                        <option value="Sweden">Sweden</option>
                        <option value="Switzerland">Switzerland</option>
                        <option value="Syria">Syria</option>
                        <option value="Taiwan">Taiwan</option>
                        <option value="Tajikistan">Tajikistan</option>
                        <option value="Tanzania">Tanzania</option>
                        <option value="Thailand">Thailand</option>
                        <option value="Timor-Leste">Timor-Leste</option>
                        <option value="Togo">Togo</option>
                        <option value="Tokelau">Tokelau</option>
                        <option value="Tonga">Tonga</option>
                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                        <option value="Tunisia">Tunisia</option>
                        <option value="Turkey">Turkey</option>
                        <option value="Turkmenistan">Turkmenistan</option>
                        <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                        <option value="Tuvalu">Tuvalu</option>
                        <option value="Uganda">Uganda</option>
                        <option value="Ukraine">Ukraine</option>
                        <option value="United Arab Emirates">United Arab Emirates</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="United States">United States</option>
                        <option value="Uruguay">Uruguay</option>
                        <option value="Uzbekistan">Uzbekistan</option>
                        <option value="Vanuatu">Vanuatu</option>
                        <option value="Venezuela">Venezuela</option>
                        <option value="Vietnam">Vietnam</option>
                        <option value="Wallis and Futuna">Wallis and Futuna</option>
                        <option value="Western Sahara">Western Sahara</option>
                        <option value="Yemen">Yemen</option>
                        <option value="Zambia">Zambia</option>
                        <option value="Zimbabwe">Zimbabwe</option>
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
                <label for="degree">Derniers diplômes <span class="required">*</span></label>
                <input type="text" id="degree" name="degree" placeholder="Votre derniers diplômes" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label for="institute">Écoles / Universités (Institution d'obtention ) <span
                        class="required">*</span></label>
                <input type="text" id="institute" name="institute" placeholder="Nom de Écoles / Universités" required>
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
                    <?php
                        global $wpdb;
                        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");
                        if ($categories) {
                            foreach ($categories as $category) {
                                echo '<label class="row"><input type="checkbox" name="subjects_of_interest[]" value="' . esc_attr($category->category) . '">' . esc_html($category->category) . '</label>';
                            }
                        } else {
                            echo '<option disabled>Aucun sujet trouvé</option>';
                        }
                    ?>
                </div>
            </div>
        </div>

    </section>

    <!-- Motivation -->
    <section class="section col motivation">
        <h3 class="section-heading">Veuillez décrire votre motivation pour rejoindre Joomaths</h3>

        <div class="row">
            <div class="col">
                <textarea id="motivation_of_joining" name="motivation_of_joining"
                    placeholder="Décrivez votre motivation"></textarea>
                <p class="text">Décrivez votre motivation pour rejoindre Joomaths en tant que Prof chez Joomaths (3
                    lignes maximum)</p>
            </div>
        </div>
    </section>

    <!-- Weekly Availability -->
    <section class="section col weekly-availability">
        <h3 class="section-heading">Quelles sont tes disponibilités pour donner des cours ?</h3>

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

                <!-- Monday Time Slots -->
                <div class="row checkbox-group">
                    <label class="row"><input type="checkbox" name="monday_timeslot[]" value="5pm-6:30pm">
                        5pm-6:30pm</label>
                    <label class="row"><input type="checkbox" name="monday_timeslot[]" value="7pm-8:30pm">
                        7pm-8:30pm</label>
                </div>

                <!-- Tuesday Time Slots -->
                <div class="row checkbox-group">
                    <label class="row"><input type="checkbox" name="tuesday_timeslot[]" value="5pm-6:30pm">
                        5pm-6:30pm</label>
                    <label class="row"><input type="checkbox" name="tuesday_timeslot[]" value="7pm-8:30pm">
                        7pm-8:30pm</label>
                </div>

                <!-- Wednesday Time Slots -->
                <div class="row checkbox-group">
                    <label class="row"><input type="checkbox" name="wednesday_timeslot[]" value="2pm-3:30pm">
                        2pm-3:30pm</label>
                    <label class="row"><input type="checkbox" name="wednesday_timeslot[]" value="4pm-5:30pm">
                        4pm-5:30pm</label>
                </div>

                <!-- Thursday Time Slots -->
                <div class="row checkbox-group">
                    <label class="row"><input type="checkbox" name="thursday_timeslot[]" value="5pm-6:30pm">
                        5pm-6:30pm</label>
                    <label class="row"><input type="checkbox" name="thursday_timeslot[]" value="7pm-8:30pm">
                        7pm-8:30pm</label>
                </div>

                <!-- Friday Time Slots -->
                <div class="row checkbox-group">
                    <label class="row"><input type="checkbox" name="friday_timeslot[]" value="5pm-6:30pm">
                        5pm-6:30pm</label>
                    <label class="row"><input type="checkbox" name="friday_timeslot[]" value="7pm-8:30pm">
                        7pm-8:30pm</label>
                </div>

                <!-- Saturday Time Slots -->
                <div class="row checkbox-group">
                    <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="9am-10:30am">
                        9am-10:30am</label>
                    <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="11am-12:30pm">
                        11am-12:30pm</label>
                    <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="2pm-3:30pm">
                        2pm-3:30pm</label>
                    <label class="row"><input type="checkbox" name="saturday_timeslot[]" value="4pm-5:30pm">
                        4pm-5:30pm</label>
                </div>

                <!-- Sunday Time Slots -->
                <div class="row checkbox-group">
                    <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="2pm-3:30pm">
                        2pm-3:30pm</label>
                    <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="4pm-5:30pm">
                        4pm-5:30pm</label>
                    <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="5:18pm-6:30pm">
                        5:18pm-6:30pm</label>
                    <label class="row"><input type="checkbox" name="sunday_timeslot[]" value="7pm-8:30pm">
                        7pm-8:30pm</label>
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
                    <p class="text">(Taille de la vidéo 3 minutes max)</p>
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

    <button type="submit" class="submit-button" name="submit_teacher_registration">Valider</button>
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
            $civility = sanitize_text_field($_POST['civility']);
            $username = sanitize_user($_POST['username']);
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $date_of_birth = sanitize_text_field($_POST['date_of_birth']);
            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $company_name = sanitize_text_field($_POST['company_name']);
            $how_found = sanitize_text_field($_POST['how_found']);
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
            $monday_timeslot = !empty($_POST['monday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['monday_timeslot'])) : '';
            $tuesday_timeslot = !empty($_POST['tuesday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['tuesday_timeslot'])) : '';
            $wednesday_timeslot = !empty($_POST['wednesday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['wednesday_timeslot'])) : '';
            $thursday_timeslot = !empty($_POST['thursday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['thursday_timeslot'])) : '';
            $friday_timeslot = !empty($_POST['friday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['friday_timeslot'])) : '';
            $saturday_timeslot = !empty($_POST['saturday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['saturday_timeslot'])) : '';
            $sunday_timeslot = !empty($_POST['sunday_timeslot']) ? implode(',', array_map('sanitize_text_field', $_POST['sunday_timeslot'])) : '';
            $signature = sanitize_text_field($_POST['signature']);
            $signature_date = sanitize_text_field($_POST['signature_date']);
    
            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['registration_error'] = 'Tous les champs obligatoires doivent être remplis.';
                return;
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

                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                foreach ($file_fields as $file_key) {
                    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                        $file_type = $_FILES[$file_key]['type'];
                        $file_size = $_FILES[$file_key]['size'];
                        
                        // Define allowed types and size limits for each field
                        $allowed_types = [
                            'upload_cv' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                            'upload_doc1' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                            'upload_doc2' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                            'upload_doc3' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                            'upload_doc4' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                            'upload_doc5' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                            'upload_video' => ['video/mp4', 'video/quicktime']
                        ];
                        $size_limit = [
                            'upload_cv' => 5 * 1024 * 1024, // 5 MB
                            'upload_doc1' => 5 * 1024 * 1024,
                            'upload_doc2' => 5 * 1024 * 1024,
                            'upload_doc3' => 5 * 1024 * 1024,
                            'upload_doc4' => 5 * 1024 * 1024,
                            'upload_doc5' => 5 * 1024 * 1024,
                            'upload_video' => 50 * 1024 * 1024 // 50 MB
                        ];
                
                        if (in_array($file_type, $allowed_types[$file_key])) {
                            if ($file_size <= $size_limit[$file_key]) {
                                $upload_overrides = ['test_form' => false];
                                $uploaded_file = wp_handle_upload($_FILES[$file_key], $upload_overrides);
                
                                if ($uploaded_file && !isset($uploaded_file['error'])) {
                                    // Insert the file into the WordPress Media Library
                                    $file_path = $uploaded_file['file'];
                                    $attachment = [
                                        'guid'           => $uploaded_file['url'],
                                        'post_mime_type' => $file_type,
                                        'post_title'     => sanitize_file_name($_FILES[$file_key]['name']),
                                        'post_content'   => '',
                                        'post_status'    => 'inherit',
                                    ];
                                    $attachment_id = wp_insert_attachment($attachment, $file_path);
                
                                    if (!is_wp_error($attachment_id)) {
                                        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                                        wp_update_attachment_metadata($attachment_id, $attach_data);
                                        $uploaded_files[$file_key] = wp_get_attachment_url($attachment_id);
                                    } else {
                                        $_SESSION['registration_error'] = "Erreur lors de l'insertion du fichier dans la médiathèque.";
                                        return;
                                    }
                                } else {
                                    $_SESSION['registration_error'] = 'Erreur de téléchargement de fichier: ' . $uploaded_file['error'];
                                    return;
                                }
                            } else {
                                $_SESSION['registration_error'] = 'La taille du fichier dépasse la limite autorisée pour ' . $file_key . '.';
                                return;
                            }
                        } else {
                            $_SESSION['registration_error'] = 'Type de fichier non valide pour ' . $file_key . '.';
                            return;
                        }
                    }
                }

                // Save data to the custom 'teachers' table
                $table_name = $wpdb->prefix . 'teachers';
                $wpdb->insert($table_name, [
                    'id' => $user_id,
                    'civility' => $civility,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'date_of_birth' => $date_of_birth,
                    'email' => $email,
                    'company_name' => $company_name,
                    'how_found' => $how_found,
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

                $_SESSION['registration_success'] = 'Registration successful. Welcome to Joomaths!';

                // Send email to the student
                $to = $email;
                $subject = 'Bienvenue sur Joomaths !';
                $message = "Bonjour $first_name,\n\nMerci de votre inscription. Votre compte a bien été créé.\n\n";
                $message .= "Nom d'utilisateur: $username\n";
                $message .= "Mot de passe: (le mot de passe que vous avez saisi lors de l'inscription)\n\n";
                $message .= "Nous avons hâte de vous voir sur notre plateforme !\n\nCordialement,\nL'équipe Joomaths";

                // Send the email
                wp_mail($to, $subject, $message);

                // Redirect to the current page to prevent form resubmission
                wp_safe_redirect($_SERVER['REQUEST_URI']);
                exit;
            } else {
                $_SESSION['registration_error'] = "Échec de l'attribution du rôle d'enseignant. Inscription interrompue.";
            }
        }
    }
    add_action('init', 'handle_teacher_registration_form');
    
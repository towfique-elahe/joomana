<?php

// Redirect wp login page to custom login page

add_action('login_enqueue_scripts', function () {
    if (basename($_SERVER['PHP_SELF']) === 'wp-login.php') {
        wp_redirect(home_url('/login/'));
        exit;
    }
});




// Login Form

// shortcode [custom_login_form]

function custom_login_form() {
    ob_start();
        
        // Retrieve CSRF token
        $csrf_token = wp_create_nonce('custom_login_form');
    ?>
<form id="loginForm" class="login-form" action="<?php echo esc_url(home_url('wp-login.php')); ?>" method="post">
    <section class="section col">
        <div class="row">
            <div class="col">
                <label for="user_login">Nom D'utilisateur Ou E-mail</label>
                <input type="text" name="log" id="user_login"
                    placeholder="Entrez votre nom d'utilisateur ou votre email" required>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="user_pass">Mot De Passe</label>
                <input type="password" name="pwd" id="user_pass" placeholder="Entrez votre mot de passe" required>
                <div class="forgot-password">
                    <a href="#">Mot de passe oublié ?</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <input class="submit-button" type="submit" value="SE CONNECTER">
            </div>
        </div>
    </section>
</form>
<?php
    return ob_get_clean();
}
add_shortcode('custom_login_form', 'custom_login_form');





// Redirect users based on their roles after login

function role_based_redirect($redirect_to, $request, $user) {
    // Ensure user is an object and has roles
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return home_url('/wp-admin/');
        } elseif (in_array('admin', $user->roles)) {
            return home_url('/admin/dashboard/');
        } elseif (in_array('teacher', $user->roles)) {
            return home_url('/teacher/dashboard/');
        } elseif (in_array('parent', $user->roles)) {
            return home_url('/parent/dashboard/');
        } elseif (in_array('student', $user->roles)) {
            return home_url('/student/dashboard/');
        }
    }

    // Default redirect location if no role matches
    return home_url('/login/');
}
add_filter('login_redirect', 'role_based_redirect', 10, 3);
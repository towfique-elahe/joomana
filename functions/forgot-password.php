<?php

// Forgot Password Form

// shortcode [custom_forgot_password_form]

function custom_forgot_password_form() {
    ob_start();
    ?>
<div class="custom-forgot-password-form">
    <form action="<?php echo esc_url(site_url('wp-login.php?action=lostpassword', 'login_post')); ?>" method="post">
        <div>
            <label for="user_login">Enter Username or Email Address</label>
            <input type="text" name="user_login" id="user_login" placeholder="Enter your username or email" required>
        </div>
        <div>
            <input type="submit" name="wp-submit" value="Reset Password">
            <input type="hidden" name="redirect_to"
                value="<?php echo esc_url(home_url('/password-reset-success/')); ?>">
        </div>
    </form>
</div>
<?php
    return ob_get_clean();
}
add_shortcode('custom_forgot_password_form', 'custom_forgot_password_form');
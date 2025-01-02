<?php

/* Template Name: Parent | Header */

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
            if (!empty($pageTitle)) {
                echo esc_html($pageTitle) . ' | ' . get_bloginfo('name') . " Portail Des Parents";
            } else {
                echo get_bloginfo('name') . " Portail Des Parents";
            }
        ?>
    </title>
    <?php wp_head(); ?>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        color: var(--text-color);
        font-family: var(--text-font-family);
    }

    html {
        font-size: 14px;
    }
    </style>
</head>

<body id="portal">
    <div class="container">
        <header class="header">
            <div class="logo">
                <?php
    if (has_custom_logo()) {
        // Display the custom logo
        the_custom_logo();
    } else {
        // Fallback: Display the site title
        echo '<span>' . get_bloginfo('name') . '</span>';
    }
    ?>
            </div>
            <div class="user">
                <div class="greeting">
                    <?php
                    if (is_user_logged_in()) {
                        $current_user = wp_get_current_user();
                        $first_name = $current_user->user_firstname;
                        $last_name = $current_user->user_lastname;
                        $full_name = trim($first_name . ' ' . $last_name);
                        echo 'Bonjour, <span class="user-name">' . esc_html($full_name) . '!</span>';
                    } else {
                        echo 'Bonjour, Invité!';
                    }
                    ?>
                </div>
                <a href="<?php echo home_url('/parent/settings/'); ?>" class="user-image">
                    <img src="<?php echo get_template_directory_uri() . '/assets/image/user.png'; ?>" alt="">
                </a>
            </div>
        </header>
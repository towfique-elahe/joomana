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
    <?php if (has_site_icon()): ?>
    <link rel="icon" href="<?php echo esc_url(get_site_icon_url()); ?>" type="image/png">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri() . '/assets/css/root.css'; ?>">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri() . '/assets/css/parent-portal.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        font-size: 14px;
    }

    @media only screen and (max-width: 786px) {
        html {
            font-size: 13px;
        }
    }

    @media only screen and (max-width: 500px) {
        html {
            font-size: 12px;
        }
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
                        } else {
                            $full_name = 'Invité';
                        }

                        // Get the current hour
                        $current_hour = (int) date('H');

                        // Determine the greeting based on the time of day
                        if ($current_hour >= 5 && $current_hour < 12) {
                            $greeting = 'Bonjour';
                        } elseif ($current_hour >= 12 && $current_hour < 18) {
                            $greeting = 'Bon après-midi';
                        } else {
                            $greeting = 'Bonsoir';
                        }

                        echo $greeting . ', <span class="user-name">' . esc_html($full_name) . '!</span>';
                    ?>
                </div>
                <?php
                    // Fetching the current user data
                    $user_id = get_current_user_id();
                    $table_name = $wpdb->prefix . 'parents';
                    $user_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $user_id));
                    $image = $user_data->image;
                ?>
                <a href="<?php echo home_url('/student/settings/'); ?>" class="user-image">
                    <img src="<?php echo esc_url( $image ? $image : get_template_directory_uri() . '/assets/image/user.png' ); ?>"
                        alt="">
                </a>
            </div>
        </header>
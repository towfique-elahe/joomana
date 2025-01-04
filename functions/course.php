<?php

// Course Databases

// Course Categories Table
function create_course_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $course_categories = $wpdb->prefix . 'course_categories';
    $course_topics = $wpdb->prefix . 'course_topics';
    $course_grades = $wpdb->prefix . 'course_grades';
    $course_levels = $wpdb->prefix . 'course_levels';
    $courses = $wpdb->prefix . 'courses';

    $sql1 = "CREATE TABLE $course_categories (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        category VARCHAR(255) NOT NULL,
        course_count INT(11) NOT NULL DEFAULT 0,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";    

    $sql2 = "CREATE TABLE $course_topics (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        topic VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        grade VARCHAR(255) NOT NULL,
        level VARCHAR(255) NOT NULL,
        course_count INT(11) NOT NULL DEFAULT 0,
        image VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql3 = "CREATE TABLE $course_grades (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        grade VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql4 = "CREATE TABLE $course_levels (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        level VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql5 = "CREATE TABLE $courses (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        category VARCHAR(100) NOT NULL,
        topic VARCHAR(100) NOT NULL,
        grade VARCHAR(50) NOT NULL,
        level VARCHAR(50) NOT NULL,
        max_student_groups INT(11) NOT NULL DEFAULT 25,
        max_students_per_group INT(11) NOT NULL DEFAULT 6,
        max_teachers INT(11) NOT NULL DEFAULT 25,
        duration INT(11) NOT NULL DEFAULT 2,
        assigned_teachers TEXT DEFAULT NULL,
        status ENUM('available', 'unavailable', 'pending', 'completed') NOT NULL DEFAULT 'available',
        start_date VARCHAR(20) NOT NULL,
        time_slot VARCHAR(30) NOT NULL,
        required_credit DECIMAL(10, 2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
    dbDelta($sql4);
    dbDelta($sql5);
}
add_action('after_setup_theme', 'create_course_tables');





// course categories section

// shortcode [course_categories]

function course_categories_section() {
    ob_start();
    ?>

<!-- Course Categories Section -->
<div class="course-categories">
    <div class="course-cards">
        <?php
            global $wpdb;
            $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");
            $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
        ?>
        <?php if (!empty($categories)) : ?>
        <?php 
            foreach ($categories as $category) : 
                $course_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}courses WHERE category = %s", 
                    $category->category
                ));
        ?>
        <div class="course-card">
            <img src="<?php echo esc_html($category->image ? $category->image : $default_image); ?>"
                alt="<?php echo esc_attr($category->category); ?>">
            <h4><?php echo esc_html($category->category); ?></h4>
            <p class="course-count"><?php echo esc_html($course_count); ?> Cours</p>
            <a class="course-button"
                href="<?php echo site_url('/available-courses/?category=' . urlencode($category->category)); ?>">Voir
                les cours</a>
        </div>
        <?php endforeach; ?>
        <?php else : ?>
        <div class="course-card">
            <p>No category found</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php
    return ob_get_clean();
}

add_shortcode('course_categories', 'course_categories_section');




// Course Filter Page

// shortcode [course_filter_page]

function course_filter_page_shortcode() {
ob_start();
?>
<!-- Filter Section -->
<div class="course-category-filtering row">
    <div class="sidebar col">
        <!-- category filter -->
        <div class="filter-section">
            <h3 class="filter-section-heading">Filter by Categories</h3>
            <div>
                <?php
                    global $wpdb; // Access the global $wpdb object for database queries

                    // Query the custom 'course_categories' table
                    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");

                    // Check if categories are available
                    if ($categories) {
                        foreach ($categories as $category) {
                            echo '<label><input type="checkbox" class="filter" data-filter="category" value="' . esc_attr($category->category) . '">' . esc_html($category->category) . '</label>';
                        }
                    } else {
                        echo '<option disabled>No categories found</option>';
                    }
                ?>
            </div>
        </div>

        <!-- grade filter -->
        <div class="filter-section">
            <h3 class="filter-section-heading">Filtrer par Grade</h3>
            <div>
                <?php
                    global $wpdb; // Access the global $wpdb object for database queries

                    // Query the custom 'course_grades' table
                    $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");

                    // Check if grades are available
                    if ($grades) {
                        foreach ($grades as $grade) {
                            echo '<label><input type="checkbox" class="filter" data-filter="grade" value="' . esc_attr($grade->grade) . '">' . esc_html($grade->grade) . '</label>';
                        }
                    } else {
                        echo '<option disabled>No grade found</option>';
                    }
                ?>
            </div>
        </div>

        <!-- level filter -->
        <div class="filter-section">
            <h3 class="filter-section-heading">Filtrer par Niveau</h3>
            <div>
                <?php
                    global $wpdb; // Access the global $wpdb object for database queries

                    // Query the custom 'course_levels' table
                    $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");

                    // Check if levels are available
                    if ($levels) {
                        foreach ($levels as $level) {
                            echo '<label><input type="checkbox" class="filter" data-filter="level" value="' . esc_attr($level->level) . '">' . esc_html($level->level) . '</label>';
                        }
                    } else {
                        echo '<option disabled>No level found</option>';
                    }
                ?>
            </div>
        </div>
    </div>

    <div class="course-cards">
        <?php
        global $wpdb; // Access the global $wpdb object for database queries

        // Query the custom 'course_topics' table
        $topics = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_topics");
        $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
    ?>
        <?php if (!empty($topics)) : ?>
        <?php foreach ($topics as $topic) : 
        // Query to get the course count for the current topic
        $course_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}courses WHERE topic = %s", 
            $topic->topic
        ));
    ?>
        <div class="course-card" data-category="<?php echo esc_attr($topic->category); ?>"
            data-grade="<?php echo esc_attr($topic->grade); ?>" data-level="<?php echo esc_attr($topic->level); ?>">
            <img src="<?php echo esc_html($topic->image ? $topic->image : $default_image); ?>"
                alt="<?php echo esc_attr($topic->topic); ?>">
            <h4><?php echo esc_html($topic->topic); ?></h4>
            <p class="course-count"><?php echo esc_html($course_count); ?> Cours</p>
            <a class="course-button"
                href="<?php echo site_url('/available-courses/?topic=' . urldecode($topic->topic)); ?>">Voir
                les
                cours</a>
        </div>
        <?php endforeach; ?>
        <?php else : ?>
        <div class="course-card">
            <p>No topic found</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const filters = document.querySelectorAll(".filter");
    const cards = document.querySelectorAll(".course-card");

    filters.forEach(filter => {
        filter.addEventListener("change", () => {
            const activeFilters = {};
            filters.forEach(f => {
                if (f.checked) {
                    const filterType = f.dataset.filter;
                    if (!activeFilters[filterType]) {
                        activeFilters[filterType] = [];
                    }
                    activeFilters[filterType].push(f.value);
                }
            });

            cards.forEach(card => {
                let visible = true;
                for (let type in activeFilters) {
                    // Split card's data attribute values into an array (handles multiple values)
                    const cardValues = card.dataset[type] ? card.dataset[type].split(
                        ",") : [];

                    // Check if any active filter matches the card's values
                    if (!activeFilters[type].some(filterValue => cardValues.includes(
                            filterValue))) {
                        visible = false;
                        break;
                    }
                }
                card.style.display = visible ? "block" : "none";
            });
        });
    });
});
</script>

<?php
    return ob_get_clean();
}

add_shortcode('course_filter_page', 'course_filter_page_shortcode');




// Available Courses

function render_available_courses($atts = [], $content = null) {
    // Get the topic and category from the URL
    $topic = isset($_GET['topic']) ? sanitize_text_field($_GET['topic']) : '';
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

    // Initialize courses array
    $courses = [];

    if ($topic || $category) {
        global $wpdb;

        // Prepare query based on parameters
        if ($topic) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}courses WHERE topic = %s", 
                $topic
            );
        } elseif ($category) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}courses WHERE category = %s", 
                $category
            );
        }

        // Execute query
        $courses = $wpdb->get_results($query);
    }

    // Start output buffering
    ob_start();

    // Check if courses are found
    if (!empty($courses)) {
        ?>
<section id="availableCourses" class="course-cards">
    <div class="container">
        <?php 
            $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
            foreach ($courses as $course): 
        ?>
        <div class="course-card">
            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>" alt="Course Image"
                class="course-image">
            <span
                class="course-tag <?php echo esc_attr($course->level === 'Fort' ? 'strong' : ($course->level === 'Débutant' ? 'beginner' : '')); ?>">
                <?php echo esc_html($course->level); ?>
            </span>
            <h3 class="course-title">
                <?php echo esc_html($course->title); ?>
            </h3>
            <p class="course-excerpt">
                <?php echo esc_html($course->description); ?>
            </p>
            <div class="course-footer">
                <span class="course-price">
                    <span class="amount"><?php echo esc_html($course->required_credit); ?></span> Credit
                </span>
                <a href="<?php echo site_url('/course-details/?id=' . $course->id); ?>" class="course-btn">Inscrire</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php
    } else {
        echo '<p>No courses found for the selected topic or category.</p>';
    }

    // Return the buffered content
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('available_courses', 'render_available_courses');






// Course Details Page

// shortcode [course_details]

function render_course_details_section() {
    global $wpdb;

    $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($course_id <= 0) {
        return '<p>Course not found.</p>';
    }

    $course = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}courses WHERE id = %d",
            $course_id
        )
    );

    // Check if the course exists
    if ($course) {
        // Extract the start date and time slot
        $start_date = strtotime($course->start_date); // Convert start date to a Unix timestamp
        $year = date('Y', $start_date); // Get the year
        $month = date('m', $start_date); // Get the month (numeric)
        $day = date('d', $start_date); // Get the day
        $time_slot = $course->time_slot; // The time slot

        // Define months in French for the month name
        $months = [
            '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
            '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
            '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
        ];

        // Time slots in the format of the table
        $time_slots = [
            '8:00 AM - 10:00 AM', '10:00 AM - 12:00 PM', '12:00 PM - 2:00 PM',
            '2:00 PM - 4:00 PM', '4:00 PM - 6:00 PM', '6:00 PM - 8:00 PM',
            '8:00 PM - 10:00 PM', '10:00 PM - 12:00 AM', '12:00 AM - 2:00 AM',
            '2:00 AM - 4:00 AM', '4:00 AM - 6:00 AM', '6:00 AM - 8:00 AM'
        ];

        ob_start();
        ?>
<div id="courseDetails" class="container row">
    <div class="left-column">
        <h2 class="title"><?php echo esc_html($course->title); ?></h2>
        <div class="description">
            <p><?php echo esc_html($course->description); ?></p>
        </div>

        <!-- Calendar Section -->
        <div class="calendar col">
            <div class="calendar-header row">
                <div class="start-date">
                    <p class="start-month"><?php echo esc_html($months[$month]); ?></p>
                    <p class="start-year"><?php echo $year; ?></p>
                </div>
            </div>

            <table class="table calendar-table">
                <tbody>
                    <tr>
                        <td>Lundi</td>
                        <td>Mardi</td>
                        <td>Mercredi</td>
                        <td>Jeudi</td>
                        <td>Vendredi</td>
                        <td>Samedi</td>
                        <td>Dimanche</td>
                        <td rowspan="6" class="special-heading">Date de début</td>
                    </tr>
                    <?php
                    // Generate the calendar for the selected month
                    $days_in_month = date('t', $start_date); // Number of days in the month
                    $first_day_of_month = date('N', strtotime("{$year}-{$month}-01")); // First day of the month

                    $current_day = 1;
                    for ($i = 1; $i <= 6; $i++) {
                        echo "<tr>";

                        for ($j = 1; $j <= 7; $j++) {
                            if (($i == 1 && $j >= $first_day_of_month) || ($i > 1 && $current_day <= $days_in_month)) {
                                $day_class = ($current_day == $day) ? 'class="current-month selected-date"' : 'class="current-month"';
                                echo "<td {$day_class}>{$current_day}</td>";
                                $current_day++;
                            } else {
                                echo "<td></td>";
                            }
                        }

                        echo "</tr>";
                        if ($current_day > $days_in_month) {
                            break;
                        }
                    }
                    ?>
                </tbody>
            </table>

            <!-- Time Slot Table -->
            <div class="time-table-section">
                <table class="table time-table">
                    <tbody>
                        <?php
                // Loop through the time slots and display them in 3 rows with 4 columns
                $slot_chunks = array_chunk($time_slots, 4);
                foreach ($slot_chunks as $slots_row) {
                    echo '<tr>';
                    foreach ($slots_row as $slot) {
                        $selected_class = ($slot == $time_slot) ? 'class="selected-time"' : '';
                        echo "<td {$selected_class}>{$slot}</td>";
                    }
                    echo '</tr>';
                }
                ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Section -->
    <div class="right-column col">
        <div class="course-info">
            <img class="course-image"
                src="<?php echo esc_url( ! empty( $course->image_url ) ? $course->image_url : get_template_directory_uri() . '/assets/image/image-placeholder.png' ); ?>"
                alt="Course image" width="400" height="300" />
            <h3 class="info-heading">Le cours comprend:</h3>
            <ul class="info-list">
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="pricetag-outline"></ion-icon> Prix:
                    </span>
                    <span class="item-value price">€ 10</span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="card-outline"></ion-icon> Crédit:
                    </span>
                    <span class="item-value"><?php echo esc_html($course->required_credit); ?> credit</span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="time-outline"></ion-icon> Durée:
                    </span>
                    <span class="item-value"><?php echo esc_html($course->duration); ?> heures</span>
                </li>
                <li class="list-item">
                    <span class="item-name">
                        <ion-icon name="people-outline"></ion-icon> Étudiants:
                    </span>
                    <span class="item-value">n/a</span>
                </li>
            </ul>
            <div class="buttons">
                <a href="#" class="button add-to-cart">
                    <ion-icon name="cart-outline"></ion-icon> AJOUTER AU PANIER
                </a>
                <a href="#" class="button buy-now">
                    <ion-icon name="bag-handle-outline"></ion-icon> ACHETER MAINTENANT
                </a>
            </div>
            <div class="share">
                Partager sur:
                <div class="social-buttons">
                    <!-- Facebook Share -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
                        target="_blank" class="social-link" title="Share on Facebook">
                        <ion-icon name="logo-facebook"></ion-icon>
                    </a>

                    <!-- Instagram Share -->
                    <a href="https://www.instagram.com/" target="_blank" class="social-link" title="Share on Instagram">
                        <ion-icon name="logo-instagram"></ion-icon>
                    </a>

                    <!-- LinkedIn Share -->
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>&summary=<?php echo urlencode(get_the_excerpt()); ?>"
                        target="_blank" class="social-link" title="Share on LinkedIn">
                        <ion-icon name="logo-linkedin"></ion-icon>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
        return ob_get_clean();
    } else {
        return '<p>Course not found.</p>';
    }
}
add_shortcode('course_details', 'render_course_details_section');
<?php

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
                href="<?php echo site_url('/topics/?category=' . urlencode($category->category)); ?>">Voir
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

    if (isset($_GET['category'])) {
        $_SESSION['category'] = urldecode($_GET['category']);
    }

    $selected_category = isset($_SESSION['category']) ? $_SESSION['category'] : '';
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
                            $is_checked = ($selected_category === $category->category) ? 'checked' : '';
                            echo '<label><input type="checkbox" class="filter" data-filter="category" value="' . esc_attr($category->category) . '" ' . $is_checked . '>' . esc_html($category->category) . '</label>';
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

    // Function to apply filters
    function applyFilters() {
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
                const cardValues = card.dataset[type] ? card.dataset[type].split(",") : [];

                // Check if any active filter matches the card's values
                if (!activeFilters[type].some(filterValue => cardValues.includes(filterValue))) {
                    visible = false;
                    break;
                }
            }
            card.style.display = visible ? "block" : "none";
        });
    }

    // Attach event listeners to filters
    filters.forEach(filter => {
        filter.addEventListener("change", applyFilters);
    });

    // Trigger filtering on page load if a category is preselected
    applyFilters();
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



// Add AJAX handler for fetching topics for admin add course page
add_action('wp_ajax_fetch_topics', 'fetch_topics_callback'); // For logged-in users
add_action('wp_ajax_nopriv_fetch_topics', 'fetch_topics_callback'); // For non-logged-in users

function fetch_topics_callback() {
    global $wpdb;

    // Check if category is provided
    if (isset($_POST['category'])) {
        $category = sanitize_text_field($_POST['category']);

        // Query topics for the selected category
        $topics = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}course_topics WHERE category = %s",
            $category
        ));

        if ($topics) {
            // Return topics as JSON
            wp_send_json_success($topics);
        } else {
            wp_send_json_error('No topics found for this category');
        }
    } else {
        wp_send_json_error('Category not provided');
    }
}
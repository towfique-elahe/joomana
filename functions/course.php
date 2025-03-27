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
            <p class="course-count <?php echo $course_count == 0 ? 'no-courses' : 'has-courses'; ?>">
                <?php echo esc_html($course_count); ?> Cours
            </p>
            <a class="course-button"
                href="<?php echo site_url('/topics/?category=' . urlencode($category->category)); ?>">Voir
                les cours</a>
        </div>
        <?php endforeach; ?>
        <?php else : ?>
        <div class="course-card">
            <p>Aucune catégorie trouvée</p>
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
            <h3 class="filter-section-heading">Filtre par Catégories</h3>
            <div>
                <?php
                    global $wpdb;
                    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");

                    if ($categories) {
                        foreach ($categories as $category) {
                            $is_checked = ($selected_category === $category->category) ? 'checked' : '';
                            echo '<label><input type="checkbox" class="filter" data-filter="category" value="' . esc_attr($category->category) . '" ' . $is_checked . '>' . esc_html($category->category) . '</label>';
                        }
                    } else {
                        echo '<option disabled>Aucune catégorie trouvée</option>';
                    }
                ?>
            </div>
        </div>

        <!-- grade filter -->
        <div class="filter-section">
            <h3 class="filter-section-heading">Filtre par Classe</h3>
            <div>
                <?php
                    global $wpdb;
                    $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");

                    if ($grades) {
                        foreach ($grades as $grade) {
                            echo '<label><input type="checkbox" class="filter" data-filter="grade" value="' . esc_attr($grade->grade) . '">' . esc_html($grade->grade) . '</label>';
                        }
                    } else {
                        echo '<option disabled>Aucune classe trouvée</option>';
                    }
                ?>
            </div>
        </div>

        <!-- level filter -->
        <div class="filter-section">
            <h3 class="filter-section-heading">Filtre par Niveau</h3>
            <div>
                <?php
                    global $wpdb;
                    $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");

                    if ($levels) {
                        foreach ($levels as $level) {
                            echo '<label><input type="checkbox" class="filter" data-filter="level" value="' . esc_attr($level->level) . '">' . esc_html($level->level) . '</label>';
                        }
                    } else {
                        echo '<option disabled>Aucune niveau trouvée</option>';
                    }
                ?>
            </div>
        </div>
    </div>

    <div class="course-cards">
        <?php
        global $wpdb;
        $topics = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_topics");
        $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
        ?>
        <?php if (!empty($topics)) : ?>
        <?php foreach ($topics as $topic) : 
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
            <p class="course-count <?php echo $course_count == 0 ? 'no-courses' : 'has-courses'; ?>">
                <?php echo esc_html($course_count); ?> Cours
            </p>
            <a class="course-button"
                href="<?php echo site_url('/available-courses/?topic=' . urldecode($topic->topic)); ?>">Voir les
                cours</a>
        </div>
        <?php endforeach; ?>
        <?php else : ?>
        <div class="no-data-message">
            <p>Aucun sujet trouvé</p>
        </div>
        <?php endif; ?>
        <!-- This will be our dynamic no results message -->
        <div class="no-data-message filter-no-results" style="display: none;">
            <p>Aucun cours ne correspond à vos critères de filtrage</p>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const filters = document.querySelectorAll(".filter");
    const cards = document.querySelectorAll(".course-card:not(.no-data-message)");
    const noResultsMessage = document.querySelector(".filter-no-results");
    const initialNoDataMessage = document.querySelector(".no-data-message:not(.filter-no-results)");

    // Function to apply filters
    function applyFilters() {
        const activeFilters = {};
        let hasActiveFilters = false;

        filters.forEach(f => {
            if (f.checked) {
                hasActiveFilters = true;
                const filterType = f.dataset.filter;
                if (!activeFilters[filterType]) {
                    activeFilters[filterType] = [];
                }
                activeFilters[filterType].push(f.value);
            }
        });

        let visibleCount = 0;

        cards.forEach(card => {
            let visible = true;

            // If no filters are active, show all cards
            if (!hasActiveFilters) {
                card.style.display = "block";
                visibleCount++;
                return;
            }

            // Otherwise apply filters
            for (let type in activeFilters) {
                const cardValues = card.dataset[type] ? card.dataset[type].split(",") : [];
                if (!activeFilters[type].some(filterValue => cardValues.includes(filterValue))) {
                    visible = false;
                    break;
                }
            }

            if (visible) {
                card.style.display = "block";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        // Handle no results message
        if (hasActiveFilters && visibleCount === 0) {
            noResultsMessage.style.display = "block";
            if (initialNoDataMessage) initialNoDataMessage.style.display = "none";
        } else {
            noResultsMessage.style.display = "none";
            if (initialNoDataMessage && !hasActiveFilters) initialNoDataMessage.style.display = "block";
        }
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
                <a href="<?php echo site_url('/course-details/?id=' . $course->id); ?>">
                    <?php echo esc_html($course->title); ?>
                </a>
            </h3>
            <p class="course-excerpt">
                <?php echo wp_strip_all_tags($course->description); ?>
            </p>
            <div class="course-footer">
                <span class="course-price">
                    <span class="amount"><?php echo esc_html($course->required_credit); ?></span> Crédit
                </span>
                <a href="<?php echo site_url('/course-details/?id=' . $course->id); ?>" class="course-btn">Détails</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php
    } else {
        echo '<p>Aucun cours trouvé pour le sujet ou la catégorie sélectionné.</p>';
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
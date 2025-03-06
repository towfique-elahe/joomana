<?php

/* Template Name: Course | Teacher Evaluation */

global $pageTitle, $wpdb;
$pageTitle = 'Évaluation des enseignants';

require_once(get_template_directory() . '/course/templates/header.php');

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$default_user_image = esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png');

// Get course_id from session
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    // Check the user's role and redirect accordingly
    if (in_array('parent', (array) $user->roles)) {
        wp_redirect(home_url('/parent/course-management/'));
        exit;
    } elseif (in_array('student', (array) $user->roles)) {
        wp_redirect(home_url('/student/course-management/'));
        exit;
    } elseif (in_array('teacher', (array) $user->roles)) {
        wp_redirect(home_url('/teacher/course-management/'));
        exit;
    } else {
        // Default redirection for other roles or if no role is matched
        wp_redirect(home_url());
        exit;
    }
}
$course_id = intval($_GET['course_id']);

global $wpdb;
$group_number = 0;

if (in_array('student', (array) $user->roles)) {
    $student_id = $user->ID;
    $student_group = $wpdb->get_var($wpdb->prepare(
        "SELECT group_number FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d LIMIT 1",
        $student_id,
        $course_id
    ));
    if ($student_group) {
        $group_number = intval($student_group);
    }
} elseif (in_array('parent', (array) $user->roles)) {
    $student_id = intval($_GET['student_id']);
    $student_group = $wpdb->get_var($wpdb->prepare(
        "SELECT group_number FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d LIMIT 1",
        $student_id,
        $course_id
    ));
    if ($student_group) {
        $group_number = intval($student_group);
    }
}

$teacher_id = $wpdb->get_var($wpdb->prepare(
    "SELECT teacher_id FROM {$wpdb->prefix}teacher_courses WHERE course_id = %d AND group_number = %d LIMIT 1",
    $course_id,
    $group_number
));

// Handle evaluation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_evaluation'])) {
    if (in_array('student', (array)$user->roles)) {
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $comment = sanitize_text_field($_POST['comment'] ?? '');

        if ($rating >= 1 && $rating <= 5) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}teacher_evaluations 
                WHERE student_id = %d AND course_id = %d 
                AND group_number = %d AND teacher_id = %d",
                $student_id, $course_id, $group_number, $teacher_id
            ));

            if ($existing) {
                $wpdb->update(
                    "{$wpdb->prefix}teacher_evaluations",
                    array(
                        'rating' => $rating,
                        'comment' => $comment,
                        'created_at' => current_time('mysql')
                    ),
                    array('id' => $existing)
                );
            } else {
                $wpdb->insert(
                    "{$wpdb->prefix}teacher_evaluations",
                    array(
                        'course_id' => $course_id,
                        'group_number' => $group_number,
                        'teacher_id' => $teacher_id,
                        'student_id' => $student_id,
                        'rating' => $rating,
                        'comment' => $comment,
                        'created_at' => current_time('mysql')
                    )
                );
            }
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// Get existing evaluations
$evaluations = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}teacher_evaluations WHERE course_id = %d AND group_number = %d AND teacher_id = %d ORDER BY created_at DESC",
    $course_id,
    $group_number,
    $teacher_id
));

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/course/templates/sidebar.php'); ?>
    </div>
    <div id="courseTeacherEvaluation" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Évaluation des enseignants</h2>
            <div class="content-breadcrumb">
                <?php 
                    if (current_user_can('student')) {
                ?>
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php 
                    } elseif (current_user_can('parent')) {
                ?>
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php 
                    } elseif (current_user_can('teacher')) {
                ?>
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <?php 
                    if (current_user_can('student')) {
                ?>
                <a href="<?php echo home_url('/student/course-management'); ?>" class="breadcrumb-link">Gestion des
                    enfants</a>
                <?php 
                    } elseif (current_user_can('parent')) {
                ?>
                <a href="<?php echo home_url('/parent/child-management'); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php 
                    } elseif (current_user_can('teacher')) {
                ?>
                <a href="<?php echo home_url('/teacher/course-management'); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Évaluation des enseignants</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="evaluation-list col">
                    <h3 class="section-heading">Évaluations soumises</h3>
                    <?php if (!empty($evaluations)) : ?>
                    <?php foreach ($evaluations as $eval) : ?>
                    <div class="evaluation-item">
                        <?php if (in_array('teacher', (array)$user->roles)) : ?>
                        <div class="student-info">
                            <?php echo esc_html($eval->first_name . ' ' . $eval->last_name); ?>
                        </div>
                        <?php endif; ?>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <span class="star <?php echo $i <= $eval->rating ? 'active' : ''; ?>">
                                <i class="fas fa-star"></i>
                            </span>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($eval->comment)) : ?>
                        <div class="comment">
                            <?php echo esc_html($eval->comment); ?>
                        </div>
                        <?php endif; ?>
                        <div class="evaluation-date">
                            <?php echo date('d/m/Y H:i', strtotime($eval->created_at)); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="no-data">Aucune évaluation trouvée.</p>
                    <?php endif; ?>
                </div>

                <?php if (in_array('student', (array)$user->roles)) : ?>
                <?php if ($teacher_id) : ?>
                <div class="evaluation-form col">
                    <h3 class="section-heading">Évaluer votre enseignant</h3>
                    <form method="POST" class="form">
                        <div class="star-rating">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <span class="star" data-value="<?php echo $i; ?>">
                                <i class="fas fa-star"></i>
                            </span>
                            <?php endfor; ?>
                            <input type="hidden" name="rating" id="rating" value="0" required>
                        </div>
                        <textarea name="comment" placeholder="Vos commentaires..." rows="4" required></textarea>
                        <button type="submit" name="submit_evaluation">Soumettre</button>
                    </form>
                </div>
                <?php else : ?>
                <p class="no-data">Aucun enseignant assigné à votre groupe.</p>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star rating interaction
    document.querySelectorAll('.star-rating .star').forEach(star => {
        star.addEventListener('click', function() {
            const container = this.closest('.star-rating');
            const value = parseInt(this.dataset.value);
            container.querySelectorAll('.star').forEach(s => {
                s.classList.toggle('active', s.dataset.value <= value);
            });
            container.querySelector('#rating').value = value;
        });
    });
});
</script>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>
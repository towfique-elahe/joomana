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

// Redirect if session_id is not provided
if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
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
        wp_redirect(home_url());
        exit;
    }
}
$session_id = intval($_GET['session_id']);

global $wpdb;

if (in_array('parent', (array) $user->roles)) {
    $student_id = intval($_GET['student_id']);
} elseif (in_array('student', (array) $user->roles)) {
    $student_id = $user->ID;
} elseif (in_array('teacher', (array) $user->roles)) {
    $teacher_id = $user->ID;
}

// Handle evaluation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_evaluation'])) {
    if (in_array('student', (array)$user->roles)) {
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $comment = sanitize_text_field($_POST['comment'] ?? '');

        if ($rating >= 1 && $rating <= 5) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}teacher_evaluations 
                WHERE student_id = %d AND session_id = %d 
                AND teacher_id = %d",
                $student_id, $session_id, $teacher_id
            ));

            if (!$existing) {
                $wpdb->insert(
                    "{$wpdb->prefix}teacher_evaluations",
                    array(
                        'session_id' => $session_id,
                        'teacher_id' => $teacher_id,
                        'student_id' => $student_id,
                        'rating' => $rating,
                        'comment' => $comment,
                        'created_at' => current_time('mysql')
                    )
                );
                wp_redirect($_SERVER['REQUEST_URI']);
                exit;
            }
        }
    }
}

// Check if the student has already submitted an evaluation
$has_evaluated = false;
if (in_array('student', (array)$user->roles)) {
    $has_evaluated = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}teacher_evaluations 
        WHERE student_id = %d AND session_id = %d 
        AND teacher_id = %d",
        $student_id, $session_id, $teacher_id
    ));
}

// Get existing evaluations
if (in_array('teacher', (array) $user->roles)) {
    $evaluations = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}teacher_evaluations WHERE session_id = %d AND teacher_id = %d ORDER BY created_at DESC",
        $session_id,
        $teacher_id
    ));
} elseif {
    $evaluations = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}teacher_evaluations WHERE session_id = %d AND student_id = %d ORDER BY created_at DESC",
        $session_id,
        $student_id
    ));
}

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
                    cours</a>
                <?php 
                    } elseif (current_user_can('parent')) {
                ?>
                <a href="<?php echo home_url('/parent/child-management'); ?>" class="breadcrumb-link">Gestion des
                    enfants</a>
                <?php 
                    } elseif (current_user_can('teacher')) {
                ?>
                <a href="<?php echo home_url('/teacher/course-management'); ?>" class="breadcrumb-link">Gestion des
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

                    <?php
    global $wpdb;

    // Function to display relative time in French
    function time_elapsed_fr($datetime) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->y > 0) return 'il y a ' . $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) return 'il y a ' . $diff->m . ' mois';
        if ($diff->d > 0) return 'il y a ' . $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        if ($diff->h > 0) return 'il y a ' . $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        if ($diff->i > 0) return 'il y a ' . $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        return 'à l\'instant';
    }
    ?>

                    <?php if (!empty($evaluations)) : ?>
                    <?php foreach ($evaluations as $eval) : ?>
                    <div class="evaluation-item">

                        <!-- Rating stars -->
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <span class="star <?php echo $i <= $eval->rating ? 'active' : ''; ?>">
                                <i class="fas fa-star"></i>
                            </span>
                            <?php endfor; ?>
                        </div>

                        <!-- Student info -->
                        <div class="student-info">
                            <?php
                    // Fetch only necessary fields for better performance
                    $student = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT first_name, last_name FROM {$wpdb->prefix}students WHERE id = %d",
                            $eval->student_id
                        )
                    );

                    echo esc_html($student ? ($student->first_name . ' ' . $student->last_name) : 'Étudiant inconnu');
                    ?>
                        </div>

                        <!-- Evaluation date (relative format in French) -->
                        <div class="evaluation-date">
                            <?php echo esc_html(time_elapsed_fr($eval->created_at)); ?>
                        </div>

                        <!-- Optional comment -->
                        <?php if (!empty($eval->comment)) : ?>
                        <div class="comment">
                            <?php echo esc_html($eval->comment); ?>
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <p class="no-data">Aucune évaluation trouvée.</p>
                    <?php endif; ?>
                </div>


                <?php if (in_array('student', (array)$user->roles) && !$has_evaluated && $teacher_id) : ?>
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
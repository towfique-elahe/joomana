<?php

/* Template Name: Course | Sidebar */

?>

<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/details/?course_id=' . $course_id); ?>">
                <i class="fas fa-book"></i> Détails du cours
            </a>
        </li>
        <?php 
            if (current_user_can('teacher')) {
        ?>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/student-management/?course_id=' . $course_id); ?>">
                <i class="fas fa-graduation-cap"></i> Gestion étudiants
            </a>
        </li>
        <?php } ?>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/resources/?course_id=' . $course_id); ?>">
                <i class="fas fa-folder-open"></i> Ressources
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/submissions/?course_id=' . $course_id); ?>">
                <i class="fas fa-tasks"></i> Soumission
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/communication/?course_id=' . $course_id); ?>">
                <i class="fas fa-comment-alt"></i> Communication
            </a>
        </li>
        <?php 
            if (current_user_can('student')) {
        ?>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/teacher-evaluation/?course_id=' . $course_id); ?>">
                <i class="fas fa-star-half-alt"></i> Évaluation
            </a>
        </li>
        <?php } ?>
    </ul>

    <ul class="sidebar-items">
        <li class="sidebar-item" style="border: var(--border); border-radius: .3rem">
            <?php 
                if (current_user_can('student')) {
            ?>
            <a href="<?php echo home_url('/student/dashboard/'); ?>">
                <i class="fas fa-backspace"></i> Tableau de bord
            </a>
            <?php 
                } elseif (current_user_can('teacher')) {
            ?>
            <a href="<?php echo home_url('/teacher/dashboard/'); ?>">
                <i class="fas fa-backspace"></i> Tableau de bord
            </a>
            <?php } ?>
        </li>
        <li class="sidebar-item logout">
            <a href="<?php echo wp_logout_url(home_url('/login/')); ?>">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
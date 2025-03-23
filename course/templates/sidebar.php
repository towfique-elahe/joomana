<?php

/* Template Name: Course | Sidebar */

?>

<!-- Teacher Sidebar -->
<?php 
    if (current_user_can('teacher')) {
?>
<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/details/?session_id=' . $session_id); ?>">
                <i class="fas fa-book"></i> Détails du cours
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/student-management/?session_id=' . $session_id); ?>">
                <i class="fas fa-graduation-cap"></i> Gestion Elèves
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/resources/?session_id=' . $session_id); ?>">
                <i class="fas fa-folder-open"></i> Resources
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/submissions/?session_id=' . $session_id); ?>">
                <i class="fas fa-tasks"></i> Soumission
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/communication/?session_id=' . $session_id); ?>">
                <i class="fas fa-comment-alt"></i> Communication
            </a>
        </li>
    </ul>

    <ul class="sidebar-items">
        <li class="sidebar-item" style="border: var(--border); border-radius: .3rem">
            <a href="<?php echo home_url('/teacher/dashboard/'); ?>">
                <i class="fas fa-backspace"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item logout">
            <a href="<?php echo wp_logout_url(home_url('/login/')); ?>">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
<?php
    }
?>

<!-- Student Sidebar -->
<?php 
    if (current_user_can('student')) {
?>
<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/details/?session_id=' . $session_id); ?>">
                <i class="fas fa-book"></i> Détails du cours
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/resources/?session_id=' . $session_id); ?>">
                <i class="fas fa-folder-open"></i> Resources
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/submissions/?session_id=' . $session_id); ?>">
                <i class="fas fa-tasks"></i> Soumission
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/communication/?session_id=' . $session_id); ?>">
                <i class="fas fa-comment-alt"></i> Communication
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/course/teacher-evaluation/?session_id=' . $session_id); ?>">
                <i class="fas fa-star-half-alt"></i> Évaluation
            </a>
        </li>
    </ul>

    <ul class="sidebar-items">
        <li class="sidebar-item" style="border: var(--border); border-radius: .3rem">
            <a href="<?php echo home_url('/student/dashboard/'); ?>">
                <i class="fas fa-backspace"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item logout">
            <a href="<?php echo wp_logout_url(home_url('/login/')); ?>">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
<?php
    }
?>

<!-- Parent Sidebar -->
<?php 
    if (current_user_can('parent')) {
?>
<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a
                href="<?php echo home_url('/course/details/?session_id=' . $session_id . '&student_id=' . $student_id); ?>">
                <i class="fas fa-book"></i> Détails du cours
            </a>
        </li>
        <li class="sidebar-item">
            <a
                href="<?php echo home_url('/course/resources/?session_id=' . $session_id . '&student_id=' . $student_id); ?>">
                <i class="fas fa-folder-open"></i> Resources
            </a>
        </li>
        <li class="sidebar-item">
            <a
                href="<?php echo home_url('/course/submissions/?session_id=' . $session_id . '&student_id=' . $student_id); ?>">
                <i class="fas fa-tasks"></i> Soumission
            </a>
        </li>
        <li class="sidebar-item">
            <a
                href="<?php echo home_url('/course/communication/?session_id=' . $session_id . '&student_id=' . $student_id); ?>">
                <i class="fas fa-comment-alt"></i> Communication
            </a>
        </li>
        <li class="sidebar-item">
            <a
                href="<?php echo home_url('/course/teacher-evaluation/?session_id=' . $session_id . '&student_id=' . $student_id); ?>">
                <i class="fas fa-star-half-alt"></i> Évaluation
            </a>
        </li>
    </ul>

    <ul class="sidebar-items">
        <li class="sidebar-item" style="border: var(--border); border-radius: .3rem">
            <a href="<?php echo home_url('/parent/dashboard/'); ?>">
                <i class="fas fa-backspace"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item logout">
            <a href="<?php echo wp_logout_url(home_url('/login/')); ?>">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
<?php
    }
?>
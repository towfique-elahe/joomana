<?php

/* Template Name: Student | Sidebar */

?>

<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/student/dashboard/'); ?>">
                <i class="fas fa-th-large"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/student/course-management/'); ?>">
                <i class="fas fa-book"></i> Cours
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/student/resources/'); ?>">
                <i class="fas fa-folder-open"></i> Ressources
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/student/progress/'); ?>">
                <i class="fas fa-tasks"></i> Progrès
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/student/credit-management/'); ?>">
                <i class="far fa-credit-card"></i> Crédits
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/student/payments/'); ?>">
                <i class="fas fa-exchange-alt"></i> Paiements
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/student/settings/'); ?>">
                <i class="fas fa-user-cog"></i> Paramètres
            </a>
        </li>
    </ul>

    <ul class="sidebar-items">
        <li class="sidebar-item logout">
            <a href="<?php echo wp_logout_url(home_url('/login/')); ?>">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
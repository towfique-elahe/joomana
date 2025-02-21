<?php

/* Template Name: Teacher | Sidebar */

?>

<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/dashboard/'); ?>">
                <i class="fas fa-th-large"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/course-management/'); ?>">
                <i class="fas fa-book"></i> Gestion des cours
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/resources/'); ?>">
                <i class="fas fa-folder-open"></i> Resources
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/revenues/'); ?>">
                <i class="fas fa-credit-card"></i> Revenus
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/settings/'); ?>">
                <i class="fas fa-cog"></i> Paramètres
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
<?php

/* Template Name: Admin | Sidebar */

?>

<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/dashboard/'); ?>">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/student-management/'); ?>">
                <i class="fas fa-user-graduate"></i> Gestion Elèves
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/parent-management/'); ?>">
                <i class="fas fa-users"></i> Gestion Parents
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/teacher-management/'); ?>">
                <i class="fas fa-chalkboard-teacher"></i> Gestion Enseignants
            </a>
        </li>
        <li class="sidebar-item parent">
            <a href="<?php echo home_url('/admin/course-management/courses/'); ?>">
                <i class="fas fa-university"></i> Gestion des cours
                <i class="fas fa-caret-down dropdown-icon"></i>
            </a>
            <ul class="sidebar-sub-items">
                <li class="sidebar-sub-item child">
                    <a href="<?php echo home_url('/admin/course-management/courses/'); ?>">
                        <i class="fas fa-book"></i> Cours
                    </a>
                </li>
                <li class="sidebar-sub-item child">
                    <a href="<?php echo home_url('/admin/course-management/categories/'); ?>">
                        <i class="fas fa-layer-group"></i> Catégories
                    </a>
                </li>
                <li class="sidebar-sub-item child">
                    <a href="<?php echo home_url('/admin/course-management/topics/'); ?>">
                        <i class="fas fa-th-list"></i> Sujets de cours
                    </a>
                </li>
                <li class="sidebar-sub-item child">
                    <a href="<?php echo home_url('/admin/course-management/grades/'); ?>">
                        <i class="fas fa-signal"></i> Classe
                    </a>
                </li>
                <li class="sidebar-sub-item child">
                    <a href="<?php echo home_url('/admin/course-management/levels/'); ?>">
                        <i class="fas fa-level-up-alt"></i> Niveaus elèves
                    </a>
                </li>
            </ul>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/session-management/'); ?>">
                <i class="fas fa-clipboard-list"></i> Gestion des sessions
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/payments/'); ?>">
                <i class="fas fa-exchange-alt"></i> Paiements
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/teacher-payments/'); ?>">
                <i class="fas fa-people-arrows"></i> Paiements Prof
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/admin/settings/'); ?>">
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
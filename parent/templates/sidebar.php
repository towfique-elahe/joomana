<?php

/* Template Name: Parent | Sidebar */

?>

<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/parent/dashboard/'); ?>">
                <i class="fas fa-th-large"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/parent/child-management/'); ?>">
                <i class="fas fa-graduation-cap"></i> Gestion des Enfants
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/parent/credit-management/'); ?>">
                <i class="far fa-credit-card"></i> Crédits
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/parent/payments/'); ?>">
                <i class="fas fa-exchange-alt"></i>Paiements
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/parent/settings/'); ?>">
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
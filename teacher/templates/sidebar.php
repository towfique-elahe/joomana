<?php

/* Template Name: Teacher | Sidebar */

?>

<div class="sidebar">
    <ul class="sidebar-items">
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/dashboard/'); ?>">
                <i class="fa fa-th-large" aria-hidden="true"></i> Tableau de bord
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/student-management/'); ?>">
                <i class="fa fa-graduation-cap" aria-hidden="true"></i> Gestion étudiants
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/course-management/'); ?>">
                <i class="fa fa-book" aria-hidden="true"></i> Gestion des cours
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/resources/'); ?>">
                <i class="fa fa-files-o" aria-hidden="true"></i> Ressources
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/revenues/'); ?>">
                <i class="fa fa-credit-card" aria-hidden="true"></i> Revenus
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?php echo home_url('/teacher/settings/'); ?>">
                <i class="fa fa-cog" aria-hidden="true"></i> Paramètres
            </a>
        </li>
    </ul>

    <ul class="sidebar-items">
        <li class="sidebar-item logout">
            <a href="<?php echo wp_logout_url(home_url('/login/')); ?>">
                <i class="fa fa-sign-out" aria-hidden="true"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const currentUrl = window.location.pathname.replace(/\/$/, ""); // Normalize current URL
    const sidebarLinks = document.querySelectorAll('.sidebar a');

    sidebarLinks.forEach(link => {
        const linkUrl = new URL(link.href).pathname.replace(/\/$/, ""); // Normalize link URL

        // Remove the active class from all links
        link.classList.remove('active');

        // Add the active class to the link whose href matches the current URL
        if (linkUrl === currentUrl) {
            link.classList.add('active');

            // Add active class to the parent anchor if the current link is a child
            const parentItem = link.closest('.sidebar-sub-item')?.closest('.parent');
            if (parentItem) {
                parentItem.querySelector('a').classList.add('active');
            }
        }
    });
});
</script>
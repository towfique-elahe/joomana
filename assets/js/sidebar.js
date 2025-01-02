document.addEventListener('DOMContentLoaded', () => {
    const currentUrl = window.location.pathname.replace(/\/$/, ""); // Normalize current URL
    const sidebarLinks = document.querySelectorAll('.sidebar a');

    sidebarLinks.forEach(link => {
        const linkUrl = new URL(link.href).pathname.replace(/\/$/, ""); // Normalize link URL

        // Remove the active class from all links
        link.classList.remove('active');

        // Add the active class to the link if the current URL is equal to or a sub-URL of the link
        if (currentUrl === linkUrl || currentUrl.startsWith(linkUrl + '/')) {
            link.classList.add('active');

            // Add active class to the parent anchor if the current link is a child
            const parentItem = link.closest('.sidebar-sub-item')?.closest('.parent');
            if (parentItem) {
                parentItem.querySelector('a').classList.add('active');
                parentItem.querySelector('.sidebar-sub-items').style.display = 'flex';
            }
        }
    });

    // Show the sub-items of parents that have an active child
    const parents = document.querySelectorAll('.parent');
    parents.forEach(parent => {
        const isActive = parent.querySelector('a.active');
        const subItems = parent.querySelector('.sidebar-sub-items');
        if (subItems) {
            subItems.style.display = isActive ? 'flex' : 'none';
        }
    });
});

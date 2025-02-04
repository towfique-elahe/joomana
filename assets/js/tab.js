document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll("#courseTabs .nav-link");
    const tabContents = document.querySelectorAll(".tab-pane");

    tabs.forEach(tab => {
        tab.addEventListener("click", function(event) {
            event.preventDefault();

            // Remove active class from all tabs and hide all content
            tabs.forEach(t => t.classList.remove("active"));
            tabContents.forEach(content => content.classList.remove("show", "active"));

            // Add active class to clicked tab and show corresponding content
            this.classList.add("active");
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.classList.add("show", "active");
            }
        });
    });
});
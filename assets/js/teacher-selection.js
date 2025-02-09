document.addEventListener("DOMContentLoaded", function () {
    const teacherContainer = document.querySelector(".teacher-container");
    const selectedTeachers = document.querySelector(".selected-teachers");
    const searchInput = document.getElementById("search-teacher-input");
    const teacherCards = teacherContainer.querySelectorAll(".teacher-card");

    // Initially hide all teacher cards
    teacherCards.forEach((card) => {
        card.style.display = "none";
    });

    // Add teacher selection functionality
    teacherContainer.addEventListener("click", function (event) {
        const teacherCard = event.target.closest(".teacher-card"); // Check if the click is on a teacher-card or its children
        if (teacherCard) {
            const teacherId = teacherCard.getAttribute("data-id");
            const teacherName = teacherCard.querySelector(".teacher-name").textContent;
            const teacherImage = teacherCard.querySelector(".teacher-image").src;

            // Check if the teacher is already selected to prevent duplicates
            if (!selectedTeachers.querySelector(`[value="${teacherId}"]`)) {
                // Create selected teacher card
                const selectedCard = document.createElement("div");
                selectedCard.classList.add("teacher-card");
                selectedCard.innerHTML = `
                    <img src="${teacherImage}" alt="${teacherName}" class="teacher-image">
                    <h3 class="teacher-name">${teacherName}</h3>
                    <button class="remove-teacher" type="button">&#10060</button>
                    <input type="hidden" name="assigned_teachers[]" value="${teacherId}">
                `;

                selectedTeachers.appendChild(selectedCard);
            }
        }
    });

    // Add teacher removal functionality
    selectedTeachers.addEventListener("click", function (event) {
        if (event.target.classList.contains("remove-teacher")) {
            const teacherCard = event.target.closest(".teacher-card");
            teacherCard.remove();
        }
    });

    // Add search functionality with a limit of 10 results
    searchInput.addEventListener("input", function () {
        const searchValue = searchInput.value.toLowerCase();
        let displayedCount = 0; // Counter to track the number of displayed teachers

        teacherCards.forEach((card) => {
            const teacherName = card.querySelector(".teacher-name").textContent.toLowerCase();

            if (teacherName.includes(searchValue) && displayedCount < 10) {
                card.style.display = ""; // Show matching card
                displayedCount++; // Increment the counter
            } else {
                card.style.display = "none"; // Hide non-matching or excess cards
            }
        });
    });

    // Handle form submission to ensure assigned teachers are included
    const form = document.querySelector("form.add-form");
    if (form) {
        form.addEventListener("submit", function (event) {
            // Collect all assigned teacher IDs
            const assignedTeachers = selectedTeachers.querySelectorAll("input[name='assigned_teachers[]']");
            const teacherIds = Array.from(assignedTeachers).map(input => input.value);
        });
    }
});
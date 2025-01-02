
// form message animation

// Wait for the DOM to fully load
    document.addEventListener('DOMContentLoaded', function () {
        // Select error and success messages
        const errorElement = document.querySelector('.form-error');
        const successElement = document.querySelector('.form-success');

        // Set a timeout to remove the messages after 3 seconds
        setTimeout(() => {
            if (errorElement) {
                errorElement.style.transition = 'opacity 0.5s';
                errorElement.style.opacity = '0';
                setTimeout(() => errorElement.remove(), 500); // Fully remove after fade-out
            }
            if (successElement) {
                successElement.style.transition = 'opacity 0.5s';
                successElement.style.opacity = '0';
                setTimeout(() => successElement.remove(), 500); // Fully remove after fade-out
            }
        }, 3000);
    });






    // teacher registration form

    // teacher's regsitration form upload cv validation
document.getElementById("upload_cv").addEventListener("change", function(event) {
    const fileInput = event.target;
    const file = fileInput.files[0];
    const preview = document.querySelector(".cv-file-name");

    // Validate file type
    if (file && file.type !== "application/pdf") {
        alert("Only PDF files are allowed.");
        fileInput.value = ""; // Clear input
        preview.textContent = "No file selected";
        return;
    }

    // Validate file size (2MB = 2 * 1024 * 1024 bytes)
    if (file && file.size > 2 * 1024 * 1024) {
        alert("File size exceeds 2MB. Please upload a smaller file.");
        fileInput.value = ""; // Clear input
        preview.textContent = "No file selected";
        return;
    }

    // Show selected file name
    if (file) {
        preview.textContent = `Selected File: ${file.name}`;
    } else {
        preview.textContent = "No file selected";
    }
});

// teacher's regsitration form upload document validation
document.addEventListener("DOMContentLoaded", function () {
    let uploadCount = 1;
    const maxUploads = 5;
    const uploadContainer = document.getElementById("uploadDocContainer");

    // Function to update the position of the "Add Another Document" button
    function updateAddButtonPosition() {
        const allUploadGroups = uploadContainer.querySelectorAll(".upload-group");
        const lastUploadGroup = allUploadGroups[allUploadGroups.length - 1];

        // Ensure the "Add Another Document" button is only appended to the last upload group
        const addUploadButton = document.getElementById("addUploadButton");
        if (addUploadButton) {
            lastUploadGroup.appendChild(addUploadButton);
        }
    }

    // Add event listener to the "Add Another Document" button
    document.getElementById("addUploadButton").addEventListener("click", function () {
        if (uploadCount < maxUploads) {
            uploadCount++;
            const newUpload = document.createElement("div");
            newUpload.classList.add("upload-group");
            newUpload.classList.add("row");
            newUpload.innerHTML = `
                <div class="upload-button-group">
                    <div class="upload-cv-button">
                        <label for="upload_doc${uploadCount}" class="upload-cv-label">
                            Télécharger le document <ion-icon name="document-attach-outline"></ion-icon>
                        </label>
                        <input type="file" id="upload_doc${uploadCount}" name="upload_doc${uploadCount}" accept=".pdf"
                            class="upload-cv-input">
                    </div>
                    <p class="text">(PDF uniquement, max 2 Mo)</p>
                    <p class="cv-file-name" id="file-name-${uploadCount}">Aucun fichier sélectionné</p>
                </div>
            `;
            uploadContainer.appendChild(newUpload);

            // Update button position after adding a new group
            updateAddButtonPosition();

            // Hide the "Add Another Document" button if max uploads are reached
            if (uploadCount === maxUploads) {
                const addUploadButton = document.getElementById("addUploadButton");
                addUploadButton.style.display = "none";
            }
        }
    });

    // File name preview for each input
    uploadContainer.addEventListener("change", function (e) {
        if (e.target.classList.contains("upload-cv-input")) {
            const fileInput = e.target;
            const file = fileInput.files[0];
            const fileNameDisplay = fileInput.closest(".upload-button-group").querySelector(".cv-file-name");

            if (file && file.type === "application/pdf" && file.size <= 2 * 1024 * 1024) {
                fileNameDisplay.textContent = `Selected File: ${file.name}`;
            } else if (file && file.type !== "application/pdf") {
                alert("Only PDF files are allowed.");
                fileInput.value = "";
                fileNameDisplay.textContent = "No file selected";
            } else if (file && file.size > 2 * 1024 * 1024) {
                alert("File size exceeds 2MB. Please upload a smaller file.");
                fileInput.value = "";
                fileNameDisplay.textContent = "No file selected";
            }
        }
    });

    // Initial position update for the add button
    updateAddButtonPosition();
});

// teacher's regsitration form upload video validation
document.addEventListener("DOMContentLoaded", function () {
    const videoInput = document.getElementById("upload_video");
    const fileNameDisplay = document.querySelector(".video-file-name");

    videoInput.addEventListener("change", function () {
        const file = videoInput.files[0];

        if (file) {
            const validFormats = ["video/mp4", "video/quicktime"]; // MP4 and MOV MIME types

            if (!validFormats.includes(file.type)) {
                alert("Only MP4 and MOV formats are allowed.");
                videoInput.value = ""; // Reset input
                fileNameDisplay.textContent = "No file selected";
                return;
            }

            if (file.size > 20 * 1024 * 1024) {
                alert("File size exceeds 20MB. Please upload a smaller file.");
                videoInput.value = ""; // Reset input
                fileNameDisplay.textContent = "No file selected";
                return;
            }

            // Display the selected file name
            fileNameDisplay.textContent = `Selected File: ${file.name}`;
        } else {
            fileNameDisplay.textContent = "No file selected";
        }
    });
});

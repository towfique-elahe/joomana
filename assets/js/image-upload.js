
// image upload validation
document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById("upload_image");
    const fileNameDisplay = document.querySelector(".image-file-name");

    imageInput.addEventListener("change", function () {
        const file = imageInput.files[0];

        if (file) {
            console.log("File selected:", file.name); // Debugging log
            const validFormats = ["image/jpeg", "image/png"]; // Allow JPEG and PNG formats only
            const maxFileSize = 2 * 1024 * 1024; // 2 MB size limit

            if (!validFormats.includes(file.type)) {
                alert("Only JPEG and PNG formats are allowed.");
                imageInput.value = ""; // Reset input
                fileNameDisplay.textContent = "No file selected";
                return;
            }

            if (file.size > maxFileSize) {
                alert("File size exceeds 2MB. Please upload a smaller file.");
                imageInput.value = ""; // Reset input
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

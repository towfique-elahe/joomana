document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal');
    const confirmButton = document.getElementById('confirmBtn');
    const cancelButton = document.getElementById('cancelBtn');
    const closeModalButton = document.querySelector('.modal-close');
    let formToSubmit = null;

    // Open modal
    document.querySelectorAll('.open-modal').forEach(button => {
        button.addEventListener('click', function() {
            formToSubmit = this.closest('form');
            modal.style.display = 'block';
        });
    });

    // Close modal
    const closeModal = () => {
        modal.style.display = 'none';
        formToSubmit = null;
    };

    closeModalButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    // Confirm delete
    confirmButton.addEventListener('click', function() {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
});
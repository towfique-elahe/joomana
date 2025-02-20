document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    let activeModal = null;
    let formToSubmit = null;

    // Open modal
    document.querySelectorAll('.open-modal').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.dataset.modal;
            activeModal = document.getElementById(modalId);
            if (activeModal) {
                activeModal.style.display = 'block';
                formToSubmit = this.closest('form');
            }
        });
    });

    // Close modal
    const closeModal = () => {
        if (activeModal) {
            activeModal.style.display = 'none';
            activeModal = null;
            formToSubmit = null;
        }
    };

    document.querySelectorAll('.modal-close, .close-modal').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    // Confirm button submit
    document.getElementById('confirmBtn')?.addEventListener('click', function() {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });

    // Close when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal();
        }
    });
});
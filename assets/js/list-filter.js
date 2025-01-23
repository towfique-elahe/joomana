// Function to filter the list by name and invoice number
function filterUser() {
    const input = document.querySelector('.search-bar input');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#list tr');

    rows.forEach(row => {
        const name = row.querySelector('.name a')?.textContent.toLowerCase() || '';
        const invoiceNumber = row.querySelector('.invoice-number')?.textContent.toLowerCase() || '';

        if (name.includes(filter) || invoiceNumber.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Function to filter the list by status
function filterStatus(status, button) {
    const rows = document.querySelectorAll('#list tr');
    const buttons = document.querySelectorAll('.status-filter button');

    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    // Filter rows by status
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Function to filter the list by grade and level
function filterBySelect() {
    const gradeFilter = document.getElementById('grade-filter').value.toLowerCase();
    const levelFilter = document.getElementById('level-filter').value.toLowerCase();
    const rows = document.querySelectorAll('#list tr');

    rows.forEach(row => {
        const rowGrade = row.getAttribute('data-grade');
        const rowLevel = row.getAttribute('data-level');

        const gradeMatch = gradeFilter === 'all' || rowGrade === gradeFilter;
        const levelMatch = levelFilter === 'all' || rowLevel === levelFilter;

        if (gradeMatch && levelMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
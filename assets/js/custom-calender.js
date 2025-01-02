const yearSelect = document.getElementById('yearSelect');
const monthSelect = document.getElementById('monthSelect');
const calendarTable = document.getElementById('calendarTable');
const timeTable = document.getElementById('timeTable');
const resetButton = document.getElementById('resetButton');

// Function to set year and month from start_date
function initializeYearAndMonth() {
    const startDate = document.getElementById('start_date').value;

    if (startDate) {
        const [year, month] = startDate.split('-'); // Split start_date into year and month
        yearSelect.value = year; // Select the year in the dropdown
        monthSelect.value = parseInt(month, 10); // Select the month (ensure it is an integer)
    }
}

// Function to generate the calendar
function generateCalendar(year, month) {
    const firstDay = new Date(year, month - 1, 1).getDay(); // Adjust for 1-based month selection
    const daysInMonth = new Date(year, month, 0).getDate(); // Get the number of days in the month
    const tbody = calendarTable.querySelector('tbody');
    tbody.innerHTML = ''; // Clear previous calendar

    let date = 1;
    for (let i = 0; i < 6; i++) { // Create rows (up to 6 rows in a calendar month)
        const row = document.createElement('tr');
        for (let j = 0; j < 7; j++) { // Create cells (7 days in a week)
            const cell = document.createElement('td');
            if (i === 0 && j < firstDay) {
                cell.textContent = ''; // Leave empty cells before the first day
            } else if (date > daysInMonth) {
                break; // Stop once we've filled all the days of the month
            } else {
                // Ensure the month and day are formatted to have leading zeros
                const formattedMonth = String(month).padStart(2, '0'); // Month should be 01-12
                const formattedDate = String(date).padStart(2, '0'); // Day should be 01-31
                
                // Set the date in the format YYYY-MM-DD
                cell.textContent = date;
                cell.classList.add('current-month');
                cell.dataset.date = `${year}-${formattedMonth}-${formattedDate}`; // Correct format
                
                // Highlight the selected date if it matches start_date
                if (cell.dataset.date === document.getElementById('start_date').value) {
                    cell.classList.add('selected-date');
                }

                date++; // Increment the date
            }
            row.appendChild(cell);
        }
        tbody.appendChild(row);
    }
}

// Function to highlight the selected time
function highlightSelectedTime() {
    const timeSlot = document.getElementById('time_slot').value;
    const timeCells = timeTable.querySelectorAll('td');

    timeCells.forEach((cell) => {
        if (cell.textContent.trim() === timeSlot) {
            cell.classList.add('selected-time');
        } else {
            cell.classList.remove('selected-time');
        }
    });
}

// Function to handle date selection
calendarTable.addEventListener('click', function (e) {
    if (e.target.tagName === 'TD' && e.target.dataset.date) {
        const previousSelected = calendarTable.querySelector('.selected-date');
        if (previousSelected) previousSelected.classList.remove('selected-date');
        e.target.classList.add('selected-date');

        const selectedDate = e.target.dataset.date;
        document.getElementById('start_date').value = selectedDate; // Populate start_date field
    }
});

// Function to handle time selection
timeTable.addEventListener('click', function (e) {
    if (e.target.tagName === 'TD') {
        const previousSelected = timeTable.querySelector('.selected-time');
        if (previousSelected) previousSelected.classList.remove('selected-time');
        e.target.classList.add('selected-time');

        document.getElementById('time_slot').value = e.target.textContent.trim(); // Populate time_slot
    }
});

// Reset button functionality
resetButton.addEventListener('click', () => {
    // Deselect the currently selected date
    const previousSelectedDate = calendarTable.querySelector('.selected-date');
    if (previousSelectedDate) previousSelectedDate.classList.remove('selected-date');
    
    // Deselect the currently selected time
    const previousSelectedTime = timeTable.querySelector('.selected-time');
    if (previousSelectedTime) previousSelectedTime.classList.remove('selected-time');

    // Reset date and time inputs
    document.getElementById('start_date').value = '';
    document.getElementById('time_slot').value = '';
});

// Event listeners for year and month changes
yearSelect.addEventListener('change', () => generateCalendar(yearSelect.value, monthSelect.value));
monthSelect.addEventListener('change', () => generateCalendar(yearSelect.value, monthSelect.value));

// Initialize calendar with current date or start_date
initializeYearAndMonth(); // Set year and month based on start_date
generateCalendar(yearSelect.value, monthSelect.value); // Generate calendar with the selected year and month

// Highlight the selected time from the database on load
highlightSelectedTime();

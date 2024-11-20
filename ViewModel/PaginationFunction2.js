document.addEventListener('DOMContentLoaded', () => {
    fetchEventSummary();
});

let currentPage = 1;
const entriesPerPage = 10;

function fetchEventSummary() {
    fetch('/SIA_Project/Model/bookingAPI2.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'get_event_summary',
            limit: entriesPerPage,
            offset: (currentPage - 1) * entriesPerPage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateEventSummaryTable(data.data);
            setupPagination(data.totalCount);
        } else {
            alert(data.message || 'Error fetching event summary.');
        }
    })
    .catch(error => console.error('Error fetching event summary:', error));
}

function populateEventSummaryTable(data) {
    const tableBody = document.getElementById('event-summary-table').querySelector('tbody');
    tableBody.innerHTML = ""; // Clear previous data

    if (data && data.length > 0) {
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${row.EventName}</td><td>${row.NumberOfGuests}</td>`;
            tableBody.appendChild(tr);
        });
    } else {
        const tr = document.createElement('tr');
        tr.innerHTML = "<td colspan='2'>No event summary data available.</td>";
        tableBody.appendChild(tr);
    }
}

function setupPagination(totalCount) {
    const totalPages = Math.ceil(totalCount / entriesPerPage);
    const paginationContainer = document.querySelector('.pagination div');
    paginationContainer.innerHTML = '';  // Clear existing buttons

    const prevButton = document.createElement('button');
    prevButton.classList.add('page-btn');
    prevButton.textContent = 'Prev';
    prevButton.disabled = currentPage === 1;
    prevButton.addEventListener('click', () => {
        currentPage--;
        fetchEventSummary();
    });
    paginationContainer.appendChild(prevButton);

    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement('button');
        pageButton.classList.add('page-btn');
        pageButton.textContent = i;
        pageButton.classList.toggle('active', i === currentPage);
        pageButton.addEventListener('click', () => {
            currentPage = i;
            fetchEventSummary();
        });
        paginationContainer.appendChild(pageButton);
    }

    const nextButton = document.createElement('button');
    nextButton.classList.add('page-btn');
    nextButton.textContent = 'Next';
    nextButton.disabled = currentPage === totalPages;
    nextButton.addEventListener('click', () => {
        currentPage++;
        fetchEventSummary();
    });
    paginationContainer.appendChild(nextButton);
}

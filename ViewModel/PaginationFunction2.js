class PaginationFunction2 {
    constructor(apiUrl, tableSelector, paginationSelector) {
        this.apiUrl = apiUrl;
        this.tableSelector = tableSelector;
        this.paginationSelector = paginationSelector;

        this.currentPage = 1;
        this.entriesPerPage = 10;
        this.totalEntries = 0;  // Track total entries for real-time updates

        document.addEventListener('DOMContentLoaded', () => {
            this.fetchEventSummary();
        });
    }

    async fetchEventSummary() {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get_event_summary',
                    limit: this.entriesPerPage,
                    offset: (this.currentPage - 1) * this.entriesPerPage,
                }),
            });

            const data = await response.json();

            if (data.success) {
                this.populateEventSummaryTable(data.data);
                this.setupPagination(data.totalCount, data.startIndex, data.endIndex);
            } else {
                alert(data.message || 'Error fetching event summary.');
            }
        } catch (error) {
            console.error('Error fetching event summary:', error);
        }
    }

    populateEventSummaryTable(data) {
        const tableBody = document.querySelector(this.tableSelector).querySelector('tbody');
        tableBody.innerHTML = ''; // Clear previous data

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

    setupPagination(totalCount, startIndex, endIndex) {
        const totalPages = Math.ceil(totalCount / this.entriesPerPage);
        const paginationContainer = document.querySelector(this.paginationSelector);
        paginationContainer.innerHTML = ''; // Clear existing buttons
    
        const createButton = (text, disabled, onClick) => {
            const button = document.createElement('button');
            button.classList.add('page-btn');
            button.textContent = text;
            button.disabled = disabled;
            button.addEventListener('click', onClick);
            return button;
        };
    
        // Previous button
        const prevButton = createButton('Prev', this.currentPage === 1, () => {
            this.currentPage--;
            this.fetchEventSummary();
        });
        paginationContainer.appendChild(prevButton);
    
        // Page number buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = createButton(
                i,
                false,
                () => {
                    this.currentPage = i;
                    this.fetchEventSummary();
                }
            );
            if (i === this.currentPage) {
                pageButton.classList.add('active');
            }
            paginationContainer.appendChild(pageButton);
        }
    
        // Next button
        const nextButton = createButton('Next', this.currentPage === totalPages, () => {
            this.currentPage++;
            this.fetchEventSummary();
        });
        paginationContainer.appendChild(nextButton);
    
        // Ensure the "Showing X to Y of Z entries" text is at the right place
        const paginationInfo = document.querySelector("#pagination-info");
        paginationInfo.textContent = `Showing ${startIndex} to ${endIndex} of ${totalCount} entries`;
    }
}

// Instantiate the class with appropriate selectors and API URL
new PaginationFunction2(
    '/SIA_Project/Model/bookingAPI2.php', // API URL
    '#event-summary-table',              // Table selector
    '.pagination div'                    // Pagination container selector
);

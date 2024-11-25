class PaginationFunction2 {
    constructor(apiUrl, tableSelector, paginationSelector) {
        this.apiUrl = apiUrl;
        this.tableSelector = tableSelector;
        this.paginationSelector = paginationSelector;

        this.currentPage = 1;
        this.entriesPerPage = 10;
        this.searchTerm = '';
        this.totalEntries = 0;

        this.initEventListeners();
        this.fetchEventSummary();
    }
    initEventListeners() {
        document.getElementById('search').addEventListener('input', (event) => {
            this.searchTerm = event.target.value.trim();
            this.currentPage = 1;
            this.fetchEventSummary();
        });

        document.getElementById('entries').addEventListener('change', (event) => {
            this.entriesPerPage = parseInt(event.target.value, 10);
            this.currentPage = 1;
            this.fetchEventSummary();
        });
    }

    async fetchEventSummary() {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: this.searchTerm ? 'search_event_summary' : 'get_event_summary',
                    search: this.searchTerm,
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
        tableBody.innerHTML = '';

        if (data && data.length > 0) {
            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${row.EventName}</td><td>${row.NumberOfGuests}</td>`;
                tableBody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = "<td colspan='2'>No matching records found.</td>";
            tableBody.appendChild(tr);
        }
    }

    setupPagination(totalCount, startIndex, endIndex) {
        const totalPages = Math.ceil(totalCount / this.entriesPerPage);
        const paginationContainer = document.querySelector(this.paginationSelector);
        paginationContainer.innerHTML = '';

        const createButton = (text, disabled, onClick) => {
            const button = document.createElement('button');
            button.textContent = text;
            button.classList.add('page-btn');
            button.disabled = disabled;
            if (text.toString() === this.currentPage.toString()) {
                button.classList.add("active");
            }
        
            button.addEventListener("click", onClick);
            return button;
        }

        paginationContainer.appendChild(
            createButton('Prev', this.currentPage === 1, () => {
                this.currentPage--;
                this.fetchEventSummary();
            })
        );

        for (let i = 1; i <= totalPages; i++) {
            const pageButton = createButton(i, i === this.currentPage, () => {
                this.currentPage = i;
                this.fetchEventSummary();
            });
            paginationContainer.appendChild(pageButton);
        }

        paginationContainer.appendChild(
            createButton('Next', this.currentPage === totalPages, () => {
                this.currentPage++;
                this.fetchEventSummary();
            })
        );

        document.querySelector('#pagination-info').textContent = `Showing ${startIndex} to ${endIndex} of ${totalCount} entries`;
    }
}

new PaginationFunction2('/SIA_Project/Model/bookingAPI2.php', '#event-summary-table', '.pagination div');
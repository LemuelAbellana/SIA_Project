class PaginationFunction3 {
    constructor(apiUrl, tableSelector, paginationSelector) {
        this.apiUrl = apiUrl;
        this.tableSelector = tableSelector;
        this.paginationSelector = paginationSelector;
        this.limit = 10;
        this.offset = 0;
    }

    async fetchBookings(limit = this.limit, offset = this.offset, search = '') {
        try {
            const response = await fetch(`${this.apiUrl}?limit=${limit}&offset=${offset}&search=${encodeURIComponent(search)}`);
            if (!response.ok) {
                console.error("Failed to fetch data. Status:", response.status);
                return;
            }
    
            const result = await response.json();
    
            if (result.status === "success") {
                this.populateTable(result.data);
                this.updatePagination(result.total_count, limit, offset);
            } else {
                console.error("Error in API response:", result);
            }
        } catch (error) {
            console.error("Error during API fetch:", error);
        }
    }
    
    populateTable(data) {
        const tableBody = document.querySelector(`${this.tableSelector} tbody`);
        tableBody.innerHTML = "";

        data.forEach((row) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${row.event_name}</td>
                <td>${row.total_bookings}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    updatePagination(totalCount, limit, offset) {
        const totalPages = Math.ceil(totalCount / limit);
        const paginationContainer = document.querySelector(`${this.paginationSelector} div`);
        const startIndex = offset + 1;
        const endIndex = Math.min(offset + limit, totalCount);
        const currentPage = Math.floor(offset / limit) + 1;
    
        paginationContainer.innerHTML = ''; // Clear existing buttons
    
        // Helper function to create buttons
        const createButton = (text, disabled, onClick) => {
            const button = document.createElement('button');
            button.classList.add('page-btn');
            button.textContent = text;
            button.disabled = disabled;
            button.addEventListener('click', onClick);
            return button;
        };
    
        // Previous button
        const prevButton = createButton('Prev', currentPage === 1, () => {
            this.changePage(limit, (currentPage - 2) * limit);
        });
        paginationContainer.appendChild(prevButton);
    
        // Page number buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = createButton(
                i,
                false,
                () => {
                    this.changePage(limit, (i - 1) * limit);
                }
            );
            if (i === currentPage) {
                pageButton.classList.add('active');
            }
            paginationContainer.appendChild(pageButton);
        }
    
        // Next button
        const nextButton = createButton('Next', currentPage === totalPages, () => {
            this.changePage(limit, currentPage * limit);
        });
        paginationContainer.appendChild(nextButton);
    
        // Display the "Showing X to Y of Z entries" text
        const paginationInfo = document.querySelector(`${this.paginationSelector} p`);
        paginationInfo.textContent = `Showing ${startIndex} to ${endIndex} of ${totalCount} entries`;
    }
    

    changePage(limit, offset) {
        this.limit = limit;
        this.offset = offset;
        this.fetchBookings();
    }

    initialize() {
        this.fetchBookings();
    
        // Add event listener for the search bar
        const searchInput = document.querySelector("#search");
        if (searchInput) {
            searchInput.addEventListener("input", (e) => {
                const searchTerm = e.target.value;
                this.fetchBookings(this.limit, 0, searchTerm); // Reset offset to 0 for new search
            });
        }
    }
}    

// Instantiate and initialize the PaginationFunction3
const paginationFunction3 = new PaginationFunction3(
    "../../Model/bookingAPI3.php", // API URL
    ".custom-table",              // Table selector
    ".pagination"                 // Pagination selector
);

paginationFunction3.initialize();

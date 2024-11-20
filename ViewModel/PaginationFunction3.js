class PaginationFunction3 {
    constructor(apiUrl, tableSelector, paginationSelector) {
        this.apiUrl = apiUrl;
        this.tableSelector = tableSelector;
        this.paginationSelector = paginationSelector;
        this.limit = 10;
        this.offset = 0;
    }

    async fetchBookings(limit = this.limit, offset = this.offset) {
        try {
            const response = await fetch(`${this.apiUrl}?limit=${limit}&offset=${offset}`);
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
        const paginationInfo = document.querySelector(`${this.paginationSelector} p`);
        const paginationButtons = document.querySelector(`${this.paginationSelector} div`);
        const currentPage = Math.floor(offset / limit) + 1;
        const totalPages = Math.ceil(totalCount / limit);

        paginationInfo.textContent = `Showing ${offset + 1} to ${Math.min(offset + limit, totalCount)} of ${totalCount} entries`;

        paginationButtons.innerHTML = `
            <button ${currentPage === 1 ? "disabled" : ""} onclick="paginationFunction3.changePage(${limit}, ${(currentPage - 2) * limit})">Prev</button>
        `;

        for (let i = 1; i <= totalPages; i++) {
            paginationButtons.innerHTML += `
                <button ${i === currentPage ? "class='active'" : ""} onclick="paginationFunction3.changePage(${limit}, ${(i - 1) * limit})">${i}</button>
            `;
        }

        paginationButtons.innerHTML += `
            <button ${currentPage === totalPages ? "disabled" : ""} onclick="paginationFunction3.changePage(${limit}, ${currentPage * limit})">Next</button>
        `;
    }

    changePage(limit, offset) {
        this.limit = limit;
        this.offset = offset;
        this.fetchBookings();
    }

    initialize() {
        this.fetchBookings();
    }
}

// Instantiate and initialize the PaginationFunction3
const paginationFunction3 = new PaginationFunction3(
    "../../Model/bookingAPI3.php", // API URL
    ".custom-table",              // Table selector
    ".pagination"                 // Pagination selector
);

paginationFunction3.initialize();

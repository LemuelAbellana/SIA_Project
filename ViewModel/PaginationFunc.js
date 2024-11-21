class PaginationFunc {
    constructor(apiUrl) {
        this.apiUrl = apiUrl;
        this.currentPage = 1;
        this.entries = parseInt(document.getElementById("entries").value);
        this.searchTerm = "";
        this.data = [];

        this.initEventListeners();
        this.fetchData();
    }

    fetchData() {
        const url = this.createApiUrl();
        
        fetch(url)
            .then(this.handleResponse)
            .then(this.handleData.bind(this))
            .catch(this.handleError);
    }

    handleResponse(response) {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    }

    handleData(data) {
        this.data = data.bookings || [];
        this.populateTable(this.data);
        this.updatePagination(data.totalEntries, this.entries, data.startIndex, data.endIndex);
    }

    handleError(error) {
        console.error("Error fetching data:", error);
    }

    createApiUrl() {
        return `${this.apiUrl}?page=${this.currentPage}&entries=${this.entries}&search=${this.searchTerm}`;
    }

    populateTable(bookings) {
        const tbody = document.querySelector("table tbody");
        tbody.innerHTML = bookings.length === 0 
            ? "<tr><td colspan='10'>No records found</td></tr>"
            : bookings.map(this.createRow).join("");

        this.initViewEditButtons();
    }

    createRow(booking) {
        return `
            <tr>
                <td>${booking.booking_id}</td>
                <td>${booking.customer_id}</td>
                <td>${booking.name || "N/A"}</td> <!-- Handling missing name -->
                <td>${booking.email || "N/A"}</td> <!-- Handling missing email -->
                <td>${booking.event_type || "N/A"}</td> <!-- Handling missing event type -->
                <td>${booking.arrival_date || "N/A"}</td> <!-- Handling missing arrival date -->
                <td>${booking.leaving_date || "N/A"}</td> <!-- Handling missing leaving date -->
                <td>${booking.number_of_people || "N/A"}</td> <!-- Handling missing number_of_people -->
                <td>${booking.contact_number || "N/A"}</td> <!-- Handling missing contact number -->
                <td>
                    <button class="viewBtn" data-booking-id="${booking.booking_id}"><i class="fa fa-eye"></i></button>
                    <button class="editBtn" data-booking-id="${booking.booking_id}"><i class="fa fa-edit"></i></button>
                    <button class="deleteBtn" data-booking-id="${booking.booking_id}"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;
    }

    initViewEditButtons() {
        const tableBody = document.querySelector("table tbody");
        tableBody.addEventListener("click", (e) => {
            const button = e.target.closest("button");
            if (!button) return;

            const bookingId = button.dataset.bookingId;
            if (button.classList.contains("viewBtn")) {
                this.viewBooking(bookingId);
            } else if (button.classList.contains("editBtn")) {
                this.editBooking(bookingId);
            } else if (button.classList.contains("deleteBtn")) {
                this.deleteBooking(bookingId);
            }
        });
    }

    viewBooking(bookingId) {
        fetch(`/SIA_Project/Model/bookingAPI.php?id=${bookingId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    this.openViewPopup(data);
                }
            })
            .catch(error => {
                console.error("Error fetching booking data:", error);
            });
    }

    editBooking(bookingId) {
        fetch(`/SIA_Project/Model/bookingAPI.php?id=${bookingId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    this.openEditPopup(data);
                }
            })
            .catch(error => {
                console.error("Error fetching booking data:", error);
            });
    }

    deleteBooking(bookingId) {
        if (confirm("Are you sure you want to delete this booking?")) {
            fetch(`/SIA_Project/Model/bookingAPI.php`, {
                method: "DELETE",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${bookingId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert("Booking deleted successfully.");
                    this.fetchData();
                }
            })
            .catch(error => {
                console.error("Error deleting booking:", error);
            });
        }
    }

    openViewPopup(booking) {
        this.populatePopup("view", booking);
        this.togglePopup("view", true);
    }

    openEditPopup(booking) {
        this.populatePopup("edit", booking);
        this.togglePopup("edit", true);
    }

    populatePopup(type, booking) {
        const popupId = type === "view" ? "view-Form" : "edit-Form";
        const form = document.getElementById(popupId).querySelector("form");

        // Clear previous values and populate new data
        form.querySelectorAll('input, select, textarea').forEach((element) => {
            const key = element.id;
            if (booking.hasOwnProperty(key)) {
                if (type === "view") {
                    element.value = booking[key] || ''; // View mode, just display the data
                    element.disabled = true; // Disable fields in view mode
                } else {
                    element.value = booking[key] || ''; // Edit mode, allow editing
                    element.disabled = false;
                }
            }
        });
    }

    togglePopup(type, isOpen) {
        const popup = document.getElementById(`${type}-Form`);
        popup.style.display = isOpen ? "flex" : "none";

        // Close popup button click
        const closeButton = document.getElementById(`close${type.charAt(0).toUpperCase() + type.slice(1)}`);
        closeButton.removeEventListener("click", this.closePopupListener); // Remove old listener
        closeButton.addEventListener("click", () => this.togglePopup(type, false)); // Add new listener
    }

    updatePagination(totalEntries, entriesPerPage, startIndex, endIndex) {
        const paginationContainer = document.querySelector(".pagination div");
        const paginationInfo = document.querySelector(".pagination p");
        if (!paginationContainer || !paginationInfo) return;

        paginationContainer.innerHTML = "";

        const totalPages = Math.ceil(totalEntries / entriesPerPage);
        if (totalPages === 0) {
            paginationContainer.innerHTML = "<p>No pages to display</p>";
            return;
        }

        paginationInfo.textContent = `Showing ${startIndex} to ${endIndex} of ${totalEntries} entries`;

        paginationContainer.appendChild(this.createPaginationButton("Prev", this.currentPage > 1, () => {
            this.currentPage--;
            this.fetchData();
        }));

        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.appendChild(this.createPaginationButton(i, this.currentPage === i, () => {
                this.currentPage = i;
                this.fetchData();
            }));
        }

        paginationContainer.appendChild(this.createPaginationButton("Next", this.currentPage === totalPages, () => {
            this.currentPage++;
            this.fetchData();
        }));
    }

    createPaginationButton(text, isDisabled, onClick) {
        const button = document.createElement("button");
        button.textContent = text;
        button.classList.add("page-btn");
        button.disabled = isDisabled;
        button.addEventListener("click", onClick);
        return button;
    }

    initEventListeners() {
        document.getElementById("search").addEventListener("input", (event) => {
            this.searchTerm = event.target.value;
            this.fetchData();
        });

        document.getElementById("entries").addEventListener("change", (event) => {
            this.entries = parseInt(event.target.value);
            this.fetchData();
        });
    }
}

const paginationFunc = new PaginationFunc('/SIA_Project/Model/bookingAPI.php');

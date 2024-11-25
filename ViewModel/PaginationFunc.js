class PaginationFunc {
    constructor(apiUrl) {
        this.apiUrl = apiUrl;
        this.currentPage = 1;
        this.entries = parseInt(document.getElementById("entries")?.value) || 10; // Default to 10 if element not found
        this.searchTerm = "";
        this.data = [];

        this.initEventListeners();
        this.fetchData();
    }

    fetchData() {
        const url = this.createApiUrl();
        fetch(url)
            .then(this.handleResponse)
            .then((data) => this.handleData(data))
            .catch((error) => this.handleError(error));
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
        this.updatePagination(data.totalEntries || 0, this.entries);
    }

    handleError(error) {
        console.error("Error fetching data:", error);
        const tbody = document.querySelector("table tbody");
        tbody.innerHTML = "<tr><td colspan='10'>Error loading data.</td></tr>";
    }

    createApiUrl() {
        return `${this.apiUrl}?page=${this.currentPage}&entries=${this.entries}&search=${this.searchTerm}`;
    }
    validateBookingDetails(booking) {
        const errors = [];
    
        // Validate contact number format
        const contactNumberRegex = /^\d{11}$/;
        if (!contactNumberRegex.test(booking.contact_number)) {
            errors.push("Contact number must be exactly 11 digits.");
        }
    
        // Validate number of people
        const numPeople = parseInt(booking.number_of_people, 10);
        if (isNaN(numPeople) || numPeople < 1 || numPeople > 1000) {
            errors.push("Number of people must be between 1 and 1000.");
        }
    
        // Validate date order
        const arrivalDate = new Date(booking.arrival_date);
        const leavingDate = new Date(booking.leaving_date);
        if (arrivalDate >= leavingDate) {
            errors.push("Leaving date must be after arrival date.");
        }
    
        // Check for overlapping dates with existing bookings
        this.data.forEach((existingBooking) => {
            // Skip the booking being edited by comparing booking_id
            if (parseInt(booking.booking_id, 10) !== parseInt(existingBooking.booking_id, 10)) {
                const existingArrival = new Date(existingBooking.arrival_date);
                const existingLeaving = new Date(existingBooking.leaving_date);
    
                // Check for overlap
                const isOverlapping =
                    (arrivalDate < existingLeaving && leavingDate > existingArrival);
                if (isOverlapping) {
                    errors.push(
                        `Booking dates overlap with an existing booking (ID: ${existingBooking.booking_id}).`
                    );
                }
            }
        });
    
        return errors;
    }
    

    populateTable(bookings) {
        const tbody = document.querySelector("table tbody");
    
        // Sort bookings based on booking_id
        const sortedBookings = bookings.sort((a, b) => {
            return a.booking_id - b.booking_id; // Ascending order
        });
    
        tbody.innerHTML = sortedBookings.length === 0
            ? "<tr><td colspan='10'>No records found</td></tr>"
            : sortedBookings.map(this.createRow).join("");
    
        this.initViewEditButtons();
    }
    

    createRow(booking) {
        return `
            <tr>
                <td>${booking.booking_id || "N/A"}</td>
                <td>${booking.customer_id || "N/A"}</td>
                <td>${booking.name || "N/A"}</td>
                <td>${booking.email || "N/A"}</td>
                <td>${booking.event_type || "N/A"}</td>
                <td>${booking.arrival_date || "N/A"}</td>
                <td>${booking.leaving_date || "N/A"}</td>
                <td>${booking.number_of_people || "N/A"}</td>
                <td>${booking.contact_number || "N/A"}</td>
                <td>
                    <button class="viewBtn" data-booking-id="${booking.booking_id}"><i class="fa fa-eye"></i></button>
                    <button class="editBtn" data-booking-id="${booking.booking_id}"><i class="fa fa-edit"></i></button>
                    <button class="deleteBtn" data-booking-id="${booking.booking_id}"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;
    }

    updatePagination(totalEntries, entriesPerPage) {
        const paginationContainer = document.querySelector(".pagination div");
        const paginationInfo = document.querySelector(".pagination p");

        paginationContainer.innerHTML = "";

        const totalPages = Math.ceil(totalEntries / entriesPerPage);
        const startIndex = totalEntries === 0 ? 0 : (this.currentPage - 1) * entriesPerPage + 1;
        const endIndex = Math.min(this.currentPage * entriesPerPage, totalEntries);

        paginationInfo.textContent = `Showing ${startIndex} to ${endIndex} of ${totalEntries} entries`;

        // Prev Button
        paginationContainer.appendChild(this.createPaginationButton("Prev", this.currentPage === 1, () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.fetchData();
            }
        }));

        // Page Numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationContainer.appendChild(this.createPaginationButton(i, this.currentPage === i, () => {
                this.currentPage = i;
                this.fetchData();
            }));
        }

        // Next Button
        paginationContainer.appendChild(this.createPaginationButton("Next", this.currentPage === totalPages, () => {
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.fetchData();
            }
        }));
    }
    setMinDates() {
        const today = new Date().toISOString().split("T")[0]; // Get today's date in YYYY-MM-DD format
        const arrivalInput = document.querySelector('input[name="arrival_date"]');
        const leavingInput = document.querySelector('input[name="leaving_date"]');

        if (arrivalInput) arrivalInput.setAttribute("min", today);
        if (leavingInput) leavingInput.setAttribute("min", today);
    }

    createPaginationButton(text, isDisabled, onClick) {
        const button = document.createElement("button");
        button.textContent = text;
        button.classList.add("page-btn");
        button.disabled = isDisabled;
    
        // Apply 'active' class to the current page
        if (text.toString() === this.currentPage.toString()) {
            button.classList.add("active");
        }
    
        button.addEventListener("click", onClick);
        return button;
    }
    
    validateBookingDetails(booking) {
        const errors = [];
    
        // Validate contact number format
        const contactNumberRegex = /^\d{11}$/;
        if (!contactNumberRegex.test(booking.contact_number)) {
            errors.push("Contact number must be exactly 11 digits.");
        }
    
        // Validate number of people
        const numPeople = parseInt(booking.number_of_people, 10);
        if (isNaN(numPeople) || numPeople < 1 || numPeople > 1000) {
            errors.push("Number of people must be between 1 and 1000.");
        }
    
        // Validate date order
        const arrivalDate = new Date(booking.arrival_date);
        const leavingDate = new Date(booking.leaving_date);
        if (arrivalDate >= leavingDate) {
            errors.push("Leaving date must be after arrival date.");
        }
    
        // Check for overlapping dates with existing bookings
        this.data.forEach(existingBooking => {
            if (parseInt(booking.booking_id, 10) !== parseInt(existingBooking.booking_id, 10)) { // Exclude the current booking
                const existingArrival = new Date(existingBooking.arrival_date);
                const existingLeaving = new Date(existingBooking.leaving_date);
    
                // Check for any overlap
                const isOverlapping =
                    arrivalDate < existingLeaving && leavingDate > existingArrival;
                if (isOverlapping) {
                    errors.push(
                        `Booking dates overlap with an existing booking (ID: ${existingBooking.booking_id}).`
                    );
                }
            }
        });
    
        return errors;
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

    fetchBookingDetails(bookingId, callback) {
        fetch(`${this.apiUrl}?id=${bookingId}`)
            .then(this.handleResponse)
            .then(callback)
            .catch((error) => {
                console.error(`Error fetching booking ${bookingId}:`, error);
                alert("An error occurred while fetching booking details.");
            });
    }

    viewBooking(bookingId) {
        console.log("Fetching details for booking ID:", bookingId);
        this.fetchBookingDetails(bookingId, (response) => {
            console.log("API Response:", response); // Debug log
            
            if (response && response.booking) {
                const details = response.booking;
                
                // Update DOM elements with booking details
                const elements = {
                    "viewBookingBookingId": details.booking_id,
                    "viewBookingName": details.name,
                    "viewBookingEmail": details.email,
                    "viewBookingContactNumber": details.contact_number,
                    "viewBookingEventType": details.event_type,
                    "viewBookingArrivalDate": details.arrival_date,
                    "viewBookingLeavingDate": details.leaving_date,
                    "viewBookingNumberOfPeople": details.number_of_people
                };
    
                // Update each element, with error handling
                for (const [elementId, value] of Object.entries(elements)) {
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.textContent = value || "N/A";
                    } else {
                        console.warn(`Element ${elementId} not found`);
                    }
                }
    
                // Show popup
                const popup = document.getElementById("view-Form");
                if (popup) {
                    popup.style.display = "flex";
                } else {
                    console.error("View popup element not found");
                }
            } else {
                console.error("Invalid API response structure:", response);
                alert("Failed to fetch booking details. Please try again.");
            }
        });
    }

    editBooking(bookingId) {
        this.fetchBookingDetails(bookingId, (response) => {
            if (response && response.booking) {
                const booking = response.booking;
                const editForm = document.getElementById("edit-Form");

                if (editForm) {
                    editForm.style.display = "flex";
                    const formFields = [
                        "booking_id",
                        "name",
                        "email",
                        "contact_number",
                        "event_type",
                        "arrival_date",
                        "leaving_date",
                        "number_of_people",
                    ];

                    formFields.forEach((field) => {
                        const input = editForm.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.value = booking[field] || "";
                        }
                    });

                    this.setMinDates(); // Disable past dates
                    const editFormElement = editForm.querySelector("form");
                    if (editFormElement) {
                        editFormElement.onsubmit = (e) => this.handleEditSubmit(e);
                    }
                }
            } else {
                Swal.fire("Error", "Failed to load booking details", "error");
            }
        });
    }


    handleEditSubmit(event) {
        event.preventDefault();
    
        const form = event.target;
        const formData = new FormData(form);
        const bookingData = Object.fromEntries(formData.entries());
    
        // Validate booking details
        const validationErrors = this.validateBookingDetails(bookingData);
    
        if (validationErrors.length > 0) {
            // Display validation errors in a modal or alert
            Swal.fire(
                'Validation Error',
                validationErrors.join('<br>'),
                'error'
            );
            return; // Stop the submission if validation fails
        }
    
        // Proceed with the update if validation passes
        fetch(this.apiUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData),
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then((data) => {
                        throw new Error(data.message || 'Failed to update booking');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message and refresh data
                    Swal.fire(
                        'Success!',
                        data.message || 'Booking details updated successfully.',
                        'success'
                    ).then(() => {
                        this.fetchData(); // Reload updated data
                        document.getElementById("edit-Form").style.display = "none"; // Close the form
                    });
                } else {
                    throw new Error(data.message || 'Unexpected error occurred');
                }
            })
            .catch(error => {
                // Handle errors during the update process
                console.error("Update failed:", error);
                Swal.fire(
                    'Error',
                    error.message || 'An error occurred while updating the booking.',
                    'error'
                );
            });
    }    
    
    // Delete the booking
    deleteBooking(bookingId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${this.apiUrl}?id=${bookingId}`, {
                    method: 'DELETE'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message || 'Booking has been deleted.',
                            icon: 'success'
                        }).then(() => {
                            this.fetchData(); // Refresh the data
                        });
                    } else {
                        throw new Error(data.message || 'Failed to delete booking.');
                    }
                })
                .catch(error => {
                    console.error("Error deleting booking:", error);
                    Swal.fire(
                        'Error!',
                        error.message || 'An error occurred while deleting the booking.',
                        'error'
                    );
                });
            }
        });
    }

    // Initialize event listeners
    initEventListeners() {
        // Existing event listeners
        document.getElementById("search").addEventListener("input", (event) => {
            this.searchTerm = event.target.value;
            this.currentPage = 1;
            this.fetchData();
        });
    
        document.getElementById("entries").addEventListener("change", () => {
            this.entries = parseInt(document.getElementById("entries").value);
            this.currentPage = 1;
            this.fetchData();
        });

        // Close buttons for popups
        const closeButtons = document.querySelectorAll('.close-btn, .close');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const popup = button.closest('.popup-form, .view-form, .edit-form, .modal');
                if (popup) {
                    popup.style.display = 'none';
                }
            });
        });

        // Close popup when clicking outside
        window.addEventListener('click', (event) => {
            const popups = document.querySelectorAll('.popup-form, .modal');
            popups.forEach(popup => {
                if (event.target === popup) {
                    popup.style.display = 'none';
                }
            });
        });
    }

    initPopupCloseListener(formId) {
        const closeBtn = document.getElementById(`close${formId.charAt(0).toUpperCase() + formId.slice(1)}`);
        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                const popup = document.getElementById(formId);
                if (popup) {
                    popup.style.display = "none"; // Hide the popup
                } else {
                    console.error(`Popup with ID "${formId}" not found.`);
                }
            });
        } else {
            console.error(`Close button for popup "${formId}" not found.`);
        }
    }
}

// Create a new instance of PaginationFunc
const apiUrl = "../../Model/bookingAPI.php"; 
new PaginationFunc(apiUrl);

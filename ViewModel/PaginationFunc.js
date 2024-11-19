let currentPage = 1;

function fetchBookings(searchTerm = "") {
    const entries = document.getElementById("entries").value;
    const url = `/SIA_Project/Model/bookingAPI.php?page=${currentPage}&entries=${entries}&search=${searchTerm}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            populateTable(data.bookings);
            updatePagination(data.totalEntries, entries);
        })
        .catch(error => {
            console.error("Error fetching bookings:", error);
        });
}

function populateTable(bookings) {
    const tbody = document.querySelector("table tbody");
    tbody.innerHTML = ""; // Clear existing rows

    if (bookings.length === 0) {
        tbody.innerHTML = "<tr><td colspan='10'>No records found</td></tr>";
        return;
    }

    bookings.forEach(booking => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${booking.booking_id}</td>
            <td>${booking.customer_id}</td>
            <td>${booking.name}</td>
            <td>${booking.email}</td>
            <td>${booking.event_type}</td>
            <td>${booking.arrival_date}</td>
            <td>${booking.leaving_date}</td>
            <td>${booking.number_of_people}</td>
            <td>${booking.contact_number}</td>
            <td>
                <button onclick="viewBooking(${booking.booking_id})"><i class="fa fa-eye"></i></button>
                <button onclick="editBooking(${booking.booking_id})"><i class="fa fa-edit"></i></button>
                <button onclick="deleteBooking(${booking.booking_id})"><i class="fa fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updatePagination(totalEntries, entriesPerPage) {
    const paginationContainer = document.querySelector(".pagination div");
    paginationContainer.innerHTML = ""; // Clear existing pagination

    const totalPages = Math.ceil(totalEntries / entriesPerPage);
    if (totalPages === 0) {
        paginationContainer.innerHTML = "<p>No pages to display</p>";
        return;
    }

    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement("button");
        button.textContent = i;
        button.classList.add("page-btn");
        if (i === currentPage) button.classList.add("active");

        button.onclick = () => {
            currentPage = i;
            fetchBookings();
        };

        paginationContainer.appendChild(button);
    }
}

// Event listeners for search and entries per page
document.getElementById("search").addEventListener("input", e => fetchBookings(e.target.value));
document.getElementById("entries").addEventListener("change", () => fetchBookings());

// Initial fetch
fetchBookings();

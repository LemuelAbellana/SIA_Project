const API_URL = "../../Model/bookingAPI3.php"; // Adjusted path

// Function to fetch data and populate the table
async function fetchBookings(limit = 10, offset = 0) {
    try {
        const response = await fetch(`${API_URL}?limit=${limit}&offset=${offset}`);
        if (!response.ok) {
            console.error("Failed to fetch data. Status:", response.status);
            return;
        }

        const result = await response.json();

        if (result.status === "success") {
            populateTable(result.data);
            updatePagination(result.total_count, limit, offset);
        } else {
            console.error("Error in API response:", result);
        }
    } catch (error) {
        console.error("Error during API fetch:", error);
    }
}

// Function to populate the table
function populateTable(data) {
    const tableBody = document.querySelector(".custom-table tbody");
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

// Function to handle pagination
function updatePagination(totalCount, limit, offset) {
  const pagination = document.querySelector(".pagination p");
  const currentPage = Math.floor(offset / limit) + 1;
  const totalPages = Math.ceil(totalCount / limit);

  pagination.textContent = `Showing ${offset + 1} to ${
    Math.min(offset + limit, totalCount)
  } of ${totalCount} entries`;

  const paginationButtons = document.querySelector(".pagination div");
  paginationButtons.innerHTML = `
    <button onclick="fetchBookings(${limit}, ${(currentPage - 2) * limit})" ${
    currentPage === 1 ? "disabled" : ""
  }>Prev</button>
  `;

  for (let i = 1; i <= totalPages; i++) {
    paginationButtons.innerHTML += `
      <button onclick="fetchBookings(${limit}, ${(i - 1) * limit})" ${
      i === currentPage ? "class='active'" : ""
    }>${i}</button>
    `;
  }

  paginationButtons.innerHTML += `
    <button onclick="fetchBookings(${limit}, ${currentPage * limit})" ${
    currentPage === totalPages ? "disabled" : ""
  }>Next</button>
  `;
}

// Initial fetch
fetchBookings();

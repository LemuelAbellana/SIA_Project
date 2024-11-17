const bookNowButton = document.querySelector('.submit-btn');
const bookingForm = document.getElementById('bookingForm');

bookNowButton.addEventListener('click', function (e) {
    e.preventDefault();

    const contactNumber = document.getElementById('contact_number').value.trim();
    const contactError = document.getElementById('contact_error');
    if (!/^\d{11}$/.test(contactNumber)) {
        contactError.textContent = "Invalid contact number. It must be an 11-digit number.";
        return;
    } else {
        contactError.textContent = "";
    }

    const numberOfPeople = parseInt(document.getElementById('number_of_people').value.trim(), 10);
    const peopleError = document.getElementById('people_error');
    if (isNaN(numberOfPeople) || numberOfPeople < 1 || numberOfPeople > 1000) {
        peopleError.textContent = "Please enter a number between 1 and 1000.";
        return;
    } else {
        peopleError.textContent = "";
    }

    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to proceed with booking this event?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, book it!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
        }
    })
});
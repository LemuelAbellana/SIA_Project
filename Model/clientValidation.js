document.addEventListener("DOMContentLoaded", function () {
    const bookingForm = document.querySelector("#bookingForm");

    if (bookingForm) {
        bookingForm.addEventListener("submit", function (event) {
            const contactNumber = document.getElementById("contact_number").value.trim();
            const numberOfPeople = document.getElementById("number_of_people").value.trim();
            let isValid = true;

            // Clear previous error messages
            document.getElementById("contact_error").textContent = "";
            document.getElementById("people_error").textContent = "";

            // Validate contact number format (e.g., an 11-digit number)
            if (!/^\d{11}$/.test(contactNumber)) {
                document.getElementById("contact_error").textContent = "Please enter a valid 11-digit contact number.";
                isValid = false;
            }

            // Validate number of people (should be a reasonable number, e.g., 1 to 1000)
            if (numberOfPeople < 1 || numberOfPeople > 1000) {
                document.getElementById("people_error").textContent = "Please enter a number between 1 and 1000.";
                isValid = false;
            }

            // Prevent form submission if validation fails
            if (!isValid) {
                event.preventDefault();
                // Optionally, you can focus the first invalid input
                if (document.getElementById("contact_error").textContent !== "") {
                    document.getElementById("contact_number").focus();
                } else if (document.getElementById("people_error").textContent !== "") {
                    document.getElementById("number_of_people").focus();
                }
            }
        });
    }
});

// Handles registration logic and communicates with the backend
class RegisterViewModel {
    constructor(formElement) {
        this.formElement = formElement;
        this.init();
    }

    init() {
        this.formElement.addEventListener("submit", (event) => {
            event.preventDefault();
            this.handleSubmit();
        });
    }

    async handleSubmit() {
        const formData = new FormData(this.formElement);
        const data = {
            username: formData.get("username"),
            email: formData.get("email"),
            password: formData.get("password"),
            repeat_password: formData.get("repeat_password"),
        };

        try {
            const response = await fetch("../Model/RegisterController.php", { // Adjust base URL as needed
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });            

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    title: "Success!",
                    text: "You are registered successfully.",
                    icon: "success",
                }).then(() => {
                    window.location.href = "loginpage.html";
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: result.errors.join("\n"),
                    icon: "error",
                });
            }
        } catch (error) {
            Swal.fire({
                title: "Error!",
                text: "An unexpected error occurred: " + error.message,
                icon: "error",
            });
        }
    }
}

// Initialize the ViewModel on the registration page
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("#register-form");
    if (form) {
        new RegisterViewModel(form);
    }
});

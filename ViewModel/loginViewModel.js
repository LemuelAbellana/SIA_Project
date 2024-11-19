class LoginViewModel {
    constructor(formSelector, usernameSelector, passwordSelector, actionUrl) {
        this.form = $(formSelector);
        this.usernameField = $(usernameSelector);
        this.passwordField = $(passwordSelector);
        this.actionUrl = actionUrl;

        this._initializeEvents();
    }

    _initializeEvents() {
        // Bind form submit event to the login process
        this.form.on('submit', (e) => {
            e.preventDefault(); // Prevent normal form submission
            this._handleLogin();
        });
    }

    _handleLogin() {
        const username = this.usernameField.val().trim();
        const password = this.passwordField.val().trim();

        // Validate the credentials
        if (!username || !password) {
            this._showError('Missing Credentials', 'Please enter both username and password.');
            return;
        }

        this._submitLoginRequest(username, password);
    }

    _submitLoginRequest(username, password) {
        $.ajax({
            url: this.actionUrl,  // Correct URL to loginController.php
            type: 'POST',
            data: {
                username: username, 
                password: password,
            },
            success: (response) => this._handleResponse(response),
            error: (xhr) => this._handleError(xhr),
        });
    }

    _handleResponse(response) {
        try {
            const result = typeof response === 'string' ? JSON.parse(response) : response;

            if (result.status === 'success') {
                this._showSuccess('Login Successful', 'Redirecting to admin panel...', result.redirect);
            } else {
                this._showError('Login Failed', result.message);
            }
        } catch (error) {
            this._showError('Error', 'Invalid server response.');
        }
    }

    _handleError(xhr) {
        console.error('AJAX Error:', xhr.responseText);
        this._showError('Request Failed', 'Could not connect to the server. Please try again later.');
    }

    _showError(title, message) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
        });
    }

    _showSuccess(title, message, redirectUrl) {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
        }).then(() => {
            window.location.href = redirectUrl;
        });
    }
}

// Initialize the LoginViewModel when document is ready
$(document).ready(function () {
    const loginViewModel = new LoginViewModel(
        '#login-form',
        '#username',
        '#password',
        '../Model/loginController.php' // Correctly point to your PHP controller
    );
});

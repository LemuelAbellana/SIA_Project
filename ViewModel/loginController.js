$(document).ready(function () {
    $('#login-form').on('submit', function (e) {
        e.preventDefault(); // Prevent normal form submission

        const username = $('#username').val().trim();  // Correct ID for username field
        const password = $('#password').val().trim();  // Correct ID for password field

        if (!username || !password) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Credentials',
                text: 'Please enter both username and password.',
            });
            return;
        }

        $.ajax({
            url: '../Database/login_action.php',
            type: 'POST',
            data: {
                username: username,
                password: password,
            },
            success: function (response) {
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;

                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful',
                            text: 'Redirecting to admin panel...',
                        }).then(() => {
                            window.location.href = result.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: result.message,
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Invalid server response.',
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Request Failed',
                    text: 'Could not connect to the server. Please try again later.',
                });
            },
        });
    });
});

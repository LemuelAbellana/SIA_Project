const showPassword = document.querySelector('#showPassword');
    const password = document.querySelector('#password');

    showPassword.addEventListener('change', function () {
        const type = showPassword.checked ? 'text' : 'password';
        password.setAttribute('type', type);
    });
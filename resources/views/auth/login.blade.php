<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <form id="login-form">
        <div>
            <label>Email</label>
            <input id="email" type="email" name="email">
        </div>
        <div>
            <label>Password</label>
            <input id="password" type="password" name="password">
        </div>
        <div id="login-error" style="color: red;"></div>
        <button type="submit">Login</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('login-form');
            const errorBox = document.getElementById('login-error');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                errorBox.textContent = '';

                try {
                    const response = await fetch('api/auth/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            email: document.getElementById('email').value,
                            password: document.getElementById('password').value,
                        }),
                    });

                    const data = await response.json();
                    
                    if (!response.ok) {
                        errorBox.textContent = data.message ?? 'Login failed.';
                        return;
                    }

                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('auth_user', JSON.stringify(data.user));

                    window.location.href = data.redirect ?? '/test';
                } catch (error) {
                    errorBox.textContent = 'Something went wrong.';
                    console.error(error);
                }
            });
        });
    </script>
</body>
</html>
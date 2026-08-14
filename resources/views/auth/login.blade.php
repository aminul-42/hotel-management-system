@extends('layouts.customer.app')

@section('title', 'Login')

@section('content')

<style>
    .login-wrapper {
        min-height: calc(100vh - 120px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        background: #f4f5f7;
    }

    .auth-box {
        background: #ffffff;
        width: 100%;
        max-width: 380px;
        padding: 40px 32px;
        border-radius: 10px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .auth-box h2 {
        text-align: center;
        margin-bottom: 24px;
        color: #1a1a1a;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        color: #333;
    }

    .form-group input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d5d7de;
        border-radius: 6px;
        font-size: 14px;
    }

    .form-group input:focus {
        outline: none;
        border-color: #4a6cf7;
    }

    .form-error {
        color: #e0245e;
        font-size: 13px;
        min-height: 18px;
        margin-bottom: 10px;
    }

    button[type="submit"] {
        width: 100%;
        padding: 11px;
        background: #4a6cf7;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        cursor: pointer;
    }

    button[type="submit"]:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    button[type="submit"]:hover:not(:disabled) {
        background: #3a5ce0;
    }
</style>

<div class="login-wrapper">
    <div class="auth-box">
        <h2>Login</h2>

        <form id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </div>

            <div class="form-error" id="loginError"></div>

            <button type="submit" id="loginBtn">
                Login
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const loginForm = document.getElementById('loginForm');
    const errorBox = document.getElementById('loginError');
    const loginBtn = document.getElementById('loginBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    loginForm.addEventListener('submit', function (e) {

        e.preventDefault();

        errorBox.textContent = '';

        loginBtn.disabled = true;
        loginBtn.textContent = 'Logging in...';

        const formData = new FormData(loginForm);
        const data = Object.fromEntries(formData.entries());

        fetch('{{ route("login.submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {

            const result = await response.json();

            if (!response.ok) {
                throw result;
            }

            window.location.href = result.redirect;

        })
        .catch(err => {

            const message = err.errors
                ? Object.values(err.errors).flat().join(' ')
                : 'Login failed. Please try again.';

            errorBox.textContent = message;

        })
        .finally(() => {

            loginBtn.disabled = false;
            loginBtn.textContent = 'Login';

        });

    });

});
</script>
@endpush
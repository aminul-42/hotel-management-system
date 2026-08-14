<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Adi Hotel International</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #1f2937;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .top-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            transition: .2s ease;
        }

        .btn-login {
            background: #2563eb;
        }

        .btn-login:hover {
            background: #1d4ed8;
        }

        .btn-dashboard {
            background: #16a34a;
        }

        .btn-dashboard:hover {
            background: #15803d;
        }

        .btn-logout {
            background: #dc2626;
            border: none;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: #b91c1c;
        }

        .hotel-name {
            font-size: 3rem;
            font-weight: bold;
            color: #1f2937;
            text-align: center;
        }

        @media (max-width: 768px) {
            .hotel-name {
                font-size: 2rem;
            }

            .top-buttons {
                top: 15px;
                right: 15px;
            }
        }
    </style>
</head>
<body>

    <div class="top-buttons">
        @guest
            <a href="{{ route('login') }}" class="btn btn-login">
                Login
            </a>
        @else
            <a href="{{ url('/dashboard') }}" class="btn btn-dashboard">
                Dashboard
            </a>

            <button id="logoutBtn" class="btn btn-logout">
                Logout
            </button>
        @endguest
    </div>

    <div class="hotel-name">
        Adi Hotel International
    </div>

    @auth
    <script>
        document.getElementById('logoutBtn').addEventListener('click', function () {
            fetch('{{ route("logout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                window.location.href = data.redirect;
            })
            .catch(() => {
                window.location.href = '{{ route("login") }}';
            });
        });
    </script>
    @endauth

</body>
</html>
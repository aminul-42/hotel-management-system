<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>frontdesk  - Hotel Management</title>
    <button id="logoutBtn">Logout</button>
</head>
<body>
    <div id="frontdesk -header">frontdesk  Header/Sidebar Placeholder</div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <main>
        @yield('content')
    </main>

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
            .then(res => res.json())
            .then(data => {
                window.location.href = data.redirect;
            });
    });
</script>
</body>
</html>
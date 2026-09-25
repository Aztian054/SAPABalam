<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LOKET 2026 — Sistem Pelayanan Pertanahan BMN Balam</title>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="bg-[#f4f6f9] font-sans text-[#334155] antialiased">
    <div id="root"></div>
</body>
</html>
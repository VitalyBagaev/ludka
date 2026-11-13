<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Лудомания') }}</title>
    @vite(['resources/js/main.js'])
</head>
<body class="min-vh-100">
    <div id="app"></div>
</body>
</html>

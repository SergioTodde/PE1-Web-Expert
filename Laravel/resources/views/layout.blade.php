<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/home.css', 'resources/js/app.js'])
    <title>@yield('title')</title>
</head>
<body>
<h1>Test</h1>
@include('partials._navigation')
<main>
    @yield('content')
</main>
</body>
</html>

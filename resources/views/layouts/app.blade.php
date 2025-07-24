<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ Vite::asset('resources/images/favicon.svg') }}">
    <title>@yield('title') - SGE-IFFarSA</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="bg-light">
    <x-toast />

    @yield('content')

    <x-spinner />
</body>

</html>

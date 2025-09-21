<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    @filamentStyles
    @vite('resources/css/app.css')
    
    <script>
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    </script>
</head>
<body class="font-sans antialiased bg-gray-950 text-white">
    <div class="min-h-screen bg-gray-950">
        {{ $slot }}
    </div>
    
    @filamentScripts
    @vite('resources/js/app.js')
</body>
</html>
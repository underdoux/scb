<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#020817">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <!-- Prevent Flash of Wrong Theme -->
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.querySelector('meta[name="theme-color"]').setAttribute('content', '#020817');
            } else {
                document.documentElement.classList.remove('dark');
                document.querySelector('meta[name="theme-color"]').setAttribute('content', '#ffffff');
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-white dark:bg-background-primary text-gray-900 dark:text-white">
        @inertia

        <div id="modal-target"></div>
        <div id="dropdown-target"></div>
    </body>
</html>

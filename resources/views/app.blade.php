<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Agreement') }}</title>

        <link rel="icon" type="image/png" href="/slice/assets/images/favicon.png">
        <link rel="shortcut icon" type="image/png" href="/slice/assets/images/favicon.png">
        <link rel="apple-touch-icon" href="/slice/assets/images/favicon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @unless(request()->is('admin*') || request()->is('settings*'))
            <link rel="stylesheet" href="/slice/assets/vendor/bootstrap/bootstrap.min.css">
            <link rel="stylesheet" href="/slice/assets/css/style.css">
        @endunless

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Lenevor') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <h2>Application {{ $exception ? 'experiencing problems' : 'up' }}</h2>
        <p>
            HTTP request received.

            <@if (defined('LENEVOR_START'))
                Response rendered in {{ round((microtime(true) - LENEVOR_START) * 1000) }}ms.
            <@endif
        </p>
    </div>
</body>
</html>
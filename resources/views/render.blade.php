<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meta['title'] }}</title>

    <!--
        Start: Favicon Generator Settings
        Reference: https://realfavicongenerator.net/
    -->
    <link rel="icon" type="image/png" href="favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg" />
    <link rel="shortcut icon" href="favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Perfect Order" />
    <link rel="manifest" href="/site.webmanifest" />
    <!-- End: Favicon Generator Settings  -->

    <!-- Dynamic Meta Tags (only for product pages or crawlers) -->
    @if (isset($meta) && !empty($meta))

        <!-- Open Graph Tags -->
        <meta property="og:url" content="{{ $meta['url'] }}">
        <meta property="og:type" content="{{ $meta['type'] }}">
        <meta property="og:image" content="{{ $meta['image'] }}">
        <meta property="og:title" content="{{ $meta['title'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">

        <!-- Conditional Additional Meta Tags -->
        @if (!empty($meta['sku']))
            <meta property="product:sku" content="{{ $meta['sku'] }}">
        @endif
        @if (!empty($meta['price']))
            <meta property="product:price:amount" content="{{ $meta['price'] }}">
        @endif
        @if (!empty($meta['currency']))
            <meta property="product:price:currency" content="{{ $meta['currency'] }}">
        @endif
        @if (!empty($meta['keywords']))
            <meta name="keywords" content="{{ $meta['keywords'] }}">
        @endif
        @if (!empty($meta['availability']))
            <meta property="product:availability" content="{{ $meta['availability'] }}">
        @endif

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $meta['image'] }}">
        <meta name="twitter:title" content="{{ $meta['title'] }}">
        <meta name="twitter:description" content="{{ $meta['description'] }}">

    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <router-view></router-view>
    </div>
</body>
</html>

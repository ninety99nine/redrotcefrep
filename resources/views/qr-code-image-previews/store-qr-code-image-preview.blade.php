<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>QR Code for {{ $store->name }}</title>

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Scan to access {{ $store->name }}">
    <meta property="og:description" content="Tap to open the store and explore products">
    <meta property="og:image" content="{{ $store->qr_code_file_path }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Card (Optional) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Scan to access {{ $store->name }}">
    <meta name="twitter:description" content="Tap to open the store and explore products">
    <meta name="twitter:image" content="{{ $store->qr_code_file_path }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">

        <div class="w-3/4 md:w-1/2 lg:w-1/4 bg-white space-y-4 py-8 px-4 shadow-sm rounded-xl flex flex-col items-center">

            <h1 class="text-center text-lg">Scan Me</h1>

            <img src="{{ $store->qr_code_file_path }}" alt="QR Code" class="w-32 h-32">

            <div class="w-full space-y-2">

                <h1 class="w-full text-center text-2xl truncate">{{ $store->name }}</h1>

                <p class="text-center text-xs text-gray-600">
                    Scan this QR Code to visit our store
                </p>

            </div>

        </div>

    </div>

</body>
</html>

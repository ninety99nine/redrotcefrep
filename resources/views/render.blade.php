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
    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Perfect Order" />

    <!-- Dynamic Manifest Link - For Progressive Web App (PWA) -->
    @if (isset($meta) && !empty($meta) && !empty($meta['alias']))
        <link rel="manifest" href="/{{ $meta['alias'] }}/manifest.json" />
    @else
        <link rel="manifest" href="/favicon/site.webmanifest" />
    @endif
    <!-- End: Favicon Generator Settings -->

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

        <!-- Google Analytics -->
        @if (!empty($meta['google_analytics_id']))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $meta['google_analytics_id'] }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ $meta['google_analytics_id'] }}');
            </script>
        @endif

        <!-- Meta Pixel -->
        @if (!empty($meta['meta_pixel_id']))
            <!-- Meta Pixel Code -->
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{{ $meta['meta_pixel_id'] }}');
                fbq('track', 'PageView');
            </script>
            <noscript>
                <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $meta['meta_pixel_id'] }}&ev=PageView&noscript=1" />
            </noscript>
            <!-- End Meta Pixel Code -->
        @endif

        <!-- TikTok Pixel -->
        @if (!empty($meta['tiktok_pixel_id']))
            <script>
            !function (w, d, t) {
              w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e){var n="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=n,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]={};var o=d.createElement("script");o.type="text/javascript",o.async=!0,o.src=n+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};

              ttq.load('{{ $meta['tiktok_pixel_id'] }}');
              ttq.page();
            }(window, document, 'ttq');
            </script>
        @endif
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <router-view></router-view>
    </div>
    @if (isset($meta) && !empty($meta) && !empty($meta['store_id']))
        <!-- Used for custom domains -->
        <script>
            window.storeId = '{{ $meta['store_id'] }}';
        </script>
    @endif
</body>
</html>

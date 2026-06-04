<!DOCTYPE html>
<html>
    <head>
        <title>{{ config('app.name', 'Pterodactyl') }}</title>

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            
            <!-- meta data -->

            <meta name="theme-color" content="{{ $siteConfiguration['arix']['meta_color'] }}"/>
            <link rel="icon" type="image/x-icon" href="{{ $siteConfiguration['arix']['meta_favicon'] }}">

            <meta name="title" content="{{ $siteConfiguration['arix']['meta_title'] }}" />
            <meta name="description" content="{{ $siteConfiguration['arix']['meta_description'] }}" />

            <meta property="og:type" content="website" />
            <meta property="og:url" content="{{config('app.url', 'https://localhost')}}" />
            <meta property="og:title" content="{{ $siteConfiguration['arix']['meta_title'] }}" />
            <meta property="og:description" content="{{ $siteConfiguration['arix']['meta_description'] }}" />
            <meta property="og:image" content="{{ $siteConfiguration['arix']['meta_image'] }}" />

            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:url" content="{{config('app.url', 'https://localhost')}}" />
            <meta property="twitter:title" content="{{ $siteConfiguration['arix']['meta_title'] }}" />
            <meta property="twitter:description" content="{{ $siteConfiguration['arix']['meta_description'] }}" />
            <meta property="twitter:image" content="{{ $siteConfiguration['arix']['meta_image'] }}" />

            <!-- meta data -->
            <!--
            <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png?v=616942">
            <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
            <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
            <link rel="manifest" href="/favicons/manifest.json">
            <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
            <link rel="shortcut icon" href="/favicons/favicon.ico">
            <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        -->
        @show

        @section('user-data')
            @if(!is_null(Auth::user()))
                <script>
                    window.PterodactylUser = {!! json_encode(Auth::user()->toVueObject()) !!};
                </script>
            @endif
            @if(!empty($siteConfiguration))
                <script>
                    window.SiteConfiguration = {!! json_encode($siteConfiguration) !!};
                </script>
            @endif
        @show
        <style>
            :root{
                <?php if ($siteConfiguration['arix']['borderInput'] === 'true') {
                    echo '--borderInput: 1px solid;
';  
                }?>
                --radiusBox: {{ $siteConfiguration['arix']['radiusBox'] }};
                --radiusInput: {{ $siteConfiguration['arix']['radiusInput'] }};
            }

            <?php if ($siteConfiguration['arix']['defaultMode'] === 'darkmode') {
                echo ':root';
            } else {
                echo '.lightmode';
            }?>{
                --image: url({{ $siteConfiguration['arix']['backgroundImage'] }});
                --primary: {{ $siteConfiguration['arix']['primary'] }};

                --successText: {{ $siteConfiguration['arix']['successText'] }};
                --successBorder: {{ $siteConfiguration['arix']['successBorder'] }};
                --successBackground: {{ $siteConfiguration['arix']['successBackground'] }};

                --dangerText: {{ $siteConfiguration['arix']['dangerText'] }};
                --dangerBorder: {{ $siteConfiguration['arix']['dangerBorder'] }};
                --dangerBackground: {{ $siteConfiguration['arix']['dangerBackground'] }}; 

                --secondaryText: {{ $siteConfiguration['arix']['secondaryText'] }};
                --secondaryBorder: {{ $siteConfiguration['arix']['secondaryBorder'] }};
                --secondaryBackground: {{ $siteConfiguration['arix']['secondaryBackground'] }};

                --gray50: {{ $siteConfiguration['arix']['gray50'] }};
                --gray100: {{ $siteConfiguration['arix']['gray100'] }};
                --gray200: {{ $siteConfiguration['arix']['gray200'] }};
                --gray300: {{ $siteConfiguration['arix']['gray300'] }};
                --gray400: {{ $siteConfiguration['arix']['gray400'] }};
                --gray500: {{ $siteConfiguration['arix']['gray500'] }};
                --gray600: {{ $siteConfiguration['arix']['gray600'] }};
                --gray700: color-mix(in srgb, {{ $siteConfiguration['arix']['gray700'] }} {{ $siteConfiguration['arix']['backdropPercentage'] }}, transparent);
                --gray800: {{ $siteConfiguration['arix']['gray800'] }};
                --gray900: {{ $siteConfiguration['arix']['gray900'] }};

                --gray700-default: {{ $siteConfiguration['arix']['gray700'] }};;
            }
            <?php if ($siteConfiguration['arix']['defaultMode'] !== 'darkmode') {
                echo ':root';
            } else {
                echo '.lightmode';
            }?>{
                --image: url({{ $siteConfiguration['arix']['backgroundImageLight'] }});
                --primary: {{ $siteConfiguration['arix']['lightmode_primary'] }};

                --successText: {{ $siteConfiguration['arix']['lightmode_successText'] }};
                --successBorder: {{ $siteConfiguration['arix']['lightmode_successBorder'] }};
                --successBackground: {{ $siteConfiguration['arix']['lightmode_successBackground'] }};

                --dangerText: {{ $siteConfiguration['arix']['lightmode_dangerText'] }};
                --dangerBorder: {{ $siteConfiguration['arix']['lightmode_dangerBorder'] }};
                --dangerBackground: {{ $siteConfiguration['arix']['lightmode_dangerBackground'] }}; 

                --secondaryText: {{ $siteConfiguration['arix']['lightmode_secondaryText'] }};
                --secondaryBorder: {{ $siteConfiguration['arix']['lightmode_secondaryBorder'] }};
                --secondaryBackground: {{ $siteConfiguration['arix']['lightmode_secondaryBackground'] }};

                --gray50: {{ $siteConfiguration['arix']['lightmode_gray50'] }};
                --gray100: {{ $siteConfiguration['arix']['lightmode_gray100'] }};
                --gray200: {{ $siteConfiguration['arix']['lightmode_gray200'] }};
                --gray300: {{ $siteConfiguration['arix']['lightmode_gray300'] }};
                --gray400: {{ $siteConfiguration['arix']['lightmode_gray400'] }};
                --gray500: {{ $siteConfiguration['arix']['lightmode_gray500'] }};
                --gray600: {{ $siteConfiguration['arix']['lightmode_gray600'] }}; 
                --gray700: color-mix(in srgb, {{ $siteConfiguration['arix']['lightmode_gray700'] }} {{ $siteConfiguration['arix']['backdropPercentage'] }}, transparent);
                --gray800: {{ $siteConfiguration['arix']['lightmode_gray800'] }};
                --gray900: {{ $siteConfiguration['arix']['lightmode_gray900'] }};

                --gray700-default: {{ $siteConfiguration['arix']['lightmode_gray700'] }};;
            }

            <?php if ($siteConfiguration['arix']['backdrop'] === 'true') {
                echo '.backdrop{border:1px solid;border-color:var(--gray600)!important;backdrop-filter:blur(16px);}';
            }?>
            @import url('//fonts.googleapis.com/css?family=Rubik:300,400,500&display=swap');
            @import url('//fonts.googleapis.com/css?family=IBM+Plex+Mono|IBM+Plex+Sans:500&display=swap');
        </style>

        @yield('assets')

        @include('layouts.scripts')
    </head>
    <body class="{{ $css['body'] ?? 'bg-neutral-50' }}">
        @section('content')
            @yield('above-container')
            @yield('container')
            @yield('below-container')
        @show
        @section('scripts')
            {!! $asset->js('main.js') !!}
        @show
    </body>
    <script>
            localStorage.setItem("username", "PepijnWeijers");
            localStorage.setItem("BuyerID", "305532");
            localStorage.setItem("Timestamp", "1736331236");
    </script>
</html>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ config('app.name', 'Pterodactyl') }} - @yield('title')</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="_token" content="{{ csrf_token() }}">

        <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="/favicons/manifest.json">
        <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
        <link rel="shortcut icon" href="/favicons/favicon.ico">
        <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        <meta name="theme-color" content="#0e4688">

        @include('layouts.scripts')

        @section('scripts')
            {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/sweetalert/sweetalert.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/animate/animate.min.css?t={cache-version}') !!}
            {!! Theme::css('css/arix.css?t={cache-version}') !!}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        @show
    </head>
    <body>

        <nav>
            <a href="{{ route('index') }}" class="logo">
                <img src="/arix/Arix.png" class="logo" alt="Arix Logo"/>
                Arix Editor
            </a>
            <div class="nav-end">
                <a href="" target="_blank">
                    <i class="fa-brands fa-discord"></i> Discord
                </a>
                <a href="{{ route('account') }}" class="account">
                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160" class="user-image" alt="User Image">
                    <span>{{ Auth::user()->name_first }} {{ Auth::user()->name_last }}</span>
                </a>
            </div>
        </nav>

        <div class="wrapper">
            <aside class="sidebar">
                <ul>
                    <li class="{{ ($navbar ?? '') === 'index' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix') }}"><i data-lucide="settings"></i></a>
                        <span class="link-tooltip">General</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'layout' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.layout') }}"><i data-lucide="layout-dashboard"></i></a>
                        <span class="link-tooltip">Layout</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'components' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.components') }}"><i data-lucide="blocks"></i></a>
                        <span class="link-tooltip">Components</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'plugins' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.plugins') }}"><i data-lucide="plug"></i></a>
                        <span class="link-tooltip">Plugins</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'announcement' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.announcement') }}"><i data-lucide="megaphone"></i></a>
                        <span class="link-tooltip">Announcement</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'mail' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.mail') }}"><i data-lucide="mail"></i></a>
                        <span class="link-tooltip">Mail</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'styling' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.styling') }}"><i data-lucide="paintbrush"></i></a>
                        <span class="link-tooltip">Styling</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'meta' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.meta') }}"><i data-lucide="globe"></i></a>
                        <span class="link-tooltip">Meta</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'colors' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.colors') }}"><i data-lucide="palette"></i></a>
                        <span class="link-tooltip">Colors</span>
                    </li>
                    <li class="{{ ($navbar ?? '') === 'advanced' ? 'active' : '' }}">
                        <a href="{{ route('admin.arix.advanced') }}"><i data-lucide="sliders-horizontal"></i></a>
                        <span class="link-tooltip">Advanced</span>
                    </li>
                </ul>
                <ul class="sidebar-bottom">
                    <li>
                        <a href="{{ route('admin.index') }}"><i data-lucide="arrow-left"></i></a>
                        <span class="link-tooltip">Admin</span>
                    </li>
                </ul>
            </aside>
            <div class="content-container">
                @if($sideEditor ?? false)
                    <div class="sideEditor-container">
                        <div class="iframe-container">
                            <iframe src="{{ route('index', ['arixPreview' => 1]) }}"></iframe>
                        </div>
                        <div class="sideEditor">
                            @yield('content')
                        </div>
                    </div>
                @else
                    @yield('content')
                @endif
            </div>
        </div>


        @section('footer-scripts')
            <script src="/js/keyboard.polyfill.js" type="application/javascript"></script>
            <script>keyboardeventKeyPolyfill.polyfill();</script>

            {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/sweetalert/sweetalert.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/slimscroll/jquery.slimscroll.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/adminlte/app.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap-notify/bootstrap-notify.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}
            {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
            <script src="/js/autocomplete.js" type="application/javascript"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <script>
                lucide.createIcons();
            </script>

            @if(Auth::user()->root_admin)
                <script>
                    $('#logoutButton').on('click', function (event) {
                        event.preventDefault();

                        var that = this;
                        swal({
                            title: 'Do you want to log out?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d9534f',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Log out'
                        }, function () {
                             $.ajax({
                                type: 'POST',
                                url: '{{ route('auth.logout') }}',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },complete: function () {
                                    window.location.href = '{{route('auth.login')}}';
                                }
                        });
                    });
                });
                </script>
            @endif

            <script>
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                })
            </script>
        @show
    </body>
</html>

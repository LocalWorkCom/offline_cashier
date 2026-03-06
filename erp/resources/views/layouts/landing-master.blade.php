<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-nav-style="menu-click" data-menu-position="fixed" data-theme-mode="light">

    <head>

        <!-- Meta Data -->
		<meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="Velvet - Laravel Bootstrap Admin Dashboard Template">
        <meta name="Author" content="Spruko Technologies Private Limited">
        <meta name="keywords" content="laravel dashboard, laravel vite, laravel template, template dashboard, admin template, admin, dashboard admin, laravel admin panel, template admin, admin panel for laravel, laravel admin, alaravel, laravel framework, dashboard, laravel template admin">

        <!-- TITLE -->
		<title> ERP </title>

        <!-- FAVICON -->
        <link rel="icon" href="{{asset('build/assets/images/brand-logos/fav.ico')}}" type="image/x-icon">

        <!-- BOOTSTRAP CSS -->
	    <link  id="style" href="{{asset('build/assets/libs/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">

        <!-- ICONS CSS -->
        <link href="{{asset('build/assets/icon-fonts/icons.css')}}" rel="stylesheet">

        <!-- APP SCSS -->
        @vite(['resources/sass/app.scss'])


        @include('layouts.components.styles')


        @yield('styles')

	</head>

    <body class="landing-body landing-alert">

        <!-- SWITCHER -->

        @include('layouts.components.landing.switcher')

        <!-- END SWITCHER -->

        <!-- PAGE -->
		<div class="landing-page-wrapper">

            <!-- HEADER -->

            @include('layouts.components.landing.header')

            <!-- END HEADER -->

            <!-- SIDEBAR -->

            @include('layouts.components.landing.sidebar')

            <!-- END SIDEBAR -->

            <!-- MAIN-CONTENT -->

            <div class="main-content landing-main">

                @yield('content')

                <!-- FOOTER -->
                @include('layouts.components.landing.footer')

                <!-- FOOTER -->

            </div>
            <!-- END MAIN-CONTENT -->

		</div>
        <!-- END PAGE-->

        <!-- SCRIPTS -->

        @include('layouts.components.landing.scripts')

        @yield('scripts')

        <!-- STICKY JS -->
        <script src="{{asset('build/assets/sticky.js')}}"></script>

        <!-- END SCRIPTS -->

	</body>
</html>

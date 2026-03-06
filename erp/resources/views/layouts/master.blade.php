<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ session('direction', 'ltr') }}" data-nav-layout="vertical"
    data-theme-mode="light" data-header-styles="gradient" data-menu-styles="dark">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="Velvet - Laravel Bootstrap Admin Dashboard Template">
    <meta name="Author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="laravel dashboard, laravel vite, laravel template, template dashboard, admin template, admin, dashboard admin, laravel admin panel, template admin, admin panel for laravel, laravel admin, alaravel, laravel framework, dashboard, laravel template admin">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- TITLE -->
    <title> Erp </title>

    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('build/assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{ asset('build/assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- ICONS CSS -->
    <link href="{{ asset('build/assets/icon-fonts/icons.css') }}" rel="stylesheet">

    <!-- Load Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- APP SCSS -->
    @vite(['resources/sass/app.scss'])


    @include('layouts.components.styles')


    <!-- MAIN JS -->
    <script src="{{ asset('build/assets/main.js') }}"></script>

    @yield('styles')
</head>

<body>

    <!-- SWITCHER -->

    @include('layouts.components.switcher')

    <!-- END SWITCHER -->

    <!-- LOADER -->
    <div id="loader">
        <img src="{{ asset('build/assets/images/media/loader.svg') }}" alt="">
    </div>
    <!-- END LOADER -->

    <!-- PAGE -->
    <div class="page">

        <!-- HEADER -->

        @include('layouts.components.header')

        <!-- END HEADER -->

        <!-- BreadCrumb -->

        {{-- @include('layouts.components.crumb') --}}

        <!-- END BreadCrumb -->

        <!-- SIDEBAR -->

        @include('layouts.components.sidebar')

        <!-- END SIDEBAR -->

        <!-- MAIN-CONTENT -->
        {{-- @include($view) --}}
        @yield('content')

        <!-- END MAIN-CONTENT -->

        <!-- SEARCH-MODAL -->

        @include('layouts.components.search-modal')

        <!-- END SEARCH-MODAL -->

        <!-- RIGHT-SIDEBAR -->

        @include('layouts.components.right-sidebar')

        <!-- END RIGHT-SIDEBAR -->

        <!-- FOOTER -->

        @include('layouts.components.footer')

        <!-- END FOOTER -->

    </div>
    <!-- END PAGE-->

    <!-- SCRIPTS -->

    @include('layouts.components.scripts')


    <!-- STICKY JS -->
    <script src="{{ asset('build/assets/sticky.js') }}"></script>
    {{--    <script src="{{ asset('js/firebase-handler.js') }}"></script> --}}


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (auth()->guard('admin')->check())
        <script type="module">
            import {
                initializeApp
            } from "https://www.gstatic.com/firebasejs/11.7.1/firebase-app.js";
            import {
                getMessaging,
                getToken,
                onMessage,
                isSupported
            } from "https://www.gstatic.com/firebasejs/11.7.1/firebase-messaging.js";

            const firebaseConfig = {
                apiKey: "AIzaSyBQ2cIo6ykJQA_PlBXhWh2NoWzT4ORu72k",
                authDomain: "al-koot-74c79.firebaseapp.com",
                projectId: "al-koot-74c79",
                storageBucket: "al-koot-74c79.firebasestorage.app",
                messagingSenderId: "333138698522",
                appId: "1:333138698522:web:053967bfc305ed53a5cca3",
            };

            // Check support and start
            $(document).ready(async function() {
                const supported = await isSupported();
                if (!supported) {
                    console.warn("Firebase Messaging is not supported in this browser.");
                    return;
                }

                const app = initializeApp(firebaseConfig);
                const messaging = getMessaging(app);

                // Register service worker
                let registration;
                try {
                    registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    console.log('Service Worker registered:', registration.scope);
                } catch (e) {
                    console.error('Service Worker registration failed:', e);
                    return;
                }

                // Listen to foreground messages
                onMessage(messaging, (payload) => {
                    console.log('Foreground message:', payload);
                    showNotification(payload.data);
                });

                const isAuth = {{ auth()->guard('admin')->check() ? 'true' : 'false' }};
                // Parse the JSON properly to get the raw token value
                const userFcmToken = {!! auth()->guard('admin')->check() ? json_encode(auth('admin')->user()->fcm_token) : 'null' !!};

                if (isAuth) {
                    const permission = await requestNotificationPermission();
                    if (permission) {
                        try {
                            const currentToken = await getFCMToken(messaging, registration);

                            // Only store if we got a token and it's different
                            if (currentToken && currentToken !== userFcmToken) {
                                await storeToken(currentToken);
                            }
                        } catch (error) {
                            console.error('Token handling failed:', error);
                        }
                    }
                }
            });

            async function requestNotificationPermission() {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                    return true;
                } else {
                    console.warn('Notification permission denied.');
                    return false;
                }
            }

            // Modified getFCMToken to JUST get the token without storing it
            async function getFCMToken(messaging, registration) {
                try {
                    const token = await getToken(messaging, {
                        vapidKey: 'BCuuSRejwdKQPRNkXkfNAj3P9A9MpdPjQgB9RkcO0-ZyNvz8KBqLFV5TdlGkWb_JLX0qPvp_L4Zd5LFGvF2tNjg',
                        serviceWorkerRegistration: registration
                    });

                    if (token) {
                        console.log('Retrieved FCM token:', token);
                        return token; // Just return the token
                    }
                    console.warn('No registration token available.');
                    return null;
                } catch (error) {
                    console.error('Error getting FCM token:', error);
                    return null;
                }
            }
            async function storeToken(token) {
                try {
                    const response = await fetch('{{ route('firebase.token') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            fcm_token: token,
                            user_id: {{ auth('admin')->user()->id ?? 'null' }}
                        })
                    });

                    if (!response.ok) throw new Error('Failed to store token');
                    console.log('Token successfully stored.');
                } catch (err) {
                    console.error('Error storing token:', err);
                }
            }

            // ✅ Foreground Notification Display
            function showNotification(data) {
                if (!('Notification' in window)) return;

                if (Notification.permission === 'granted') {
                    const notif = new Notification(data.title || 'New Notification', {
                        body: data.body || 'You have a new message',
                        icon: data.icon || '/logo.png',
                        data: {
                            url: data.url || '/'
                        }
                    });

                    notif.onclick = (event) => {
                        window.focus();
                        if (notif.data?.url) {
                            window.open(notif.data.url, '_blank');
                        }
                    };
                }
            }
        </script>
    @endif
    <!-- Initialize Select2 -->
    <script>
        $(document).ready(function() {
            $('.select2').select2(); // Ensure this matches your element class.
        });
    </script>


    <!-- APP JS -->
    @vite('resources/js/app.js')


    <!-- CUSTOM-SWITCHER JS -->
    @vite('resources/assets/js/custom-switcher.js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.min.js"></script>

    <script>
        Pusher.logToConsole = true;

        const pusherKey = "{{ config('broadcasting.connections.pusher.key') }}";
        const pusherCluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";

        const pusher = new Pusher(pusherKey, {
            cluster: pusherCluster,
            forceTLS: true,
        });
    //     const pusherKey = "{{ config('broadcasting.connections.pusher.key') }}";
    //     const pusherCluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";

    //     const pusher = new Pusher(pusherKey, {
    //         cluster: pusherCluster,
    //         forceTLS: true,
    //         authEndpoint: '/broadcasting/auth',
    //         auth: {
    //             headers: {
    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //             }
    //         }
    //     });
    // var channel = pusher.subscribe("private-notification-{{ auth('admin')->user()->id }}-{{ auth('admin')->user()->flag }}");

        var channel = pusher.subscribe('notification' + {{ auth('admin')->user()->id }});

        channel.bind('Notifications', function(data) {
            let notificationData = data.data; // Correctly access nested data

            let notificationItem = `
        <a href="${notificationData.url}" class="dropdown-item">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bx bx-bell bx-tada bx-md text-primary"></i>
                </div>
                <div>
                    <p class="mb-0">${notificationData.title}</p>
                    <small class="text-muted">${notificationData.description}</small>
                </div>
            </div>
        </a>
    `;

            // Append new notification at the **TOP** of the list
            $('#notification-list').prepend(notificationItem);

            // Update the notification count badge
            let count = parseInt($('#notification-icon-badge').text()) + 1;
            $('#notification-icon-badge').text(count);
            $('#notifiation-data').text(count);
        });
    </script>
    @yield('scripts')


</body>

</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ session('direction', 'rtl') }}">

<head>
    <meta charset="UTF-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:url" content="" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="" />
    <meta property="og:description" content="" />
    <meta property="og:image" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@lang('website/home.title')</title>

    <link rel="shortcut icon" href="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo.png') }}" sizes="25x25" />

    <!-- Stylesheets -->
    <link rel="stylesheet"
        href="{{ asset('front/AlKout-Resturant/SiteAssets/bootstrap-5.1.3/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('front/AlKout-Resturant/SiteAssets/fontawesome-free-5.15.4-web/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('front/AlKout-Resturant/SiteAssets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/AlKout-Resturant/SiteAssets/aos-master/dist/aos.css') }}">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css">
    <!-- Owl Carousel -->
    <link rel="stylesheet" href="{{ asset('front/AlKout-Resturant/SiteAssets/css/animate.min.css') }}">


    <link rel="stylesheet"
        href="{{ asset('front/AlKout-Resturant/SiteAssets/OwlCarousel2-2.3.4/dist/assets/owl.carousel.min.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('front/AlKout-Resturant/SiteAssets/OwlCarousel2-2.3.4/dist/assets/owl.theme.default.min.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <!-- include Gallery (lightbox) plugin -->
    <link rel="stylesheet" href="{{ asset('front/AlKout-Resturant/SiteAssets/lightbox/css/lightbox.min.css') }}" />
    <!-- Main Style -->
    {{-- <link rel="stylesheet" href="SiteAssets/css/style.css" type="text/css" /> --}}

    {{-- @if (app()->getLocale() == 'ar') --}}
    <link rel="stylesheet" href="{{ asset('front/AlKout-Resturant/SiteAssets/css/style.css') }}" type="text/css" />
    {{-- @else
        <link rel="stylesheet" href="{{ asset('front/AlKout-Resturant/SiteAssets/css/style-EN.css') }}"
            type="text/css" />
    @endif --}}
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
    <style>
        .calendar-table td.disabled {
            color: #ccc;
            pointer-events: none;
            background-color: #f9f9f9;
        }

        .required_star {
            width: 20px;
            text-align: center;
            color: red;
        }
    </style>
    @stack('style')

</head>

<body>


    @include('website.layouts.header') {{-- Default Header --}}

    <main class="follow-request">
        @yield('content')


        <section class="before-footer"></section>
    </main>

    <!-- modals -->
    @include('website.success-modal')

    <!-- login modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close text-light" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                {{-- <div id="msg-error" style="display: none;text-align:center" class="message bg-warning p-2 rounded-3">

                </div> --}}
                @include('website.auth.login')
                @include('website.auth.register')
                @include('website.auth.forgetpass')
            </div>
        </div>
    </div>
    <!-- end login modal -->
    @include('website.branches')
    <!-- branches  modal -->

    <div class="logout-modal modal fade" tabindex="-1" id="deleteaddressauthModal">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="deleteaddressauthForm">
                    @csrf
                    <div class="modal-header border-0">
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-sign-out-alt main-color fs-1"></i>
                        <h4 class="mt-4"> @lang('header.deleteaddress')</h4>
                    </div>
                    <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                        <button type="submit" class="btn w-25 mx-2"> @lang('header.confirm')</button>
                        <button type="button" class="btn reversed main-color w-25 mx-2"
                            data-bs-dismiss="modal">@lang('header.cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="logout-modal modal fade" tabindex="-1" id="defaultddressauthModal">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="defaultddressauthForm">
                    @csrf
                    <div class="modal-header border-0">
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-sign-out-alt main-color fs-1"></i>
                        <h4 class="mt-4"> @lang('auth.defaultddress')</h4>
                    </div>
                    <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                        <button type="submit" class="btn w-25 mx-2"> @lang('header.confirm')</button>
                        <button type="button" class="btn reversed main-color w-25 mx-2"
                            data-bs-dismiss="modal">@lang('header.cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="location-modal modal fade" tabindex="-1" id="locationModal">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-map-marker-alt main-color fs-1"></i>
                    <h4 class="mt-4">
                        @lang('header.notdeliverylocation')
                    </h4>
                </div>
                <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#deliveryModal">
                        @lang('header.changeaddress')
                    </button> <button type="button" class="btn reversed main-color" data-bs-toggle="modal"
                        data-bs-target="#branchesModal">
                        @lang('header.changebranch')</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="branchSelectionModal" tabindex="-1" aria-labelledby="branchSelectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3 class="text-center fw-bold mb-4"> @lang('header.startOrder') </h3>
                    <div class="bg-dark-graypy-2">
                        <h5 class="text-dark fw-bold text-center"> @lang('header.whichbranch')</h5>
                    </div>
                    <div class="d-flex justify-content-end my-2">
                        <button class="btn btn-no-modal useMyLocationReceiveBtn" id="useMyLocationReceiveBtn">
                            @lang('header.useMyLocation')</button>
                    </div>
                    <div class="section-one my-4">
                        <h5 class="text-dark fw-bold mb-3"> @lang('header.locationrecive')</h5>
                        <div class="select-menu">
                            <select id="countrySelect" class="form-select mb-3">
                                <option selected disabled> @lang('header.choosecountry')</option>
                                @foreach (GetCountries() as $countries)
                                    <option value="{{ $countries->id }}">
                                        {{ app()->getLocale() === 'ar' ? $countries->name_ar : $countries->name_en }}
                                    </option>
                                @endforeach
                            </select>
                            <select id="citySelect" class="form-select mb-3">
                                <option selected disabled> @lang('header.choosecity')</option>

                            </select>
                            <select id="areaSelect" class="form-select mb-3">
                                <option selected disabled>@lang('header.choosearea')</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-green" id="searchButton"> @lang('header.accept')</button>
                        </div>
                    </div>
                    <div class="section-three my-4 d-none">
                        <div id="content">

                        </div>
                        <div class="d-flex justify-content-end my-3">
                            <button class="btn btn-green" id="continue">@lang('header.Continue')</button>
                        </div>
                    </div>
                    <div class="section-four my-4 d-none">
                        <div class="row bg-warning selectors-container">
                            <div class="col-lg-7 position-relative">
                                <button id="calendar-btn2"
                                    class="select-btn d-flex align-items-center justify-content-between">
                                    <div class="text-muted">
                                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/calender.svg') }}"
                                            alt="" />
                                        <span id="selected-date2" class="mx-2"></span>
                                    </div>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </button>

                                <div id="calendar-container2" class="calendar-container">
                                    <div class="calendar-header">
                                        <button id="prev-month2" class="nav-btn">&lt;</button>
                                        <span id="calendar-month-year"></span>
                                        <button id="next-month2" class="nav-btn">&gt;</button>
                                    </div>
                                    <table class="calendar-table">
                                        <thead>
                                            <tr id="calendar-days-title2"></tr>
                                        </thead>
                                        <tbody id="calendar-days2"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <button id="search-time-btn" class="btn search-btn w-100">@lang('header.searchtake')</button>
                            </div>
                        </div>
                        <div id="available-times-section" class="mt-4 d-none">
                            <h5 class="fw-bold">@lang('header.avilabletimes')</h5>
                            <div class="times-carousel owl-carousel owl-theme">
                                <div class="item time-item" data-table="">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/clock.svg') }}"
                                        alt="" />
                                    <p class="mb-0"></p>
                                    <small class="text-muted"></small>
                                </div>
                            </div>

                        </div>
                        <div id="recieve-now" class="bg-warning d-flex align-items-center p-3 d-none">
                            <input class="form-check-input ms-2" type="checkbox" id="recieve-now">
                            <label class="form-check-label d-flex align-items-center main-color" for="recieve-now">
                                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/red-clock.svg') }}"
                                    alt="" />
                                @lang('header.pickupnow')
                            </label>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn reversed main-color mx-3" id="previousBtn"> @lang('header.previous')</button>
                            <button id="confirm-recieve-btn" class="btn"> @lang('header.Continue')</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="location-pop-up">
        <div id="modal_access" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <h2>@lang('header.locationaccess')</h2>
                    </div>
                    <div class="modal-footer d-flex flex-column border-0">
                        <button type="button" class="btn w-100" id="accessLocationBtn">@lang('header.access')</button>
                        {{-- <button type="button" class="btn w-100 reversed main-color" data-bs-toggle="modal"
                            data-bs-target="#deliveryModal">@lang('header.onmap')</button> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/OwlCarousel2-2.3.4/dist/owl.carousel.min.js') }}"></script>

    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/fontawesome-free-5.15.4-web/js/all.min.js') }}"></script>
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/bootstrap-5.1.3/dist/js/bootstrap.bundle.js') }}"></script>
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/bootstrap-5.1.3/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/js/bootstarp-select.js') }}"></script>
    <!-- Main js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.min.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <!-- include Gallery (lightbox) plugin js-->
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/lightbox/js/lightbox.min.js') }}"></script>

    <!-- include Owl Carousel plugin js-->
    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/aos-master/dist/aos.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDgewbk6uYuyvCImG5r5wl0wRuDVaQrFg8"></script>
    <script>
        AOS.init();
    </script>
    @if (auth('client')->check())
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

            async function initFirebaseMessaging() {
                try {
                    // 1. Check browser support
                    const supported = await isSupported();
                    if (!supported) {
                        console.warn("Firebase Messaging not supported");
                        return;
                    }

                    // 2. Initialize Firebase
                    const app = initializeApp(firebaseConfig);
                    const messaging = getMessaging(app);

                    // 3. Register Service Worker
                    const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
                        scope: '/'
                    });
                    console.log('SW registered at scope:', registration.scope);

                    // 4. Verify registration (added this)
                    const existingReg = await navigator.serviceWorker.getRegistration();
                    console.log('Existing registration:', existingReg ? 'Found' : 'Not found');

                    // 5. Request permission and get token
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        const currentToken = await getToken(messaging, {
                            vapidKey: 'BCuuSRejwdKQPRNkXkfNAj3P9A9MpdPjQgB9RkcO0-ZyNvz8KBqLFV5TdlGkWb_JLX0qPvp_L4Zd5LFGvF2tNjg',
                            serviceWorkerRegistration: registration
                        });

                        if (currentToken) {
                            console.log('FCM Token:', currentToken);

                            // Get the stored token from your user data
                            const storedToken = {!! auth()->guard('client')->check() ? json_encode(auth('client')->user()->fcm_token) : 'null' !!};

                            // Only store if different or new
                            if (!storedToken || storedToken !== currentToken) {
                                await storeToken(currentToken);
                            }
                        }
                    }

                    // 6. Set up message handler
                    onMessage(messaging, (payload) => {
                        console.log('Foreground message:', payload);
                        showNotification(payload.notification || payload.data);
                    });

                } catch (error) {
                    console.error('FCM initialization error:', error);
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
                            user_id: {{ auth('client')->user()->id ?? 'null' }}
                        })
                    });

                    if (!response.ok) throw new Error('Storage failed: ' + await response.text());
                    console.log('Token stored successfully');
                } catch (err) {
                    console.error('Token storage error:', err);
                }
            }

            function showNotification(data) {
                if (!('Notification' in window) || Notification.permission !== 'granted') return;

                const options = {
                    body: data.body || 'New notification',
                    icon: data.icon || '/logo.png',
                    data: {
                        url: data.url || '/'
                    }
                };

                const notification = new Notification(data.title || 'Notification', options);

                notification.onclick = (event) => {
                    window.focus();
                    if (notification.data?.url) {
                        window.open(notification.data.url, '_blank');
                    }
                };
            }

            // Initialize when DOM is ready
            document.addEventListener('DOMContentLoaded', initFirebaseMessaging);
        </script>
    @endif
    @include('website.user-modal')
    @include('website.cart.global')


    @include('website.delivery')

    {{-- @include('website.sideCart') --}}

    <!-- Required Libraries -->

    <script src="{{ asset('front/AlKout-Resturant/SiteAssets/js/style.js') }}"></script>

    <script>
        $(document).ready(function() {

            // Pass the session value to a JavaScript variable
            var sessionaddress = {{ session()->has('new_address_id') ? session('new_address_id') : 'null' }};
            let branchTakeaway = getCookie('branch_takaway');
            const address = localStorage.getItem('authaddress');
            // console.log(address);
            if (sessionaddress !== null && sessionaddress !== undefined && sessionaddress !== ' ' &&
                branchTakeaway === 'false') {
                // console.log(sessionaddress);
                localStorage.setItem('authaddress', sessionaddress);

                if (address && Object.keys(address).length === 0) {
                    setCookie('branch_takaway', 'false', 7);
                    localStorage.removeItem('mapDetail');
                    localStorage.removeItem('addressDetails');
                }
            } else {
                localStorage.removeItem('authaddress');
            }
        });

        @if (request()->getHttpHost() === 'erpsystem.testdomain100.online' || request()->getHttpHost() === 'erp.test')

            Pusher.logToConsole = true;
        @endif
        const pusherKey = "{{ config('broadcasting.connections.pusher.key') }}";
        const pusherCluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
        const pusher = new Pusher(pusherKey, {
            cluster: pusherCluster,
            forceTLS: true
        });

        // Channel Configuration

        updateCartCount();
    </script>

    @if (session('showModal'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Show the modal
                const confirmOrderModal = new bootstrap.Modal(document.getElementById('successmodal'));
                confirmOrderModal.show();

                // Close the modal after 1 second
                setTimeout(() => {
                    confirmOrderModal.hide();
                }, 1000); // 1000ms = 1 second
            });
        </script>
    @endif
    @if (session('show_address_no'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Show the modal
                const confirmOrderModal = n new bootstrap.Modal('#addressnotcart').show();
                confirmOrderModal.show();

                // Close the modal after 1 second
                setTimeout(() => {
                    confirmOrderModal.hide();
                }, 30000); // 1000ms = 1 second
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalAccessKey = 'locationPopupShown'; // Key to track popup state
            const routeMenuUrl = "{{ route('menu', ['branch_id' => '__BRANCH_ID__']) }}"; // Placeholder URL

            // Check if the popup has already been shown

            const popupShown = getCookie(modalAccessKey);

            if (!popupShown) {
                // Show the modal if it hasn't been shown before
                const modalElement = document.getElementById('modal_access');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    setCookie('branch_takaway', 'false', 7);

                    // Set a cookie when the modal is closed
                    modalElement.addEventListener('hidden.bs.modal', function() {
                        setCookie(modalAccessKey, 'true', 7); // Cookie expires in 7 days
                    });
                }
            }

            // Check if latitude and longitude cookies exist
            const hasLatitudeCookie = document.cookie.split('; ').some(row => row.startsWith('latitude='));
            const hasLongitudeCookie = document.cookie.split('; ').some(row => row.startsWith('longitude='));

            // Handle "Allow" button click
            document.getElementById('accessLocationBtn').addEventListener('click', function() {

                $('#modal_access').modal('show'); // Close the modal
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        setCookie('locationPopupShown', true, 7);
                        // Set cookies with latitude and longitude
                        setCookie('latitude', latitude, 7);
                        setCookie('longitude', longitude, 7);

                        setBranchCookie(latitude, longitude, function() {
                            // // Reload the page after setting location cookies and branch_id
                            window.location.reload();
                        });
                    }, function(error) {
                        handleGeolocationError(error);
                    });
                } else {
                    alert('@lang('header.geolocationnotsupported')');
                }
            });
            $('#modal_access').on('hidden.bs.modal', function() {
                document.body.removeAttribute('inert');
            });

            // Function to handle geolocation errors
            function handleGeolocationError(error) {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        alert('@lang('header.permissiondenied')');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert('@lang('header.positionunavailable')');
                        break;
                    case error.TIMEOUT:
                        alert('@lang('header.requesttimeout')');
                        break;
                    default:
                        alert('@lang('header.unknownerror')');
                        break;
                }
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.go-branch-link').forEach(link => {
                link.addEventListener('click', (event) => {
                    localStorage.removeItem('cart');
                    updateCartCount();
                });
            });
        });

        function getCookie(name) {
            let cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                let cookie = cookies[i].trim();
                // Check if the cookie starts with the desired name
                if (cookie.startsWith(name + '=')) {
                    return cookie.split('=')[1];
                }
            }
            return null; // Return null if the cookie doesn't exist
        }

        function setCookie(name, value, days) {
            const existing = getCookie(name);
            if (existing !== value) { // Only update if value is different
                let expires = '';
                if (days) {
                    let date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = '; expires=' + date.toUTCString();
                }
                document.cookie = name + '=' + value + expires + '; path=/';
            }
        }

        function setBranchCookie(lat, long, updateBranchId = true, callback) {
            if (lat && long) {
                // Set latitude and longitude cookies
                setCookie('latitude', lat, 7);
                setCookie('longitude', long, 7);

                if (updateBranchId) {
                    // Make an AJAX request to fetch the nearest branch only if updateBranchId is true
                    $.ajax({
                        url: '/nearest-branch',
                        method: 'GET',
                        data: {
                            lat: lat,
                            long: long
                        },
                        success: function(response) {
                            var branch_id = response.branch_id;

                            if (branch_id) {
                                setCookie('branch_id', branch_id, 7);
                                window.location.reload();
                            }

                            if (callback && typeof callback === "function") {
                                callback();
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching nearest branch:', error);
                        }
                    });
                } else {
                    if (callback && typeof callback === "function") {
                        callback();
                    }
                }
            }
        }

        function reorder(order_id) {
            $.ajax({
                url: "{{ route('reorder') }}",
                method: 'get',
                data: {
                    orderId: order_id
                },

                success: function(response) {
                    console.log(response);

                    if (response.status === false && response) {
                        alert("Validation error"); // or show your own styled error
                        return;
                    }

                    let orderData = response.data;

                    let transformed = {
                        items: (orderData.items || []).map(item => {
                            let addons = (item.addon_categories || []).flatMap(
                                cat =>
                                Array.isArray(cat.addon) ? cat.addon.map(
                                    add => ({
                                        id: String(add.id),
                                        name: add.name,
                                        price: parseFloat(add.price)
                                    })) : []
                            );

                            let sizeLabel = item.has_size && item
                                .size_selected ? item.size_selected.name : '';

                            let sizeId = item.has_size && item
                                .size_selected ? item.size_selected.id : '';

                            let sizePrice = item.has_size && item
                                .size_selected ? item.size_selected.price : '';
                            let itemPrice = parseFloat(item.price);
                            let addonsPrice = addons.reduce((sum, addon) =>
                                sum + addon.price, 0);
                            let totalPrice = (itemPrice + addonsPrice) * item
                                .quantity;

                            return {
                                dish_id: String(item.dish_id),
                                name: item.name,
                                image: item.image || '',
                                price: itemPrice,
                                size: {
                                    label: sizeLabel,
                                    id: sizeId,
                                    price: sizePrice
                                },
                                addons,
                                quantity: item.quantity,
                                notes: item.note || '',
                                totalPrice
                            };
                        }),
                        coupon: orderData.coupon_code || '',
                        coupon_value: 0,
                        symbol: orderData.items?.[0]?.currency_symbol || '',
                        order_type: orderData.type || 'Delivery'
                    };

                    localStorage.setItem('cart', JSON.stringify(transformed));

                    // Save takeaway details if it's a takeaway order
                    if (transformed.order_type.toLowerCase() === 'takeaway') {
                        document.cookie = "branch_takaway=true; path=/;";
                        const takeawayDetails = {
                            branch_id: orderData.branch_id,
                            branch_name: orderData.branch_name,
                            branch_address: orderData.branch_address,
                            branch_phone: orderData.branch_phone,
                            selected_date: orderData.selected_date,
                            selected_time: orderData.selected_time,
                            order_type: transformed.order_type
                        };

                        localStorage.setItem("takeaway_details", JSON.stringify(takeawayDetails));
                    }

                    // ✅ Redirect to cart
                    window.location.href = '/cart';

                },
                error: function(xhr) {
                    console.error("Reorder error:", xhr);
                    alert("Something went wrong!");
                }
            });
        }

        $(document).ready(function() {
            var lat = getCookie('latitude');
            var long = getCookie('longitude');
            var branch = getCookie('branch_id');

            if (!branch) {
                var defaultBranch = '{{ getDefaultBranch() }}';

                if (defaultBranch) {
                    setCookie('branch_id', defaultBranch, 7);
                }
            }
        });
    </script>
    @include('website.layouts.footer')

    <div id="chatModalContainer" style="display: none;">
        @include('website.chatModal')
    </div>
    @include('website.table-reservation.view')

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let currentUrl = window.location.pathname;

            if (!currentUrl.includes('/chat')) {
                document.getElementById("chatModalContainer").style.display = "block";
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const cartLink = document.getElementById('cart-header');
            const hasCart = localStorage.getItem('cart');
            const hasReservation = localStorage.getItem('TableReservation');
            let reservationData = null;

            if (hasReservation) {
                reservationData = JSON.parse(hasReservation);
            }

            if (hasCart || (hasReservation && reservationData && reservationData.reservType == 'with')) {
                cartLink.href = "{{ route('cart') }}";
            } else if (hasReservation) {
                cartLink.href = "{{ route('table-reservation.confirmation') }}";
                document.getElementById('cart-count').textContent = 1;
            } else {
                cartLink.href = "{{ route('cart') }}";
            }
        });
    </script>

    {{-- @include('website.receive') --}}

    @stack('scripts')

</body>

</html>

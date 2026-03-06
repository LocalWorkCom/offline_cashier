<header class="app-header">

    <!-- Start::main-header-container -->
    <div class="main-header-container container-fluid">

        <!-- Start::header-content-left -->
        <div class="header-content-left">

            <!-- Start::header-element -->
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="{{ url('dashboard') }}" class="header-logo">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="logo"
                            class="desktop-logo">
                        <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}" alt="logo"
                            class="toggle-logo">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-dark.png') }}" alt="logo"
                            class="desktop-dark">
                        <img src="{{ asset('build/assets/images/brand-logos/toggle-dark.png') }}" alt="logo"
                            class="toggle-dark">
                    </a>
                </div>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link -->
                <a aria-label="anchor" href="javascript:void(0);" class="sidemenu-toggle header-link"
                    data-bs-toggle="sidebar">
                    <span class="open-toggle me-2">
                        <i class="bx bx-menu header-link-icon"></i>
                    </span>
                </a>
                <div class="main-header-center  d-none d-lg-block  header-link">
                    <input type="text" class="form-control form-control-lg" id="typehead"
                        placeholder="Search for results..." autocomplete="off">
                    <button type="button" aria-label="button" class="btn pe-1"><i class="fe fe-search"
                            aria-hidden="true"></i></button>
                    <div id="headersearch" class="header-search">
                        <div class="p-3">
                            <div class="">
                                <p class="fw-semibold text-muted mb-2 fs-13">Recent Searches</p>
                                <div class="ps-2">
                                    <a href="javascript:void(0)" class="search-tags"><i
                                            class="fe fe-search me-2"></i>People<span></span></a>
                                    <a href="javascript:void(0)" class="search-tags"><i
                                            class="fe fe-search me-2"></i>Pages<span></span></a>
                                    <a href="javascript:void(0)" class="search-tags"><i
                                            class="fe fe-search me-2"></i>Articles<span></span></a>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="fw-semibold text-muted mb-2 fs-13">Apps and pages</p>
                                <ul class="ps-2">
                                    <li class="p-1 d-flex align-items-center text-muted mb-2 search-app">
                                        <a href="{{ url('full-calendar') }}"><span><i
                                                    class="bx bx-calendar me-2 fs-14 bg-primary-transparent p-2 rounded-circle"></i>Calendar</span></a>
                                    </li>
                                    <li class="p-1 d-flex align-items-center text-muted mb-2 search-app">
                                        <a href="{{ url('mail') }}"><span><i
                                                    class="bx bx-envelope me-2 fs-14 bg-primary-transparent p-2 rounded-circle"></i>Mail</span></a>
                                    </li>
                                    <li class="p-1 d-flex align-items-center text-muted mb-2 search-app">
                                        <a href="{{ url('buttons') }}"><span><i
                                                    class="bx bx-dice-1 me-2 fs-14 bg-primary-transparent p-2 rounded-circle"></i>Buttons</span></a>
                                    </li>
                                </ul>
                            </div>
                            <div class="mt-3">
                                <p class="fw-semibold text-muted mb-2 fs-13">Links</p>
                                <ul class="ps-2">
                                    <li class="p-1 align-items-center text-muted mb-1 search-app">
                                        <a href="javascript:void(0)"
                                            class="text-primary"><u>http://spruko/spruko.com</u></a>
                                    </li>
                                    <li class="p-1 align-items-center text-muted mb-1 search-app">
                                        <a href="javascript:void(0)"
                                            class="text-primary"><u>http://spruko/spruko.com</u></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="py-3 border-top px-0">
                            <div class="text-center">
                                <a href="javascript:void(0)" class="text-primary text-decoration-underline fs-15">View
                                    all</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element header-search d-lg-none d-block ">
                <!-- Start::header-link -->
                <a aria-label="anchor" href="javascript:void(0);" class="header-link" data-bs-toggle="modal"
                    data-bs-target="#searchModal">
                    <i class="bx bx-search-alt-2 header-link-icon"></i>
                </a>
                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-left -->

        <!-- Start::header-content-right -->
        <div class="header-content-right">

            <!-- Start::header-element -->
            <div class="header-element country-selector">
                <!-- Start::header-link|dropdown-toggle -->
                <a aria-label="anchor" href="javascript:void(0);" class="header-link dropdown-toggle"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bx bx-globe header-link-icon"></i>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <ul class="main-header-dropdown dropdown-menu border-0" data-popper-placement="none">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('set-locale', 'en') }}">
                            <span class="avatar avatar-xs lh-1 me-2">
                                <img src="{{ asset('build/assets/images/flags/us_flag.png') }}" alt="img">
                            </span>
                            English
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('set-locale', 'ar') }}">
                            <span class="avatar avatar-xs lh-1 me-2">
                                <img src="{{ asset('build/assets/images/flags/ar_flag.png') }}" alt="img">
                            </span>
                            Arabic
                        </a>
                    </li>
                </ul>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element header-theme-mode">
                <!-- Start::header-link|layout-setting -->
                <a aria-label="anchor" href="javascript:void(0);" class="header-link layout-setting">
                    <!-- Start::header-link-icon -->
                    <i class="bx bx-sun bx-flip-horizontal header-link-icon ionicon  dark-layout"></i>
                    <!-- End::header-link-icon -->
                    <!--  Start::header-link-icon -->
                    <i class="bx bx-moon bx-flip-horizontal header-link-icon ionicon light-layout"></i>
                    <!-- End::header-link-icon -->
                </a>
                <!-- End::header-link|layout-setting -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            {{-- <div class="header-element cart-dropdown">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside">
                    <i class="bx bx-cart header-link-icon ionicon"></i>
                    <span class="badge bg-danger rounded-pill header-icon-badge" id="cart-icon-badge">5</span>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <!-- Start::main-header-dropdown -->
                <div class="main-header-dropdown dropdown-menu  border-0 dropdown-menu-end"
                    data-popper-placement="none">
                    <div class="p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fs-17 fw-semibold">Cart Items</p>
                            <span class="badge bg-success-transparent" id="cart-data">5 Items</span>
                        </div>
                    </div>
                    <div>
                        <hr class="dropdown-divider">
                    </div>
                    <ul class="list-unstyled mb-0" id="header-cart-items-scroll">
                        <li class="dropdown-item border-bottom">
                            <div class="d-flex align-items-start cart-dropdown-item">
                                <span class="avatar avatar-xl bd-gray-200 p-1">
                                    <img src="{{ asset('build/assets/images/ecommerce/png/1.png') }}" alt="">
                                </span>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-start justify-content-between mb-0">
                                        <div class=" fs-13 fw-semibold">
                                            <a href="{{ url('cart') }}">Cactus mini plant</a>
                                        </div>
                                        <div>
                                            <a aria-label="anchor" href="javascript:void(0);"
                                                class="header-cart-remove float-end dropdown-item-close"><i
                                                    class="ti ti-trash"></i></a>
                                        </div>
                                    </div>
                                    <div class="min-w-fit-content align-items-start">
                                        <div class=" fw-normal fs-12 text-muted">Quantity:02</div>
                                        <div class="d-flex  align-items-center">
                                            <span class=" fw-semibold fs-16">$1,229</span>
                                            <p class="text-muted text-decoration-line-through ms-1 op-6 fs-12 mb-0">
                                                $1,799</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dropdown-item border-bottom">
                            <div class="d-flex align-items-start cart-dropdown-item">
                                <span class="avatar avatar-xl bd-gray-200 p-1">
                                    <img src="{{ asset('build/assets/images/ecommerce/png/15.png') }}"
                                        alt="">
                                </span>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-start justify-content-between mb-0">
                                        <div class=" fs-13 fw-semibold">
                                            <a href="{{ url('cart') }}">Sports shoes for men</a>
                                        </div>
                                        <div>
                                            <a aria-label="anchor" href="javascript:void(0);"
                                                class="header-cart-remove float-end dropdown-item-close"><i
                                                    class="ti ti-trash"></i></a>
                                        </div>
                                    </div>
                                    <div class="min-w-fit-content align-items-start">
                                        <div class=" fw-normal fs-12 text-muted">Quantity:01</div>
                                        <div class="d-flex  align-items-center">
                                            <span class=" fw-semibold fs-16">$10,229</span>
                                            <p class="text-muted text-decoration-line-through ms-1 op-6 fs-12 mb-0">
                                                $799</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dropdown-item border-bottom">
                            <div class="d-flex align-items-start cart-dropdown-item">
                                <span class="avatar avatar-xl bd-gray-200 p-1">
                                    <img src="{{ asset('build/assets/images/ecommerce/png/40.png') }}"
                                        alt="">
                                </span>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-start justify-content-between mb-0">
                                        <div class=" fs-13 fw-semibold">
                                            <a href="{{ url('cart') }}">Pink color smart watch </a>
                                        </div>
                                        <div>
                                            <a aria-label="anchor" href="javascript:void(0);"
                                                class="header-cart-remove float-end dropdown-item-close"><i
                                                    class="ti ti-trash"></i></a>
                                        </div>
                                    </div>
                                    <div class="min-w-fit-content align-items-start">
                                        <div class=" fw-normal fs-12 text-muted">Quantity:03</div>
                                        <div class="d-flex  align-items-center">
                                            <span class=" fw-semibold fs-16">$5,500</span>
                                            <p class="text-muted text-decoration-line-through ms-1 op-6 fs-12 mb-0">
                                                $599</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dropdown-item border-bottom">
                            <div class="d-flex align-items-start cart-dropdown-item">
                                <span class="avatar avatar-xl bd-gray-200 p-1">
                                    <img src="{{ asset('build/assets/images/ecommerce/png/8.png') }}" alt="">
                                </span>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-start justify-content-between mb-0">
                                        <div class=" fs-13 fw-semibold">
                                            <a href="{{ url('cart') }}">Red Leafs plant </a>
                                        </div>
                                        <div>
                                            <a aria-label="anchor" href="javascript:void(0);"
                                                class="header-cart-remove float-end dropdown-item-close"><i
                                                    class="ti ti-trash"></i></a>
                                        </div>
                                    </div>
                                    <div class="min-w-fit-content align-items-start">
                                        <div class=" fw-normal fs-12 text-muted">Quantity:01</div>
                                        <div class="d-flex  align-items-center">
                                            <span class=" fw-semibold fs-16">$15,300</span>
                                            <p class="text-muted text-decoration-line-through ms-1 op-6 fs-12 mb-0">
                                                $799</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dropdown-item">
                            <div class="d-flex align-items-start cart-dropdown-item">
                                <span class="avatar avatar-xl bd-gray-200 p-1">
                                    <img src="{{ asset('build/assets/images/ecommerce/png/11.png') }}"
                                        alt="">
                                </span>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-start justify-content-between mb-0">
                                        <div class=" fs-13 fw-semibold">
                                            <a href="{{ url('cart') }}">Good luck mini plant</a>
                                        </div>
                                        <div>
                                            <a aria-label="anchor" href="javascript:void(0);"
                                                class="header-cart-remove float-end dropdown-item-close"><i
                                                    class="ti ti-trash"></i></a>
                                        </div>
                                    </div>
                                    <div class="min-w-fit-content align-items-start">
                                        <div class=" fw-normal fs-12 text-muted">Quantity:02</div>
                                        <div class="d-flex  align-items-center">
                                            <span class=" fw-semibold fs-16">$600</span>
                                            <p class="text-muted text-decoration-line-through ms-1 op-6 fs-12 mb-0">$99
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="p-3 empty-header-item border-top">
                        <div class="d-grid">
                            <a href="{{ url('checkout') }}" class="btn btn-primary">Checkout</a>
                        </div>
                    </div>
                    <div class="p-5 empty-item d-none">
                        <div class="text-center">
                            <span class="avatar avatar-xxl avatar-rounded bg-warning-transparent">
                                <i class="bx bx-cart bx-tada fs-2"></i>
                            </span>
                            <h6 class="fw-bold mb-2 mt-3">Your Cart is Empty</h6>
                            <a href="{{ url('products') }}" class="btn btn-primary btn-wave btn-sm m-1"
                                data-abc="true">continue shopping <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <!-- End::main-header-dropdown -->
            </div> --}}
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element notifications-dropdown">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                    <i class="bx bx-bell bx-flip-horizontal header-link-icon ionicon"></i>
                    <span class="badge bg-info rounded-pill header-icon-badge pulse pulse-secondary"
                        id="notification-icon-badge">{{ getNotifications(auth('admin')->user()->id)->count() ?? 0 }}</span>
                </a>
                <div class="main-header-dropdown dropdown-menu border-0 dropdown-menu-end">
                    <div class="p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fs-17 fw-semibold">@lang('sidebar.Notifications')</p>
                            <span class="badge bg-secondary-transparent" id="notifiation-data">
                                {{ getNotifications(auth('admin')->user()->id)->count() ?? 0 }}
                            </span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div id="notification-list">
                        @foreach (getNotifications(auth('admin')->user()->id) as $notification)
                            <a href="{{ route('notifications.markAsRead', ['id' => $notification->id, 'user_id' => auth('admin')->user()->id]) }}" class="dropdown-item">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bx bx-bell bx-tada bx-md text-primary"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">
                                            {{ $notification->title_ar }}
                                        </p>
                                        <small class="text-muted"> {{ $notification->description_ar }}</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- End::header-element -->
            <div class="d-flex header-settings header-shortcuts-dropdown">
                <a aria-label="anchor" href="javascript:void(0);" class=" header-link nav-link icon"
                    data-bs-toggle="offcanvas" data-bs-target="#apps" aria-controls="apps">
                    <i class="bx bx-category  header-link-icon"></i>
                </a>
            </div>

            <div class="offcanvas offcanvas-end wd-330" tabindex="-1" id="apps" aria-labelledby="appsLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 id="appsLabel" class="mb-0 fs-18">Related Apps</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"> <i class="bx bx-x   apps-btn-close"></i></button>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ url('full-calendar') }}">
                                <div class="text-center p-3 related-app border">
                                    <span class="avatar fs-23 bg-success-transparent p-2 mb-2">
                                        <i class="bx bx-calendar text-success"></i>
                                    </span>
                                    <span class="d-block fs-13 text-muted fw-semibold">Calendar</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('mail') }}">
                                <div class="text-center p-3 related-app border">
                                    <span class="avatar  fs-23 bg-info-transparent p-2 mb-2">
                                        <i class="bx bx-envelope  text-info"></i>
                                    </span>
                                    <span class="d-block fs-13 text-muted fw-semibold">Mail</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('profile') }}">
                                <div class="text-center p-3 related-app border">
                                    <span class="avatar bg-warning-transparent fs-23 bg p-2 mb-2">
                                        <i class="bx bx-user  text-warning"></i>
                                    </span>
                                    <span class="d-block fs-13 text-muted fw-semibold">Profile</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('chat') }}">
                                <div class="text-center p-3 related-app border">
                                    <span class="avatar    bg-pink-transparent fs-23 bg p-2 mb-2">
                                        <i class="bx bx-chat text-pink"></i>
                                    </span>
                                    <span class="d-block fs-13 text-muted fw-semibold">Chat</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('contacts') }}">
                                <div class="text-center p-3 related-app border">
                                    <span class="avatar    bg-secondary-transparent fs-23 bg p-2 mb-2">
                                        <i class="bx bx-phone text-secondary"></i>
                                    </span>
                                    <span class="d-block fs-13 text-muted fw-semibold">Contacts</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ url('mail-settings') }}">
                                <div class="text-center p-3 related-app border">
                                    <span class="avatar    bg-teal-transparent fs-23 bg p-2 mb-2">
                                        <i class="bx bx-cog text-teal"></i>
                                    </span>
                                    <span class="d-block fs-13 text-muted fw-semibold">Settings</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Start::header-element -->

            <!-- Start::header-element -->
            <div class="header-element header-fullscreen">
                <!-- Start::header-link -->
                <a aria-label="anchor" onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                    <i class="bx bx-fullscreen header-link-icon  full-screen-open"></i>
                    <i class="bx bx-exit-fullscreen header-link-icon  full-screen-close  d-none"></i>
                </a>
                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="d-flex header-settings">
                <a aria-label="anchor" href="javascript:void(0);" class=" header-link nav-link icon me-1"
                    data-bs-toggle="offcanvas" data-bs-target="#sidebar-right" aria-controls="sidebar-right">
                    <i class="bx bx-slider header-link-icon"></i>
                </a>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element mainuserProfile">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="d-sm-flex wd-100p">
                            <div class="avatar avatar-sm"><img alt="avatar" class="rounded-circle"
                                    src="{{ asset('build/assets/images/images.png') }}"></div>
                            <div class="ms-2 my-auto d-none d-xl-flex">
                                <h6 class=" font-weight-semibold mb-0 fs-13 user-name d-sm-block d-none">
                                    {{ auth('admin')->user()->name }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <ul class="dropdown-menu  border-0 main-header-dropdown  overflow-hidden header-profile-dropdown"
                    aria-labelledby="mainHeaderProfile">
                    <li><a class="dropdown-item border-bottom" href="{{ url('profile') }}"><i
                                class="fs-13 me-2 bx bx-user"></i>Profile</a></li>
                    <li><a class="dropdown-item border-bottom" href="{{ url('mail') }}"><i
                                class="fs-13 me-2 bx bx-comment"></i>Message</a></li>
                    <li><a class="dropdown-item border-bottom" href="{{ url('mail-settings') }}"><i
                                class="fs-13 me-2 bx bx-cog"></i>Settings</a></li>
                    <li><a class="dropdown-item border-bottom" href="{{ url('faqs') }}"><i
                                class="fs-13 me-2 bx bx-help-circle"></i>Help</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="display: none;">
                            @csrf
                        </form>
                        <a class="dropdown-item" href="#"
                            onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                            <i class="fs-13 me-2 bx bx-arrow-to-right"></i> Log Out
                        </a>
                    </li>
                </ul>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link|switcher-icon -->
                <a aria-label="anchor" href="javascript:void(0);" class="header-link switcher-icon ms-1"
                    data-bs-toggle="offcanvas" data-bs-target="#switcher-canvas">
                    <i class="bx bx-cog bx-spin header-link-icon"></i>
                </a>
                <!-- End::header-link|switcher-icon -->
            </div>
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-right -->

    </div>
    <!-- End::main-header-container -->

</header>

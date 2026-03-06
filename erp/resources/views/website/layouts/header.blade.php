<header class="fixed-top">
    <div class="container @if (Request::routeIs('home')) @else second-header @endif">
        <nav class="navbar navbar-expand-lg navbar-light ">
            <a class="navbar-service" href="{{ route('home') }}">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}">
            </a>

            @if (session('locale') == 'ar')
                <a class="navbar-toggler" href="{{ route('set-locale', 'en') }}">
                    @lang('header.en')

                </a>
            @else
                <a class="navbar-toggler" href="{{ route('set-locale', 'ar') }}">
                    @lang('header.ar')

                </a>
            @endif
            <?php /*<div class="collapse navbar-collapse flex-column" id="navbarSupportedContent">
                <ul class="navbar-nav mb-2 mb-lg-0 align-items-center justify-content-between">
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="modal" data-bs-target="#deliveryModal">
                            <button class="btn white fw-bold d-flex justify-content-between align-items-center">
                                <figure class="mb-0"><img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/food-delivery 1.svg') }}" alt="">
                                </figure>
                                <span>@lang('header.deliveryTo') </span>
                            </button>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link">
                            <button class="btn white fw-bold d-flex justify-content-between align-items-center">
                                <figure class="mb-0"><img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/take-away 1.svg') }}" alt="">
                                </figure>
                                <span>  @lang('header.pickup')</span>
                            </button>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link">
                            <button class="btn white fw-bold d-flex justify-content-between align-items-center"
                                data-bs-toggle="modal" data-bs-target="#table-reservation">
                                <figure class="mb-0">
                                    <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table 1.svg') }}" alt="">
                                </figure>
                                <span> @lang('header.reservation')</span>
                            </button>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <button class="btn white fw-bold d-flex justify-content-between" data-bs-toggle="modal"
                                data-bs-target="#deliveryModal">
                                <span>
                                    <i class="fas fa-map-marker-alt ms-2 main-color"></i>
                                    @lang('header.startOrder')
                                </span>
                                <span class="select-sm">
                                    @lang('header.choose')
                                </span>
                            </button>
                        </a>
                    </li>

                    <li class="nav-item">
                        @auth('client')
                            @if (Auth::guard('client')->user()->flag == 'client')
                                <a data-bs-toggle="modal" data-bs-target="#profileModal"><i
                                        class="fas fa-user-circle main-color"></i>
                                    <span>{{ Auth::guard('client')->user()->name }} </span>
                                </a>
                            @else
                                <a class="nav-link btn align-items-center" data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                    <i class="fas fa-user-circle"></i>
                                    <span>@lang('header.login')</span>
                                </a>
                            @endif
                        @else
                            <a class="nav-link btn align-items-center" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-user-circle"></i>
                                <span>@lang('header.login')</span>
                            </a>
                        @endauth
                    </li>
                </ul>
                <div class="d-flex justify-content-between w-100">
                    <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                        <li class="nav-item  {{ Request::routeIs('home') ? 'active' : '' }}" aria-current="page">
                            <a class="nav-link" href="{{ route('home') }}"> @lang('header.home')</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="modal" data-bs-target="#branchesModal">
                                @lang('header.branches')</a>

                        </li>
                        <li class="nav-item {{ Request::routeIs('menu') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('menu') }}"> @lang('header.menu')</a>

                        </li>
                        <li class="nav-item {{ Request::routeIs('contactUs') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('contactUs') }}">
                                @lang('header.contactUs')
                        </li>
                    </ul>
                    <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                        <li class="nav-item">
                            <a class="nav-link cart-icon" href="{{ route('cart') }}">
                                <i class="fas fa-shopping-cart"></i>
                                <span id="cart-count" class="badge bg-danger rounded-pill">0</span>
                            </a>
                        </li>
                        <li class="nav-item">

                            @if (session('locale') == 'ar')
                                <a class="nav-link" href="{{ route('set-locale', 'en') }}">
                                    @lang('header.en')

                                </a>
                            @else
                                <a class="nav-link" href="{{ route('set-locale', 'ar') }}">
                                    @lang('header.ar')

                                </a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div> */
            ?>


            <div class="collapse navbar-collapse flex-column" id="navbarSupportedContent">
                <div class="d-flex justify-content-between w-100">
                    <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                        <!-- <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="modal" data-bs-target="#deliveryModal">
                                <button class="btn white fw-bold d-flex justify-content-between align-items-center">
                                    <figure class="mb-0"><img
                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/food-delivery 1.svg') }}"
                                            alt="">
                                    </figure>
                                    <span>@lang('header.deliveryTo') </span>
                                </button>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link">
                                <button class="btn white fw-bold d-flex justify-content-between align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#branchSelectionModal">
                                    <figure class="mb-0"><img
                                            src="{{ asset('front/AlKout-Resturant/SiteAssets/images/take-away 1.svg') }}"
                                            alt="">
                                    </figure>
                                    <span> @lang('header.pickup')</span>
                                </button>
                            </a>
                        </li>
                            <li class="nav-item">
                                <a class="nav-link">
                                    <button class="btn white fw-bold d-flex justify-content-between align-items-center"
                                        data-bs-toggle="modal" data-bs-target="#table-reservation"
                                        id="view_table_reservation">
                                        <figure class="mb-0">
                                            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/table 1.svg') }}"
                                                alt="">
                                        </figure>
                                        <span> @lang('header.reservation')</span>
                                    </button>
                                </a>
                            </li> -->
                    </ul>
                    <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                        <li class="nav-item">
                            <!-- <a class="nav-link" href="#">
                                <button class="btn white fw-bold d-flex justify-content-between" data-bs-toggle="modal"
                                    data-bs-target="#deliveryModal">
                                    <span>
                                        <i class="fas fa-map-marker-alt ms-2 main-color"></i>
                                        @lang('header.startOrder')
                                    </span>
                                    <span class="select-sm">
                                        @lang('header.choose')
                                    </span>
                                </button>
                            </a> -->
                        </li>
                        <li class="nav-item">
                            @auth('client')
                                @if (Auth::guard('client')->user()->flag == 'client')
                                    <a data-bs-toggle="modal" data-bs-target="#profileModal"><i
                                            class="fas fa-user-circle main-color"></i>
                                        <span>{{ Auth::guard('client')->user()->name }} </span>
                                    </a>
                                @else
                                    <a class="nav-link btn align-items-center text-light" data-bs-toggle="modal"
                                        data-bs-target="#loginModal">
                                        <i class="fas fa-user-circle"></i>
                                        <span>@lang('header.login')</span>
                                    </a>
                                @endif
                            @else
                                <a class="nav-link btn align-items-center text-light" data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                    <i class="fas fa-user-circle"></i>
                                    <span>@lang('header.login')</span>
                                </a>
                            @endauth
                        </li>
                    </ul>
                </div>
                <div class="d-flex justify-content-between w-100">
                    <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                        <li class="nav-item  {{ Request::routeIs('home') ? 'active' : '' }}" aria-current="page">
                            <a class="nav-link" href="{{ route('home') }}"> @lang('header.home')</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="modal" data-bs-target="#branchesModal">
                                @lang('header.branches')</a>

                        </li>
                        <li class="nav-item {{ Request::routeIs('menu') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('menu') }}"> @lang('header.menu')</a>

                        </li>
                        <li class="nav-item {{ Request::routeIs('contactUs') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('contactUs') }}">
                                @lang('header.contactUs')
                        </li>
                    </ul>
                    <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
                        {{-- <li class="nav-item">
                            <a class="nav-link cart-icon" href="{{ route('cart') }}" id="cart-header">
                                <i class="fas fa-shopping-cart text-light"></i>
                                <span id="cart-count" class="badge bg-danger  rounded-pill">0</span>
                            </a>
                        </li> --}}
                        <li class="nav-item">

                            @if (session('locale') == 'ar')
                                <a class="nav-link" href="{{ route('set-locale', 'en') }}">
                                    @lang('header.en')

                                </a>
                            @else
                                <a class="nav-link" href="{{ route('set-locale', 'ar') }}">
                                    @lang('header.ar')

                                </a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>


        </nav>
    </div>
</header>

<div class="mobile-nav d-lg-none d-flex fixed-bottom w-100">
    <div class="row m-0 w-100">
        <div class="col-3 nav-item">
            @auth('client')
                @if (Auth::guard('client')->user()->flag == 'client')
                    <a class="nav-link" data-bs-toggle="modal" data-bs-target="#profileModal"><i
                            class="fas fa-user-circle main-color"></i>
                        <span>{{ Auth::guard('client')->user()->name }} </span>
                    </a>
                @else
                    <a class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-user-circle"></i>
                        <span>@lang('header.login')</span>
                    </a>
                @endif
            @else
                <a class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-user-circle"></i>
                    <span>@lang('header.login')</span>
                </a>
            @endauth
        </div>
        <div class="col-2 nav-item  {{ Request::routeIs('home') ? 'active' : '' }}" aria-current="page">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="fas fa-home"></i>
                <span>@lang('header.home')</span>
            </a>
        </div>
        <div class="col-3 nav-item {{ Request::routeIs('menu') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('menu') }}">
                <i class="fas fa-clipboard-list"></i>
                <span> @lang('header.menu')</span>

            </a>
        </div>
        {{-- <div class="col-2 nav-item">
            <a class="nav-link" href="{{ route('cart') }}">
                <i class="fas fa-shopping-cart"></i>
                <span> @lang('header.cart')</span>

            </a>
        </div> --}}
        <!-- <div class="col-2 nav-item">
        <a class="nav-link" data-bs-toggle="modal" data-bs-target="#branchesModal">
          <i class="fas fa-map-marked-alt"></i>
          <span> الفروع</span>
        </a>
      </div> -->

        <div class="col-2 nav-item">
            <a class="nav-link" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom"
                aria-controls="offcanvasBottom">
                <i class="fas fa-layer-group"></i>
                <span>
                    @lang('header.more')
                </span>
            </a>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold" id="offcanvasBottomLabel">@lang('header.more')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="list-unstyled p-0">
            <li class="nav-item">
                <a class="nav-link " href="#" data-bs-toggle="modal" data-bs-target="#deliveryModal">
                    <span><i class="fas fa-map-marker-alt ms-2 main-color"></i>
                        @lang('header.startOrder')
                    </span>
                </a>
            </li>
            <li class="nav-item {{ Request::routeIs('contactUs') ? 'active' : '' }}">
                <a class="nav-link " href="{{ route('contactUs') }}">
                    @lang('header.contactUs')
            </li>

            <li class="nav-item">

                @if (session('locale') == 'ar')
                    <a class="nav-link" href="{{ route('set-locale', 'en') }}">
                        @lang('header.en')

                    </a>
                @else
                    <a class="nav-link" href="{{ route('set-locale', 'ar') }}">
                        @lang('header.ar')

                    </a>
                @endif
            </li>

        </ul>
    </div>
</div>


{{-- <header class="fixed-top">
    <nav class="navbar navbar-expand-lg navbar-light ">
        <div class="container  @if (Request::routeIs('home')) @else second-header @endif">

            <a class="navbar-service" href="{{ route('home') }}">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}">
            </a>
            <button class="navbar-toggler" type="button" onclick="openNav()">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <button class="btn white fw-bold d-flex justify-content-between" data-bs-toggle="modal"
                                data-bs-target="#deliveryModal">
                                <span><i class="fas fa-map-marker-alt ms-2"></i>
                                    @lang('header.deliveryTo')
                                </span>
                                <span class="select-sm">
                                    @lang('header.choose')
                                </span>
                            </button>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::routeIs('home') ? 'active' : '' }}" aria-current="page">
                        <a class="nav-link " href="{{ route('home') }}">
                            @lang('header.home')
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="modal" data-bs-target="#branchesModal">
                            @lang('header.branches')</a>
                    </li>
                    <li class="nav-item {{ Request::routeIs('menu') ? 'active' : '' }}">
                        <a class="nav-link " href="{{ route('menu') }}">
                            @lang('header.menu')
                        </a>
                    </li>
                    <li class="nav-item {{ Request::routeIs('contactUs') ? 'active' : '' }}">
                        <a class="nav-link " href="{{ route('contactUs') }}">
                            @lang('header.contactUs')
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link cart-icon" href="{{ route('cart') }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cart-count" class="badge bg-danger rounded-pill">0</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        @if (session('locale') == 'ar')
                            <a class="nav-link" href="{{ route('set-locale', 'en') }}">
                                @lang('header.en')

                            </a>
                        @else
                            <a class="nav-link" href="{{ route('set-locale', 'ar') }}">
                                @lang('header.ar')

                            </a>
                        @endif
                    </li>

                    @auth('client')
                        @if (Auth::guard('client')->user()->flag == 'client')
                            <li class="nav-item">
                                <a class="nav-link  align-items-center" data-bs-toggle="modal"
                                    data-bs-target="#profileModal"><i class="fas fa-user-circle main-color"></i>
                                    <span>{{ Auth::guard('client')->user()->name }} </span>
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link btn align-items-center" data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                    <i class="fas fa-user-circle"></i>
                                    <span>@lang('header.login')</span>
                                </a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item">
                            <a class="nav-link btn align-items-center" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-user-circle"></i>
                                <span>@lang('header.login')</span>
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>


        </div>
    </nav>
    <div class="mobNav d-lg-none d-block ">
        <div id="sidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <div class="overlay-content">
                <a href="{{ route('home') }}"> @lang('header.home') </a>
                <a href="{{ route('menu') }}"> @lang('header.menu') </a>
                <a href="{{ route('contactUs') }}"> @lang('header.contactUs') </a>
                <a href="{{ route('cart') }}"> <i class="fas fa-shopping-cart"></i>
                </a>

                @if (session('locale') == 'ar')
                    <a class="nav-link" href="{{ route('set-locale', 'en') }}">
                        @lang('header.en')

                    </a>
                @else
                    <a class="nav-link" href="{{ route('set-locale', 'ar') }}">
                        @lang('header.ar')

                    </a>
                @endif

                @auth('client')
                    @if (Auth::guard('client')->user()->flag == 'client')
                        <a data-bs-toggle="modal" data-bs-target="#profileModal"><i
                                class="fas fa-user-circle main-color"></i>
                            <span>{{ Auth::guard('client')->user()->name }} </span>
                        </a>
                    @else
                        <a data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-user-circle"></i>
                            <span>@lang('header.login')</span>
                        </a>
                    @endif
                @else
                    <a data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-user-circle"></i>
                        <span>@lang('header.login')</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>

</header> --}}
<script></script>

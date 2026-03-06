
            <header class="app-header">

                <!-- Start::main-header-container -->
                <div class="main-header-container container-fluid">

                    <!-- Start::header-content-left -->
                    <div class="header-content-left">

                        <!-- Start::header-element -->
                        <div class="header-element">
                            <div class="horizontal-logo">
                                <a href="{{url('index')}}" class="header-logo">
                                    <img src="{{asset('build/assets/images/brand-logos/toggle-logo.png')}}" alt="logo" class="toggle-logo">
                                    <img src="{{asset('build/assets/images/brand-logos/toggle-dark.png')}}" alt="logo" class="toggle-dark">
                                </a>
                            </div>
                        </div>
                        <!-- End::header-element -->

                        <!-- Start::header-element -->
                        <div class="header-element">
                            <!-- Start::header-link -->
                            <a aria-label="anchor" href="javascript:void(0);" class="sidemenu-toggle header-link" data-bs-toggle="sidebar">
                                <span class="open-toggle">
                                    <i class="ri-menu-3-line fs-20"></i>
                                </span>
                            </a>
                            <!-- End::header-link -->
                        </div>
                        <!-- End::header-element -->

                    </div>
                    <!-- End::header-content-left -->

                    <!-- Start::header-content-right -->
                    <div class="header-content-right">

                        <!-- Start::header-element -->
                        <div class="header-element align-items-center">
                            <!-- Start::header-link|switcher-icon -->
                            <div class="btn-list d-lg-none d-block">
                                <a href="{{url('signup-basic')}}" class="btn btn-primary-light d-sm-inline d-none">
                                    Sign Up
                                </a>
                                <button type="button" class="btn btn-secondary">
                                    Get Started
                                </button>
                                <button aria-label="anchor" type="button" class="btn btn-icon btn-success" data-bs-toggle="offcanvas" data-bs-target="#switcher-canvas">
                                    <i class="ri-settings-3-line"></i>
                                </button>
                            </div>
                            <!-- End::header-link|switcher-icon -->
                        </div>
                        <!-- End::header-element -->

                    </div>
                    <!-- End::header-content-right -->

                </div>
                <!-- End::main-header-container -->

            </header>
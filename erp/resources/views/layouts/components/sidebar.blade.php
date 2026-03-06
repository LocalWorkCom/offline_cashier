<aside class="app-sidebar" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="{{ route('dashboard.home') }}" class="header-logo">
            <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
            <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
            <img src="{{ asset('build/assets/images/brand-logos/desktop.png') }}" alt="logo" class="desktop-dark">
            <img src="{{ asset('build/assets/images/brand-logos/toggle-dark.png') }}" alt="logo"
                class="toggle-dark">
        </a>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>

            <ul class="main-menu">
                <!-- Start::slide__category -->
                <li class="slide__category"><span class="category-name">@lang('sidebar.Main')</span></li>
                <!-- End::slide__category -->
                @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
                    <li class="slide">
                        <a href="{{ route('dashboard.home') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.Dashboards')</span>
                        </a>
                    </li>
                @endif

                @if (auth('admin')->user()->hasPermissionTo('view users', 'admin'))
                    <li class="slide">
                        <a href="{{ route('client.index') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bi bi-person'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.clients')</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view floors', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view floor_partitions', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view tables', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view cuisines', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view dishes', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view dish_categories', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view recipes', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view branch_menu_categories', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view branch_menus', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view branch_menu_addons', 'admin') ||
                        auth('admin')->user()->hasPermissionTo('view branch_menu_sizes', 'admin'))

                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bi bi-shop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.Resturants') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view floors', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view floor_partitions', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view tables', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view cuisines', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view dishes', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view dish_categories', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view recipes', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view branch_menu_categories', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view branch_menus', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view branch_menu_addons', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view branch_menu_sizes', 'admin'))

                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        @lang('sidebar.Branches')
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('branches.list') }}"
                                                    class="side-menu__item">@lang('sidebar.Branches')
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth('admin')->user()->hasPermissionTo('view branch_settings', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('branch_settings.list') }}"
                                                    class="side-menu__item">@lang('sidebar.branch_settings')
                                                </a>
                                            </li>
                                        @endif

                                        <li class="slide has-sub">
                                            <a href="javascript:void(0);" class="side-menu__item">@lang('sidebar.Floors')
                                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                            <ul class="slide-menu child2">
                                                @if (auth('admin')->user()->hasPermissionTo('view floors', 'admin'))
                                                    <li class="slide">
                                                        <a href="{{ route('floors.list') }}"
                                                            class="side-menu__item">@lang('sidebar.Floors') </a>
                                                    </li>
                                                @endif


                                                <li class="slide has-sub">
                                                    <a href="javascript:void(0);"
                                                        class="side-menu__item">@lang('sidebar.FloorPartition')
                                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                    <ul class="slide-menu child3">
                                                        @if (auth('admin')->user()->hasPermissionTo('view floor_partitions', 'admin'))
                                                            <li class="slide">
                                                                <a href="{{ route('floorPartitions.list') }}"
                                                                    class="side-menu__item">@lang('sidebar.FloorPartition') </a>
                                                            </li>
                                                        @endif

                                                        @if (auth('admin')->user()->hasPermissionTo('view tables', 'admin'))
                                                            <li class="slide">
                                                                <a href="{{ route('tables.list') }}"
                                                                    class="side-menu__item">@lang('sidebar.Tables') </a>
                                                            </li>
                                                        @endif

                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view cuisines', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view dishes', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view dish_categories', 'admin') ||
                                    auth('admin')->user()->hasPermissionTo('view recipes', 'admin'))

                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">@lang('sidebar.Dishes')
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        @if (auth('admin')->user()->hasPermissionTo('view cuisines', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('dashboard.cuisines.index') }}"
                                                    class="side-menu__item">@lang('sidebar.Cuisines')
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth('admin')->user()->hasPermissionTo('view dishes', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('dashboard.dishes.index') }}"
                                                    class="side-menu__item">@lang('sidebar.Dishes')
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth('admin')->user()->hasPermissionTo('view dish_categories', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('dashboard.dish-categories.index') }}"
                                                    class="side-menu__item">@lang('sidebar.DishesCategory')
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth('admin')->user()->hasPermissionTo('view recipes', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('dashboard.recipes.index') }}"
                                                    class="side-menu__item">@lang('sidebar.Recipes')
                                                </a>
                                            </li>
                                        @endif
                                        @if (auth('admin')->user()->hasPermissionTo('view recipes', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('dashboard.addons.index') }}"
                                                    class="side-menu__item">@lang('sidebar.Addons')
                                                </a>
                                            </li>

                                            <li class="slide">
                                                <a href="{{ route('dashboard.addon_categories.index') }}"
                                                    class="side-menu__item">@lang('sidebar.AddonCategories')
                                                </a>
                                            </li>
                                        @endif

                                    </ul>
                                </li>
                            @endif

                        </ul>

                    </li>
                @endif
                <!-- End::slide -->
                <!-- End::slide__category -->
                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view employees', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view positions', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view departments', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view shifts', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view violations', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view timetables', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view excuses', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view leave_requests', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view leave_types', 'admin')))

                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.HR SYSTEM')</span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1">
                                <a href="javascript:void(0)">@lang('sidebar.HR SYSTEM') </a>
                            </li>
                            <!-- Start::slide -->
                            @if (auth('admin')->user()->hasPermissionTo('view employees', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('employees.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Employee') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view hierarchies', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('employee.hierarchies') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.EmployeeHierarchies') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view violations', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('violations.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.violations') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view vehicles', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('vehicles.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.vehicles') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view chef-cuisine', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('chefCuisine.index') }}" class="side-menu__item">

                                        <span class="side-menu__label">@lang('sidebar.chefCategory') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view positions', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('positions.index') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Positions') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view job_types', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('job_types.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.JobTypes') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view departments', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('departments.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Departments') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view shifts', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('shifts.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Shifts') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view timetables', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('timeTable.index') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Timetables') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view timetables', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('employeeSchedules.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Schedule') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view timetables', 'admin'))
                                <li class="slide">
                                    <a href="{{ url('Attendance') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Attendance') </span>
                                    </a>
                                </li>
                            @endif
                            {{-- update with correct permission --}}
                            @if (auth('admin')->user()->hasPermissionTo('view timetables', 'admin'))
                                <li class="slide">
                                    <a href="{{ url('FingerDevice') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.FingerDevice') </span>
                                    </a>
                                </li>
                            @endif

                            <!-- Start::slide -->
                            @if (auth('admin')->user()->hasPermissionTo('view excuses', 'admin'))
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.excuses') </span>
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child2">
                                        <li class="slide">
                                            <a href="{{ url('blog') }}" class="side-menu__item">@lang('sidebar.excuses')
                                            </a>
                                        </li>

                                        <li class="slide">
                                            <a href="{{ url('blog-details') }}"
                                                class="side-menu__item">@lang('sidebar.excusesReport')
                                            </a>
                                        </li>

                                        <li class="slide">
                                            <a href="{{ url('blog-create') }}"
                                                class="side-menu__item">@lang('sidebar.excuseslogs')
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif


                            @if (auth('admin')->user() &&
                                    (auth('admin')->user()->hasPermissionTo('view leave_requests', 'admin') ||
                                        auth('admin')->user()->hasPermissionTo('view leave_types', 'admin') ||
                                        auth('admin')->user()->hasPermissionTo('view leave_settings', 'admin') ||
                                        auth('admin')->user()->hasPermissionTo('view leave_setting_positions', 'admin')))
                                <!-- Start::slide -->
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Vacation') </span>
                                        <i class="fe fe-chevron-right side-menu__angle"></i>
                                    </a>

                                    <ul class="slide-menu child2">
                                        @if (auth('admin')->user()->hasPermissionTo('view leave_requests', 'admin'))
                                            <!-- <li class="slide">
                                                <a href="{{ url('blog') }}"
                                                    class="side-menu__item">@lang('sidebar.Vacation')
                                                </a>
                                            </li>

                                            <li class="slide">
                                                <a href="{{ url('blog-details') }}"
                                                    class="side-menu__item">@lang('sidebar.VacationReport')
                                                </a>
                                            </li> -->
                                        @endif

                                        @if (auth('admin')->user()->hasPermissionTo('view leave_types', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('leave-types.list') }}"
                                                    class="side-menu__item">@lang('sidebar.VacationTypes')
                                                </a>
                                            </li>
                                        @endif

                                        @if (auth('admin')->user()->hasPermissionTo('view leave_settings', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('leave-settings.list') }}"
                                                    class="side-menu__item">@lang('sidebar.VacationSettings')
                                                </a>
                                            </li>
                                        @endif

                                        @if (auth('admin')->user()->hasPermissionTo('view leave_setting_positions', 'admin'))
                                            <li class="slide">
                                                <a href="{{ route('leave-setting-positions.list') }}"
                                                    class="side-menu__item">@lang('sidebar.LeaveSettingPositions')
                                                </a>
                                            </li>
                                        @endif

                                    </ul>
                                </li>
                            @endif
                            <!-- End::slide -->
                        </ul>
                    </li>
                @endif
                {{-- @if (auth('admin')->user()->can('view stores') || auth('admin')->user()->can('view opening_balance') || auth('admin')->user()->can('view lines') || auth('admin')->user()->can() || auth('admin')->user()->can()) --}}
                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view stores', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view opening_balance', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view lines', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view shelves', 'admin')))
                    <!-- <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-shop-window"></i> </span>
                            <span class="side-menu__label">@lang('sidebar.Store') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view stores')
    <li class="slide side-menu__label1">
                                                                                                <a href="javascript:void(0)">@lang('sidebar.Store') </a>
                                                                                            </li>
@endif
                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view opening_balance')
    <li class="slide">
                                                                                                <a href="{{ url('OppeningBalance') }}" class="side-menu__item">
                                                                                                    <span class="side-menu__label">@lang('sidebar.OppeningBalance') </span>
                                                                                                </a>
                                                                                            </li>
@endif

                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    @lang('sidebar.Lines')
                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                <ul class="slide-menu child2">
                                  @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view lines')
    <li class="slide">
                                                                                                        <a href="#" class="side-menu__item">@lang('sidebar.Lines')</a>
                                                                                                    </li>
@endif

                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">@lang('sidebar.Division')
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child3">
                                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view divisions')
    <li class="slide">
                                                                                                                <a href="#" class="side-menu__item">@lang('sidebar.Division')</a>
                                                                                                            </li>
@endif

                                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view shelves')
    <li class="slide">
                                                                                                                <a href="javascript:void(0);"
                                                                                                                    class="side-menu__item">@lang('sidebar.Shilves')
                                                                                                                </a>
                                                                                                            </li>
@endif

                                        </ul>
                                    </li>

                                </ul>
                            </li>
                        </ul> -->
                    <!-- End::slide -->
                    <!-- </li> -->
                @endif

                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view products', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view categories', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view brands', 'admin')))

                    <!-- End::slide__category -->
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bx bx-purchase-tag-alt"></i> </span>
                            <span class="side-menu__label">@lang('sidebar.Invoices') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">

                            @if (auth('admin')->user()->hasPermissionTo('view brands', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('brands.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Brand') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view categories', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('categories.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Category') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view products', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('products.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Products') </span>
                                    </a>
                                </li>
                            @endif



                        </ul>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view cashier_machines', 'admin'))
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-receipt"></i> </span>
                            <span class="side-menu__label">@lang('sidebar.cashier_machines') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">

                            @if (auth('admin')->user()->hasPermissionTo('view cashier_machines', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('dashboard.cashierMachines.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.cashier_machines') </span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view einvoices_superadmin', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view einvoices', 'admin')))

                    {{-- @if (auth('admin')->user()->can() || auth('admin')->user()->can()) --}}
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-receipt"></i> </span>
                            <span class="side-menu__label">@lang('sidebar.invoices') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">

                            @if (auth('admin')->user()->hasPermissionTo('view einvoices_superadmin', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('dashboard.einvoices.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.eInvoices') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view invoice', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('invoice.index') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.invoices') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view returnInvoiceRequest', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('return-invoice.index') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.returnInvoice') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view einvoices', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('dashboard.einvoices.show') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.show_invoices') </span>
                                    </a>
                                </li>
                                <li class="slide">
                                    <a href="{{ route('dashboard.einvoices.submitted') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.submitted_invoices') </span>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
                @if (auth('admin')->user() && auth('admin')->user()->hasPermissionTo('view orders', 'admin'))
                    {{-- @if (auth('admin')->user()->can()) --}}
                    <!-- End::slide__category -->
                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-receipt"></i> </span>
                            <span class="side-menu__label">@lang('sidebar.orders') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">

                            @if (auth('admin')->user()->hasPermissionTo('view orders', 'admin'))
                                <li class="slide">
                                    <a href="{{ url('dashboard/orders') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.orders') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view returnInvoiceRequest', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('return-invoice-request.index') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.returnInvoiceRequest') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view waiter_request', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('waiterrequest.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('requests.waiterRequests') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view complaints', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('complaints.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.complaints') </span>
                                    </a>
                                </li>
                            @endif
                            @if (auth('admin')->user()->hasPermissionTo('view hanging-orders', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('hanging-orders.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.hanging-orders') </span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view purchase_invoices', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view vendors', 'admin')))

                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-wallet2"></i> </span>
                            <span class="side-menu__label">@lang('sidebar.Purchase') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            @if (auth('admin')->user()->hasPermissionTo('view purchase_invoices', 'admin'))
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">@lang('sidebar.Purchase') </a>
                                </li>
                                <li class="slide">
                                    <a href="{{ route('purchases.index') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Purchase') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view vendors', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('vendors.index') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Vendors') </span>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view coupons', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view discounts', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view offers', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view gifts', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view point_systems', 'admin')))

                    <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-gift"></i> </span>
                            <span class="side-menu__label">@lang('sidebar.Offers/Discounts') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1">
                                <a href="javascript:void(0)">@lang('sidebar.Offers && Discounts') </a>
                            </li>
                            @if (auth('admin')->user()->hasPermissionTo('view offers', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('offers.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Offers') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view coupons', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('coupons.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Coupon') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view discounts', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('discounts.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Discount') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view gifts', 'admin'))
                                <li class="slide">
                                    <a href="{{ route('gifts.list') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.Gifts') </span>
                                    </a>
                                </li>
                            @endif

                            @if (auth('admin')->user()->hasPermissionTo('view point_systems', 'admin'))
                                <li class="slide">
                                    <a href="{{ url('lolaityPoint') }}" class="side-menu__item">
                                        <span class="side-menu__label">@lang('sidebar.lolaityPoint') </span>
                                    </a>
                                </li>
                            @endif
                            <!-- End::Purchase -->
                        </ul>
                    </li>
                @endif
                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view store_transactions', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view product_transactions', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view order_transactions', 'admin')))
                    <!-- <li class="slide has-sub">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class="bi bi-arrow-left-right"></i>                            </span>
                            <span class="side-menu__label">@lang('sidebar.Transactions') </span>
                            <i class="fe fe-chevron-right side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1">
                                <a href="javascript:void(0)">@lang('sidebar.Transactions') </a>
                            </li>

                           Start::slide -->
                    <!-- <ul>
                              @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view store_transactions')
    <li class="slide">
                                                                                                    <a href="{{ url('accordions-collapse') }}"
                                                                                                        class="side-menu__item">@lang('sidebar.StoreTransactions')
                                                                                                    </a>
                                                                                                </li>
@endif
                              @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view product_transactions')
    <li class="slide">
                                                                                                    <a href="{{ url('accordions-collapse') }}"
                                                                                                        class="side-menu__item">@lang('sidebar.ProductTransactions')
                                                                                                    </a>
                                                                                                </li>
@endif

                              @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))
('view order_transactions')
    <li class="slide">
                                                                                                    <a href="{{ url('carousel') }}" class="side-menu__item">@lang('sidebar.OrderTransactions') </a>
                                                                                                </li>
@endif -->

                    <!-- </ul>  -->
                    <!-- End::slide -->
                    <!-- </ul>
                    </li> -->
                @endif
                @if (auth('admin')->user() &&
                        (auth('admin')->user()->hasPermissionTo('view report_best_seller_dishes', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view report_most_customer_place_order', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view report_orders', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view report_delivery_orders', 'admin')))

                    @if (auth('admin')->user()->hasPermissionTo('view report_best_seller_dishes', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view report_most_customer_place_order', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view report_orders', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view report_waiter_requests', 'admin') ||
                            //                        || auth()->user()->can('view report_delivery_orders')
                            auth('admin')->user()->hasPermissionTo('view report_cashier_balances', 'admin') ||
                            auth('admin')->user()->hasPermissionTo('view report_cashier_balance_transactions', 'admin'))
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <span class=" side-menu__icon">
                                    <i class="bx bx-file"></i>
                                </span>
                                <span class="side-menu__label">@lang('sidebar.Reports')</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">@lang('sidebar.Reports') </a>
                                </li>
                                <!-- Start::slide -->
                                @if (auth('admin')->user()->hasPermissionTo('view report_best_seller_dishes', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.best_seller_dish.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.report_best_seller_dishes') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_most_customer_place_order', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.most.customers.list') }}" class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.report_customers') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_orders', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.orders.list') }}" class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.orders_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_cancelled_orders', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.cancelled_orders.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.cancelled_orders_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_customer_service_delivery_orders', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.customer_service_delivery_orders.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.customer_service_delivery_orders_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_delivery_orders', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.delivery.orders.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.delivery_orders_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_branches', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.branches.list') }}" class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.branches_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_cashier_balances', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.cashier_balances.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.cashier_balances_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_cashier_balance_transactions', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.cashier_balance_transactions.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.cashier_balance_transactions_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_hanging_orders', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.hanging-orders.list') }}" class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.hanging_orders_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_feedbacks', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.feedbacks.list') }}" class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.feedbacks_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_table_reservations', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.table_reservations.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.table_reservations_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view waiter_table_service_report', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.waiter_table_service_report.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.waiter_table_service_report') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view delivery_order_report', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.delivery_order_report.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.delivery_order_report') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view delivery_earnings_payments_report', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.delivery_earnings_payments_report.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.delivery_earnings_payments_report') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view delivery_performance_metrics_report', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.delivery_performance_metrics_report.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.delivery_performance_metrics_report') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_cashier_performance', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.cashier_performance.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.report_cashier_performance') </span>
                                        </a>
                                    </li>
                                @endif
                                {{-- @endif --}}
                                @if (auth('admin')->user()->hasPermissionTo('view report_booking_revenue', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.booking_revenue.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.booking_revenue_reports') </span>
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view report_booking_cancellation', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.booking_cancellation.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.booking_cancellation_reports') </span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view report_waiter_requests', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.waiter_requests.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.report_waiter_requests') </span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view report_kitchen_performance', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.kitchen_performance.list') }}"
                                            class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.report_kitchen_performance') </span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view report_branch_safe', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.branch_safe.index') }}" class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.branch_safe') </span>
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view report_branch_safe', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('reports.cashier_branch-safe.list') }}" class="side-menu__item">
                                            <span class="side-menu__label">@lang('sidebar.branch_safe_cashier') </span>
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </li>

                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <span class=" side-menu__icon">
                                    <i class="bi bi-gear"></i> </span>
                                <span class="side-menu__label">@lang('sidebar.Setting') </span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">@lang('sidebar.Setting') </a>
                                </li>
                                @if (auth('admin')->user()->hasPermissionTo('view countries', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('countries.list') }}"
                                            class="side-menu__item">@lang('sidebar.countries')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view nationalities', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('nationality.list') }}"
                                            class="side-menu__item">@lang('sidebar.nationalities')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view currencies', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('currencies.list') }}"
                                            class="side-menu__item">@lang('sidebar.currencies')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view violation_types', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('violation_types.list') }}"
                                            class="side-menu__item">@lang('sidebar.violation_types')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view colors', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('colors.list') }}"
                                            class="side-menu__item">@lang('sidebar.colors')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view hotels', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('hotels.list') }}"
                                            class="side-menu__item">@lang('sidebar.hotels')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view sizes', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('sizes.list') }}"
                                            class="side-menu__item">@lang('sidebar.size')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view units', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('units.list') }}"
                                            class="side-menu__item">@lang('sidebar.Units')
                                        </a>
                                    </li>
                                @endif
                                {{--                          @if (auth('admin')->user()->hasPermissionTo('view order_settings', 'admin')) --}}
                                {{--                                <li class="slide"> --}}
                                {{--                                    <a href="{{ route('order_settings.list') }}" --}}
                                {{--                                        class="side-menu__item">@lang('sidebar.OrderSettings') --}}
                                {{--                                    </a> --}}
                                {{--                                </li> --}}
                                {{--                            @endif --}}
                                @if (auth('admin')->user()->hasPermissionTo('view roles', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('roles.list') }}"
                                            class="side-menu__item">@lang('sidebar.roles')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view permissions', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('permissions.list') }}"
                                            class="side-menu__item">@lang('sidebar.permissions')
                                        </a>
                                    </li>
                                @endif

                                {{-- @can('view Notification')
                            {{-- @can('view Notification')
                                <li class="slide">
                                    <a href="{{ url('Notification') }}" class="side-menu__item">@lang('sidebar.Notification') </a>
                                </li>
                            @endif
                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))('view excuse_settings')
                                <li class="slide">
                                    <a href="{{ url('ExcusesSetting') }}" class="side-menu__item">@lang('sidebar.ExcusesSetting') </a>
                                </li>
                            @endif
                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))('view einvoice_settings')
                                <li class="slide">
                                    <a href="{{ url('invoiceSetting') }}" class="side-menu__item">@lang('sidebar.invoiceSetting') </a>
                                </li>
                            @endif
                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))('view leave_settings')
                                <li class="slide">
                                    <a href="{{ url('leaveSetting') }}" class="side-menu__item">@lang('sidebar.leaveSetting') </a>
                                </li>
                            @endif
                          @if (auth('admin')->user()->hasPermissionTo('view branches', 'admin'))('view leave_nationals')
                                <li class="slide">
                                    <a href="{{ url('leaveNationals') }}" class="side-menu__item">@lang('sidebar.leaveNationals') </a>
                                </li>
                            @endif --}}

                                @if (auth('admin')->user()->hasPermissionTo('view vehicle_settings', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('vehicle_settings.index') }}"
                                            class="side-menu__item">@lang('sidebar.vehicle_settings')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view cashPaymentSetting', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('cashPaymentSettings.list') }}"
                                            class="side-menu__item">@lang('sidebar.CashPaymentSetting')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view ethnic_backgrounds', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('ethnic_backgrounds.list') }}"
                                            class="side-menu__item">@lang('sidebar.EthnicBackground')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view bank_names', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('bank_names.list') }}"
                                            class="side-menu__item">@lang('sidebar.BankName')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view employee_status', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('employee_status.list') }}"
                                            class="side-menu__item">@lang('sidebar.EmployeeStatus')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view filed_of_study', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('filed_of_study.list') }}"
                                            class="side-menu__item">@lang('sidebar.FiledOfStudy')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view education_level', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('education_level.list') }}"
                                            class="side-menu__item">@lang('sidebar.EducationLevel')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view university', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('university.list') }}"
                                            class="side-menu__item">@lang('sidebar.University')
                                        </a>
                                    </li>
                                @endif

                                @if (auth('admin')->user()->hasPermissionTo('view marital_statuses', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('marital_statuses.list') }}"
                                            class="side-menu__item">@lang('sidebar.MaritalStatuses')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view military_service_statuses', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('military_service_statuses.list') }}"
                                            class="side-menu__item">@lang('sidebar.MilitaryServiceStatuses')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view payment_types', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('payment_types.list') }}"
                                            class="side-menu__item">@lang('sidebar.PaymentTypes')
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->user()->hasPermissionTo('view payment_frequencies', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('payment_frequencies.list') }}"
                                            class="side-menu__item">@lang('sidebar.PaymentFrequencies')
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </li>
                    @endif
                    @if (auth('admin')->user()->hasPermissionTo('view company_profile_setting', 'admin'))
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <span class=" side-menu__icon">
                                    <i class="bx bx-purchase-tag-alt"></i> </span>
                                <span class="side-menu__label">@lang('sidebar.company_setting') </span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0)">@lang('sidebar.company_profile_setting') </a>
                                </li>
                                @if (auth('admin')->user()->hasPermissionTo('view business_activity', 'admin'))
                                    <li class="slide">
                                        <a href="{{ route('business_activity.list') }}"
                                            class="side-menu__item">@lang('sidebar.businessActivity')
                                        </a>
                                    </li>
                                @endif

                                <li class="slide">
                                    <a href="{{ route('company_profile_setting.list') }}"
                                        class="side-menu__item">@lang('sidebar.company_profile_setting')
                                    </a>
                                </li>

                                <li class="slide">
                                    <a href="{{ route('contact_information_setting.list') }}"
                                        class="side-menu__item">@lang('sidebar.contact_information_setting')
                                    </a>
                                </li>


                                <li class="slide">
                                    <a href="{{ route('social_media_information_setting.list') }}"
                                        class="side-menu__item">@lang('sidebar.social_media_information_setting')
                                    </a>
                                </li>


                            </ul>
                        </li>
                    @endif

                @endif
                <!-- website -->
                <li class="slide__category"><span class="category-name">@lang('sidebar.website') </span>
                </li>
                @if (auth('admin')->user()->hasPermissionTo('view logos', 'admin'))
                    <li class="slide">
                        <a href="{{ route('logos.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.Logo')</span>
                        </a>
                    </li>
                @endif

                @if (auth('admin')->user()->hasPermissionTo('view sliders', 'admin'))
                    <li class="slide">
                        <a href="{{ route('sliders.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.Slider')</span>
                        </a>
                    </li>
                @endif

                {{--                @if (auth('admin')->user()->hasPermissionTo('view rates', 'admin')) --}}
                {{--                    <li class="slide"> --}}
                {{--                        <a href="{{ route('rates.list') }}" class="side-menu__item"> --}}
                {{--                            <span class=" side-menu__icon"> --}}
                {{--                                <i class='bx bx-desktop'></i> --}}
                {{--                            </span> --}}
                {{--                            <span class="side-menu__label">@lang('sidebar.Rates')</span> --}}
                {{--                        </a> --}}
                {{--                    </li> --}}
                {{--                @endif --}}

                @if (auth('admin')->user()->hasPermissionTo('view terms', 'admin'))
                    <li class="slide">
                        <a href="{{ route('terms.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.Terms')</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view payment_policies', 'admin'))
                    <li class="slide">
                        <a href="{{ route('payment_policies.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.payment_policies')</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view payment_reservation_policies', 'admin'))
                    <li class="slide">
                        <a href="{{ route('payment_reservation_policy.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.payment_reservation_policies')</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view privacies', 'admin'))
                    <li class="slide">
                        <a href="{{ route('privacies.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.Privacy')</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view returns', 'admin'))
                    <li class="slide">
                        <a href="{{ route('returns.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.Return')</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view faqs', 'admin'))
                    <li class="slide">
                        <a href="{{ route('faqs.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.FAQ')</span>
                        </a>
                    </li>
                @endif
                @if (auth('admin')->user()->hasPermissionTo('view cancellation_reasons', 'admin'))
                    <li class="slide">
                        <a href="{{ route('cancel_reasons.list') }}" class="side-menu__item">
                            <span class=" side-menu__icon">
                                <i class='bx bx-desktop'></i>
                            </span>
                            <span class="side-menu__label">@lang('sidebar.cancellation_reasons')</span>
                        </a>
                    </li>
                @endif


            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg></div>
        </nav>
        <!-- End::nav -->
    </div>
    <!-- End::main-sidebar -->

</aside>

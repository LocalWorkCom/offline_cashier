@extends('layouts.master')

@section('styles')
    <!-- DATA-TABLES CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <!-- Bootstrap CSS -->
    <style>
        #orgchart {
            transform: scale(1);
            /* or 1.5 */
            transform-origin: 0 0;
            overflow: scroll;
        }
    </style>
@endsection

@section('content')
    <!-- PAGE HEADER -->
    <div class="d-sm-flex d-block align-items-center justify-content-between page-header-breadcrumb">
        <h4 class="fw-medium mb-0">@lang('employee.employees')</h4>
        <div class="ms-sm-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">@lang('sidebar.Main')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@lang('employee.employees')</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-4 -->
            <div class="row">

                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <!-- Search input -->

                                <!-- Filters Container -->
                                <div class="d-flex flex-wrap gap-3">
                                    <!-- Company Filter -->
                                    <div class="mb-3">
                                        <label for="CompanyFilter" class="form-label">Company</label>
                                        <select id="CompanyFilter" class="form-control" onchange="loadBranches()">
                                            <option value="">Select Company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}" selected>{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Branch Filter -->
                                    <div class="mb-3">
                                        <label for="branchFilter" class="form-label">Branch</label>
                                        <select id="branchFilter" onchange="loadDepartments()" class="form-control">
                                            <option value="">Select Branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Department Filter -->
                                    <div class="mb-3">
                                        <label for="departmentFilter" class="form-label">Department</label>
                                        <select id="departmentFilter" class="form-control" onchange="loadSubDepartments()">
                                            <option value="">Select Department</option>
                                            {{-- @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach --}}
                                        </select>
                                    </div>

                                    <!-- Sub-department Filter (Hidden by default) -->
                                    <div class="mb-3" id="subDepartmentContainer" style="display: none;">
                                        <label for="subDepartmentFilter" class="form-label">Sub-department</label>
                                        <select id="subDepartmentFilter" class="form-control"
                                            onchange="loadPositions('sub_department')">

                                            <!-- Sub-department options will be dynamically added here -->
                                        </select>
                                    </div>

                                    <!-- Position Filter (Hidden by default) -->
                                    <div class="mb-3" id="positionContainer" style="display: none;">
                                        <label for="positionFilter" class="form-label">Position</label>
                                        <select id="positionFilter" class="form-control">
                                            <!-- Position options will be dynamically added -->
                                        </select>
                                    </div>

                                    <!-- Status Filter (Hidden by default) -->
                                    <div class="mb-3" id="statusContainer" style="display: none;">
                                        <label for="statusFilter" class="form-label">Status</label>
                                        <select id="statusFilter" class="form-control">
                                            <option value="">Select Status</option>
                                            <option value="active">active</option>
                                            <option value="inactive">in_active</option>
                                            <!-- Status options will be dynamically added -->
                                        </select>
                                    </div>

                                    <!-- Start Date Filter -->
                                    <div class="mb-3">
                                        <label for="startDateFilter" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="startDateFilter" />
                                    </div>
                                </div>

                                <!-- Filter Button -->
                                <div class="mb-3">
                                    <button class="btn btn-primary" onclick="filterHierarchy()">Apply Filters</button>
                                </div>
                            </div>
                        </div>


                        <div class="card-body">

                            <div id="orgchart" class="mt-5"></div>
                            <div class="mb-3">
                                <button class="btn btn-success" id="saveImageBtn">Save as Image</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Save as Image Button -->
@endsection
@php

    if (!function_exists('renderSubDepartment')) {
        function renderSubDepartment($sub, $parentKey)
        {
            $output = '';

            // Subdepartment node
            $subKey = 'subdept_' . $sub->id;
            $output .= "[{ v: '$subKey', f: '" . addslashes($sub->name) . "' }, '$parentKey', 'Sub-Department'],\n";

            // Positions
            foreach ($sub->positions as $position) {
                $posKey = 'position_' . $position->id;
                $output .= "[{ v: '$posKey', f: '" . addslashes($position->name) . "' }, '$subKey', 'Position'],\n";

                foreach ($position->employees as $emp) {
                    $empKey = $emp->id;
                    $output .=
                        "[{ v: '$empKey', f: '" .
                        addslashes($emp->first_name) .
                        "<div style=\"color:blue;\">" .
                        addslashes($position->name) .
                        "</div>' }, '$posKey', 'Employee'],\n";
                }

                foreach ($position->subPositions as $subpos) {
                    $subposKey = 'subpos_' . $subpos->id;
                    $output .=
                        "[{ v: '$subposKey', f: '" . addslashes($subpos->name) . "' }, '$posKey', 'Sub-Position'],\n";

                    foreach ($subpos->employees as $emp) {
                        $empKey = $emp->id;
                        $output .=
                            "[{ v: '$empKey', f: '" .
                            addslashes($emp->first_name) .
                            "<div style=\"color:blue;\">" .
                            addslashes($subpos->name) .
                            "</div>' }, '$subposKey', 'Employee'],\n";
                    }
                }
            }
            foreach ($sub->children as $childSub) {
                $output .= renderSubDepartment($childSub, $subKey);
            }

            return $output;
        }
    }
@endphp
@section('scripts')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script type="text/javascript">
        window.allDepartments = @json($departments);

        google.charts.load('current', {
            packages: ["orgchart"]
        });
        google.charts.setOnLoadCallback(drawChart);
        var data; // 🔥 Declare global variable
        var chart; // 🔥 Declare global chart variable
        // Load sub-departments when a department is selected
        // Load sub-departments when a department is selected
        function loadDepartments() {
            var branchId = document.getElementById('branchFilter').value;
            var departmentSelect = document.getElementById('departmentFilter');
            var positionSelect = document.getElementById('positionFilter');
            var subDepartmentSelect = document.getElementById('subDepartmentFilter');

            if (!branchId) return;

            departmentSelect.innerHTML = '<option value="">Loading...</option>';
            positionSelect.innerHTML = '<option value="">Select Position</option>';
            subDepartmentSelect.innerHTML = '<option value="">Select Sub-Department</option>';

            $.ajax({
                url: '/dashboard/department/branch/' + branchId,
                type: 'GET',
                success: function(data) {
                    console.log(branchId);

                    departmentSelect.innerHTML = '<option value="">Select Department</option>';
                    data.forEach(function(department) {
                        departmentSelect.innerHTML +=
                            `<option value="${department.id}">${department.name}</option>`;
                    });
                },
                error: function() {
                    departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
                }
            });
        }

        function loadSubDepartments() {
            var departmentId = document.getElementById('departmentFilter').value;

            if (departmentId) {
                // Show the sub-department filter dropdown
                document.getElementById('subDepartmentContainer').style.display = 'block';

                // Clear existing options
                var subDepartmentSelect = document.getElementById('subDepartmentFilter');
                subDepartmentSelect.innerHTML = '<option value="">Loading...</option>';

                // Make an AJAX request to fetch sub-departments based on department ID
                $.ajax({
                    url: '/dashboard/department/get-subdepartments/' +
                        departmentId, // Ensure this matches your Laravel route
                    type: 'GET',
                    success: function(data) {
                        console.log(data);

                        // Clear existing options and populate new ones
                        subDepartmentSelect.innerHTML = '<option value="">Select Sub-department</option>';

                        // Check if there are sub-departments and populate the dropdown
                        if (data.length > 0) {
                            data.forEach(function(subDepartment) {
                                subDepartmentSelect.innerHTML +=
                                    `<option value="${subDepartment.id}">${subDepartment.name}</option>`;
                            });
                        } else {
                            subDepartmentSelect.innerHTML +=
                                '<option value="">No sub-department available</option>';
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching sub-departments:', error);
                        subDepartmentSelect.innerHTML =
                            '<option value="">Error fetching sub-departments</option>';
                    }
                });
            } else {
                // Hide the sub-department filter if no department is selected
                document.getElementById('subDepartmentContainer').style.display = 'none';
            }
            loadPositions('department');
        }

        function loadPositions(type) {
            if (type == 'department') {

                var search_id = document.getElementById('departmentFilter').value;
            } else {

                var search_id = document.getElementById('subDepartmentFilter').value;
            }

            if (search_id) {
                document.getElementById('positionContainer').style.display = 'block';

                var positionSelect = document.getElementById('positionFilter');
                positionSelect.innerHTML = '<option value="">Loading...</option>';

                $.ajax({
                    url: '/dashboard/department/positions/' + search_id, // Adjust this route as needed
                    type: 'GET',
                    success: function(data) {
                        positionSelect.innerHTML = '<option value="">Select Position</option>';
                        if (data.length > 0) {
                            data.forEach(function(position) {
                                positionSelect.innerHTML +=
                                    `<option value="${position.id}">${position.name_ar}</option>`;
                            });
                        } else {
                            positionSelect.innerHTML += '<option value="">No positions found</option>';
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching positions:', error);
                        positionSelect.innerHTML = '<option value="">Error fetching positions</option>';
                    }
                });
            } else {
                document.getElementById('positionContainer').style.display = 'none';
            }
        }

        function loadBranches() {
            var companyId = document.getElementById('CompanyFilter').value;

            if (companyId) {
                // Clear existing branch options
                var branchSelect = document.getElementById('branchFilter');
                branchSelect.innerHTML = '<option value="">Loading...</option>';

                // Make an AJAX request to fetch branches based on the company
                $.ajax({
                    url: '/dashboard/branch/get-branches-by-company/' +
                        companyId, // Ensure this URL matches your Laravel route
                    type: 'GET',
                    success: function(data) {
                        // Clear existing options and populate new ones
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';

                        // Check if there are branches and populate the dropdown
                        if (data.length > 0) {
                            data.forEach(function(branch) {
                                branchSelect.innerHTML +=
                                    `<option value="${branch.id}">${branch.name}</option>`;
                            });
                        } else {
                            branchSelect.innerHTML += '<option value="">No branches available</option>';
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                        branchSelect.innerHTML = '<option value="">Error fetching branches</option>';
                    }
                });
            } else {
                // Clear the branch filter if no company is selected
                document.getElementById('branchFilter').innerHTML = '<option value="">Select Branch</option>';
            }
        }

        function renderSubDepartmentJS(sub, parentKey) {
            let rows = [];

            const subKey = `subdept_${sub.id}`;
            rows.push([{
                    v: subKey,
                    f: sub.name
                },
                parentKey,
                'Sub-Department'
            ]);

            if (Array.isArray(sub.positions) && sub.positions.length > 0) {
                sub.positions.forEach(position => {
                    const posKey = `position_${position.id}`;
                    
                    rows.push([{
                            v: posKey,
                            f: position.name_ar
                        },
                        subKey,
                        'Position'
                    ]);
                    

                    if (Array.isArray(position.employees) && position.employees.length > 0) {

                        position.employees.forEach(emp => {
                             const empKey = String(emp.id); // or `${emp.id}`
  
                             rows.push([{
                                    v: empKey,
                                    f: `${emp.first_name}<div style="color:blue;">${position.name_ar}</div>`
                                },
                                posKey,
                                'Employee'
                            ]);
                        });
                    }

                    if (Array.isArray(position.sub_positions) && position.sub_positions.length > 0) {
                        console.log("999999");

                        position.sub_positions.forEach(subpos => {
                            const subposKey = `subpos_${subpos.id}`;
                            rows.push([{
                                    v: subposKey,
                                    f: subpos.name_ar
                                },
                                posKey,
                                'Sub-Position'
                            ]);

                            if (Array.isArray(subpos.employees) && subpos.employees.length > 0) {
                                subpos.employees.forEach(emp => {
                                    const empKey = emp.id;
                                    rows.push([{
                                            v: empKey,
                                            f: `${emp.first_name}<div style="color:blue;">${subpos.name}</div>`
                                        },
                                        subposKey,
                                        'Employee'
                                    ]);
                                });
                            }
                        });
                    }
                });
            }

            if (Array.isArray(sub.children) && sub.children.length > 0) {
                console.log("45210");

                sub.children.forEach(childSub => {
                    rows = rows.concat(renderSubDepartmentJS(childSub, subKey));
                });
            }

            return rows;
        }


        function filterHierarchy() {

            const companyId = document.getElementById('CompanyFilter').value;
            const branchId = document.getElementById('branchFilter').value;
            const departmentId = document.getElementById('departmentFilter').value;
            const subDepartmentId = document.getElementById('subDepartmentFilter').value;
            const positionId = document.getElementById('positionFilter').value;

            let filteredData = [];

            window.allDepartments.forEach(dept => {
                if (
                    (!companyId || dept.branch.company.id == companyId) &&
                    (!branchId || dept.branch_id == branchId) &&
                    (!departmentId || dept.id == departmentId)
                ) {

                    const deptKey = `dept_${dept.id}`;
                    filteredData.push([{
                        v: deptKey,
                        f: dept.name
                    }, '', 'Department']);

                    dept.children.forEach(sub => {
                        if (!subDepartmentId || sub.id == subDepartmentId) {

                            filteredData = filteredData.concat(renderSubDepartmentJS(sub, deptKey));
                        }
                    });
                }
            });

            updateChart(filteredData);
        }

        function updateChart(filteredData) {
            data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            // Add filtered rows to the DataTable
            console.log(filteredData);
            console.log(data);
            data.addRows(filteredData);

            // Redraw the OrgChart
            chart.draw(data, {
                allowHtml: true
            });
            google.visualization.events.addListener(chart, 'select', function() {
                var selection = chart.getSelection();

                if (selection.length > 0) {
                    var selectedId = data.getValue(selection[0].row, 0);
                    window.location.href = '/dashboard/employee/show/' + selectedId; // redirect to employee profile
                }
            });
        }




        function drawChart() {
            data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            data.addRows([
                @foreach ($departments as $dept)
                    // Department node
                    [{
                        v: 'dept_{{ $dept->id }}',
                        f: 'Department: {{ addslashes($dept->name) }}'
                    }, '', 'Department'],

                    // Process its subdepartments recursively
                    @foreach ($dept->children as $sub)
                        {!! renderSubDepartment($sub, 'dept_' . $dept->id) !!}
                    @endforeach
                @endforeach
            ]);


            chart = new google.visualization.OrgChart(document.getElementById('orgchart'));
            chart.draw(data, {
                allowHtml: true
            });

            // Add "Save as Image" functionality using html2canvas
            document.getElementById('saveImageBtn').addEventListener('click', function() {
                const chartContainer = document.getElementById('orgchart');

                // Backup original styles
                const originalStyle = {
                    width: chartContainer.style.width,
                    height: chartContainer.style.height,
                    overflow: chartContainer.style.overflow,
                    transform: chartContainer.style.transform,
                    transformOrigin: chartContainer.style.transformOrigin,
                };

                // Temporarily scale down and show full content
                chartContainer.style.overflow = 'visible';
                chartContainer.style.transform = 'scale(0.5)';
                chartContainer.style.transformOrigin = 'top left';

                // Make container big enough to avoid scroll
                chartContainer.style.width = chartContainer.scrollWidth * 2 + 'px';
                chartContainer.style.height = chartContainer.scrollHeight * 2 + 'px';

                setTimeout(() => {
                    html2canvas(chartContainer, {
                        useCORS: true,
                        scale: 2, // increase quality
                    }).then(function(canvas) {
                        // Restore original styles
                        chartContainer.style.width = originalStyle.width;
                        chartContainer.style.height = originalStyle.height;
                        chartContainer.style.overflow = originalStyle.overflow;
                        chartContainer.style.transform = originalStyle.transform;
                        chartContainer.style.transformOrigin = originalStyle.transformOrigin;

                        // Export image
                        const link = document.createElement('a');
                        link.download = 'org_chart_full.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    });
                }, 500);
            });


            google.visualization.events.addListener(chart, 'select', function() {
                var selection = chart.getSelection();

                if (selection.length > 0) {
                    var selectedId = data.getValue(selection[0].row, 0);
                    window.location.href = '/dashboard/employee/show/' + selectedId;
                }
            });
        }
    </script>
@endsection

{{-- <section class="location-pop-up">
    <div id="modal_access" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h2>@lang('header.locationaccess')</h2>
                </div>
                <div class="modal-footer d-flex flex-column border-0">
                    <button type="button" class="btn w-100" id="accessLocationBtn">@lang('header.access')</button>
                    <button type="button" class="btn w-100 reversed main-color" data-bs-toggle="modal"
                        data-bs-target="#deliveryModal">@lang('header.onmap')</button>
                </div>
            </div>
        </div>
    </div>
</section> --}}
{{--
@push('scripts')
    <script>

        var routeMenuUrl = "{{ route('menu', ['branch_id' => '__BRANCH_ID__']) }}"; // Temporary URL
    </script>
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



            document.querySelectorAll('.branch-link').forEach(function(branchLink) {
                branchLink.addEventListener('click', function(event) {
                    const id = branchLink.getAttribute('data-id');
                    console.log(id);

                    setCookie('branch_id', id, 7);
                });
            });

            // Handle "Use My Location" button click
            console.log(0);

            // Function to get the nearest branch based on latitude and longitude
            function getNearestBranchHTML(userLat, userLon) {
                $.ajax({
                    url: '/get-nearest-branch',
                    method: 'GET',
                    data: {
                        latitude: userLat,
                        longitude: userLon
                    },
                    success: function(response) {
                        const nearestBranch = response.branch;
                        displayNearestBranch(nearestBranch);
                    },
                    error: function() {
                        // Handle error if necessary
                    }
                });
            }

            // Display the nearest branch in the modal
            function displayNearestBranch(branch) {
                const branchList = document.getElementById('branchList');
                const routeUrl = routeMenuUrl.replace('__BRANCH_ID__', branch.id);

                branchList.innerHTML = `
            <a href="${routeUrl}">
                <div class="location border-bottom mb-1 branch-item">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mt-2 branch-name">
                            <i class="fas fa-map-marker-alt main-color mx-2"></i>${branch.name}
                        </h6>
                        <span class="badge ${branch.isOpen ? 'text-success' : 'text-muted'} mt-2">
                            ${branch.isOpen ? 'مفتوح' : 'مغلق'}
                        </span>
                    </div>
                    <p class="text-muted mx-2 branch-address">${branch.address}</p>
                    <p class="main-color fw-bold">
                        <i class="fas fa-phone mx-2"></i>${branch.phone}
                    </p>
                </div>
            </a>
        `;

                document.getElementById('useMyLocationBtn').classList.add('d-none');
                $('#branchesModal').modal('show'); // Show the modal with the branch details
            }



            // Check if latitude and longitude cookies exist
            const hasLatitudeCookie = document.cookie.split('; ').some(row => row.startsWith('latitude='));
            const hasLongitudeCookie = document.cookie.split('; ').some(row => row.startsWith('longitude='));

            // Handle "Allow" button click
            document.getElementById('accessLocationBtn').addEventListener('click', function() {

                $('#modal_access').modal('show'); // Close the modal
                console.log(navigator.geolocation);

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Set cookies with latitude and longitude
                        setCookie('latitude', latitude, 7);
                        setCookie('longitude', longitude, 7);

                        setBranchCookie(latitude, longitude, function() {
                            // Reload the page after setting location cookies and branch_id
                            window.location.reload();
                        });
                    }, function(error) {
                        console.error('Geolocation error:', error);
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
            } --}}

            {{-- // Function to set cookies
            // function setCookie(name, value, days) {
            //     document.cookie = name + "=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
            //     var expires = "";
            //     if (days) {
            //         var date = new Date();
            //         date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            //         expires = "; expires=" + date.toUTCString();
            //     }
            //     document.cookie = name + "=" + (value || "") + expires + "; path=/";
            // }

            // // Function to get cookies
            // function getCookie(name) {
            //     var nameEQ = name + "=";
            //     var ca = document.cookie.split(';');
            //     for (var i = 0; i < ca.length; i++) {
            //         var c = ca[i];
            //         while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            //         if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            //     }
            //     return null;
            // }
        });
    </script>
@endpush --}}

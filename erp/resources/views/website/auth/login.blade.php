<div class="modal-body login  px-4" id="loginBody">
    <form method="POST" id="login-Form" action="{{ route('website.login') }}">
        @csrf
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}" alt="Logo"
                    height="110">
            </div>

            <div class="col-md-7 right-side p-3">
                <h2 class="main-color fw-bold">@lang('auth.welcome')</h2>
                <h5>@lang('auth.getlogin')</h5>
                <meta name="csrf-token" content="{{ csrf_token() }}">

                <!-- Phone Input -->

                <div class="input-group mb-3">
                    <input type="text" class="form-control  @error('email_or_phone') is-invalid @enderror "
                        name="email_or_phone" id="phoneInput" placeholder="@lang('auth.phoneplace')"
                        value="{{ old('email_or_phone') }}" required>
                    <select id="country" name="country_code_login" class="selectpicker me-2" data-live-search="true"
                        required>
                  
                        @foreach (GetCountries() as $country)
                            <option
                                data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                value="{{ $country->phone_code }}">{{ $country->phone_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="email_or_phoneError" class="error-message mb-1 text-danger"></div>
                <div id="country_code_loginError" class="error-message mb-1 text-danger"></div>

                <!-- Password Input -->
                <div class="input-group position-relative mb-3">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                        id="passwordInput" placeholder="@lang('auth.passplace')" required>
                    <button class="input-group-eye position-absolute" type="button" id="togglePassword">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <div id="passwordError" class="error-message mb-1 text-danger"></div>

                <a id="showforgetLink">
                    <small>@lang('auth.forgetpass')</small>
                </a>

                <button type="submit" class="btn py-3 my-2 w-100">@lang('auth.login')</button>

                <p class="text-center">
                    <small>@lang('auth.loginsocial')</small>
                </p>

                <!-- <div class="d-flex justify-content-center gap-lg-3">
                    <a class="social-media-btn d-flex align-items-center" href="#">
                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/facebook-icon.png') }}"
                            alt="Facebook" class="ms-2" height="20">
                        <span>@lang('auth.facebook')</span>
                    </a>

                    <a class="social-media-btn d-flex align-items-center" href="#">
                        <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/google-icon.png') }}"
                            alt="Google" class="ms-2" height="20">
                        <span>@lang('auth.google')</span>
                    </a>
                </div> -->

                <p class="text-center">
                    <small>
                        <span class="text-dark">@lang('auth.newuser')</span>
                        <a href="#" class="main-color text-decoration-underline" id="showRegisterLink"
                            aria-label="@lang('auth.signup')">@lang('auth.signup')</a>
                    </small>
                </p>
            </div>

            <div class="col-md-5 login-background">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/login-chef-backgound.png') }}"
                    alt="">
            </div>
        </div>
    </form>
</div>
@push('scripts')
    <script>
        document.getElementById('login-Form').addEventListener('submit', function(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            // Retrieve and parse address data from local storage
            const addressData = localStorage.getItem('addressDetails');
             const mapDetail = localStorage.getItem('mapDetail');
            let latData = getCookie('latitude');
            let longData = getCookie('longitude');

            if (addressData) {
                formData.append('address', JSON.stringify(addressData));
                formData.append('location', JSON.stringify(mapDetail));
            }
            if (longData && latData) {
                formData.append('latData', latData);
                formData.append('longData', longData);
            }
            // formData.append('notauthaddress', JSON.stringify(notauthaddressData));

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => Promise.reject(data));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = "{{ route('home') }}";
                    }
                })
                .catch(error => {
                    if (error.errors) {
                        for (const [field, messages] of Object.entries(error.errors)) {
                            const errorElement = document.getElementById(`${field}Error`);
                            const inputElement = document.querySelector(`[name="${field}"]`);

                            if (errorElement) {
                                errorElement.textContent = messages.join(', ');
                            }

                            if (inputElement) {
                                inputElement.classList.add('is-invalid');
                            }
                        }
                    } else {
                        console.error('An unexpected error occurred:', error);
                    }
                });
        });

        // $(document).ready(function() {
        //     $('#login-Form').on('submit', function(event) {
        //         event.preventDefault();
        //         var formData = $(this).serialize();
        //         $.ajax({
        //             url: '{{ route('website.login') }}',
        //             method: 'POST',
        //             data: formData,
        //             success: function(response) {
        //                 console.log(response);
        //                 if (response.status === 'success') {
        //                     $('#loginBody').html(`
    //                         <div class="text-center">
    //                             <h3>Login successful!</h3>
    //                             <p>Welcome, ${response.data.user.name}!</p>
    //                             <button class="btn btn-primary" onclick="window.location.href='/home'">Go to Dashboard</button>
    //                         </div>
    //                     `);
        //                     setTimeout(function() {
        //                         window.location.href =
        //                         '/';
        //                     }, 300);
        //                 }
        //             },
        //             error: function(response) {
        //                 if (response.status === 422 || response.status === 403) {
        //                     for (const [field, messages] of Object.entries(error.errors)) {
        //                     const errorElement = document.getElementById(`${field}Error`);
        //                     const inputElement = document.querySelector(`[name="${field}"]`);

        //                     if (errorElement) {
        //                         errorElement.textContent = messages.join(', ');
        //                     }

        //                     if (inputElement) {
        //                         inputElement.classList.add('is-invalid');
        //                     }
        //                 }
        //                 } else {
        //                 }
        //             }
        //         });
        //     });
        // });
    </script>
    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });
    </script>
@endpush

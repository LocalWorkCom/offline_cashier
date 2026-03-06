<div class="modal-body register d-none px-4" id="registerBody">
    <form method="POST" action="{{ route('website.register') }}" id="Register">
        @csrf
        <div class="row">
            {{-- logo --}}
            <div class="col-12 d-flex justify-content-center">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}"
                     alt="Logo" height="110">
            </div>

            {{-- form side --}}
            <div class="col-md-7 right-side p-3">
                <h2 class="main-color fw-bold">@lang('auth.welcome')</h2>
                <h5>@lang('auth.canregister')</h5>

                {{-- flash error (server‑side redirects) --}}
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- name --}}
                <div class="input-group mb-3">
                    <input type="text" name="name" class="form-control" id="nameInput"
                        placeholder="@lang('auth.nameweb')" required>
                    <div id="nameError" class="invalid-feedback"></div>
                </div>

                <meta name="csrf-token" content="{{ csrf_token() }}">

                {{-- phone + country code --}}
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="phone" id="phoneInput"
                        placeholder="@lang('auth.phoneplace')" value="{{ old('phone') }}" required>

                    <select id="country" name="country_code" class="selectpicker me-2" data-live-search="true">
                        @foreach (GetCountries() as $country)
                            <option
                                data-content='<img src="{{ $country->flag }}" class="flag-icon"> {{ $country->phone_code }}'
                                value="{{ $country->phone_code }}">
                                {{ $country->phone_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="phoneError" class="invalid-feedback"></div>
                <div id="country_codeError" class="invalid-feedback"></div>

                {{-- email (optional) --}}
                {{-- <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control"
                           id="emailInput" placeholder="@lang('auth.emailweb')">
                </div> --}}
                <div id="emailError" class="invalid-feedback"></div>

                {{-- password --}}
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" id="passwordInput"
                        placeholder="@lang('auth.passweb')" required>
                    <button class="input-group-eye" type="button" id="togglePassword">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <div id="password2Error" class="invalid-feedback"></div>

                <button type="submit" class="btn py-3 mb-2 w-100">
                    @lang('auth.signup')
                </button>

                <p class="text-center">
                    <small>
                        @lang('auth.isexist')
                        <a href="#" class="main-color text-decoration-underline"
                            id="showLoginLink">@lang('auth.login')</a>
                    </small>
                </p>

                <p class="text-center">
                    <small>
                        <span class="text-muted">@lang('auth.police')</span>
                        <a href="{{ route('privacy') }}" class="main-color text-decoration-underline">
                            @lang('auth.privacyweb')
                        </a>
                        <a href="{{ route('terms') }}" class="main-color text-decoration-underline">
                            @lang('auth.policeweb')
                        </a>
                    </small>
                </p>
            </div>

            {{-- right side image --}}
            <div class="col-md-5 login-background">
                <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/login-chef-backgound.png') }}"
                    alt="">
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.getElementById('Register').addEventListener('submit', function(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            /* reset previous errors */
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            /* append map / address if present */
            const addressData = localStorage.getItem('addressDetails');
            const mapDetail = localStorage.getItem('mapDetail');
            if (addressData) {
                formData.append('address', JSON.stringify(addressData));
                formData.append('location', JSON.stringify(mapDetail));
            }

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // ✅ success
                    if (data.status === 'success' || data.status === true || data.status === 200) {
                        window.location.href = "{{ route('home') }}";
                        return;
                    }

                    // ❌ validation error
                    if (data.code === 400 && data.errorData) {
                        handleErrors(data.errorData);
                    }
                })
                .catch(error => {
                    console.error('Unexpected error', error);
                });

            function handleErrors(bag) {
                Object.entries(bag).forEach(([field, messages]) => {
                    const input = form.querySelector(`[name="${field}"]`);
                    const holder = document.getElementById(`${field}Error`);
                    const msgText = Array.isArray(messages) ? messages.join(', ') : messages;

                    if (input) {
                        input.classList.add('is-invalid');
                    }

                    if (holder) {
                        holder.textContent = msgText;
                        holder.classList.add('d-block'); // make sure visible
                    }

                    // password confirmation case
                    if (field === 'password') {
                        const pass2Holder = document.getElementById('password2Error');
                        if (pass2Holder) {
                            pass2Holder.textContent = msgText;
                            pass2Holder.classList.add('d-block');
                        }
                    }
                });
            }

        });

        /* eye toggle */
        document.getElementById('togglePassword')?.addEventListener('click', () => {
            const pwd = document.getElementById('passwordInput');
            const eye = document.getElementById('eyeIcon');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
            eye.classList.toggle('fa-eye');
            eye.classList.toggle('fa-eye-slash');
        });
    </script>
@endpush
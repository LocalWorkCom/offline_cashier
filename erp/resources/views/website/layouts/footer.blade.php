<footer>
    <div class="footer-curve"></div>
    <div class="container ">
        <figure class="footer-logo">
            <img src="{{ asset('front/AlKout-Resturant/SiteAssets/images/logo-with-white-bg.png') }}" />
        </figure>
    </div>
    <div class="footer">
        <div class="container py-sm-5 p-4 desktop-footer">
            <div class=" row m-0 ">
                <div class="col-md-3" data-aos="zoom-in">
                    <h4 class="footer-title"> @lang('header.contactUs')</h4>
                    <ul class="list-unstyled px-0 pt-4">
                        <li>
                            <ul class="list-unstyled social-media p-0">
                                <li>
                                    <a href="#" class="ms-3">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="ms-3">
                                        <i class="fab fa-snapchat"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="ms-3">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <i class="fas fa-phone ms-1"></i>
                            <a href="#" class="clickable-phone" data-phone="{{ $branchPhone }}"
                                title="@lang('header.copyNumber')">
                                {{ $branchPhone ? $branchPhone : __('header.noPhone') }}
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-building ms-1"></i>
                            {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt ms-1"></i>
                            {{ app()->getLocale() === 'ar' ? $branch->address_ar : $branch->address_en }}
                        </li>
                    </ul>
                </div>
                <div class="col-md-3" data-aos="zoom-in">
                    <h4 class="footer-title"> @lang('header.quicklinks')</h4>
                    <ul class="list-unstyled px-0 pt-4">
                        <li><a href="{{ route('menu') }}">@lang('header.main_menu')</a></li>
                        <li><a href="{{ route('menu', ['category_id' => 'offers']) }}"> @lang('header.offers')</a></li>
                        <li><a href="{{ route('terms') }}">@lang('header.terms')</a></li>
                    </ul>
                </div>
                <div class="col-md-3" data-aos="zoom-in">
                    <h4 class="footer-title"> @lang('header.help')</h4>
                    <ul class="list-unstyled px-0 pt-4">
                        <li><a href="{{ route('contactUs') }}"> @lang('header.callcenter')</a></li>
                        <li><a href="{{ route('return') }}">@lang('header.returns')</a></li>
                        <li><a href="{{ route('privacy') }}">@lang('header.privacypolicy')</a></li>

                    </ul>
                </div>
                <div class="col-md-3" data-aos="zoom-in">
                    <h4 class="footer-title mb-4">@lang('header.resturanttimes')</h4>
                    @forelse ($branchWorkingHours as $hours)
                        <p>
                            {{ $hours['days'] }}:
                            {{ $hours['opening_hour'] }} - {{ $hours['closing_hour'] }}
                        </p>
                    @empty
                        <p>@lang('header.no_hours_available')</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="container py-sm-5 p-4 mobile-footer d-none">
            <div class="row m-0">
                <div class="col-12 col-sm-6 mb-4 mb-sm-0">
                    <div class="row">
                        <div class="col-6" data-aos="zoom-in">
                            <h4 class="footer-title">@lang('header.contactUs')</h4>
                            <ul class="list-unstyled px-0 pt-4">
                                <li>
                                    <ul class="list-unstyled social-media p-0">
                                        <li><a href="#" class="ms-3"><i class="fab fa-facebook"></i></a></li>
                                        <li><a href="#" class="ms-3"><i class="fab fa-snapchat"></i></a></li>
                                        <li><a href="#" class="ms-3"><i class="fab fa-instagram"></i></a></li>
                                    </ul>
                                </li>
                                <li>
                                    <i class="fas fa-phone ms-1"></i>
                                    <a href="#" class="clickable-phone" data-phone="{{ $branchPhone }}"
                                        title="@lang('header.copyNumber')">
                                        {{ $branchPhone ? $branchPhone : __('header.noPhone') }}
                                    </a>
                                </li>
                                <li><i class="fas fa-building ms-1"></i>
                                    {{ app()->getLocale() === 'ar' ? $branch->name_ar : $branch->name_en }}</li>
                                <li><i class="fas fa-map-marker-alt ms-1"></i>
                                    {{ app()->getLocale() === 'ar' ? $branch->address_ar : $branch->address_en }}</li>
                            </ul>
                        </div>

                        <div class="col-6" data-aos="zoom-in">
                            <h4 class="footer-title">@lang('header.quicklinks')</h4>
                            <ul class="list-unstyled px-0 pt-4">
                                <li><a href="{{ route('menu') }}">@lang('header.main_menu')</a></li>
                                <li><a href="{{ route('menu', ['category_id' => 'offers']) }}">@lang('header.offers')</a>
                                </li>
                                <li><a href="{{ route('terms') }}">@lang('header.terms')</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6">
                    <div class="row">
                        <div class="col-6" data-aos="zoom-in">
                            <h4 class="footer-title">@lang('header.help')</h4>
                            <ul class="list-unstyled px-0 pt-4">
                                <li><a href="{{ route('contactUs') }}">@lang('header.callcenter')</a></li>
                                <li><a href="{{ route('return') }}">@lang('header.returns')</a></li>
                                <li><a href="{{ route('privacy') }}">@lang('header.privacypolicy')</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copywrite">

            <p class="m-0">@lang('header.allterms')<a href="#"> @lang('header.elkowt')</a> 2025</p>
        </div>
    </div>
</footer>

{{-- @push('scripts') --}}
<script>
    $('#minimize').click(function() {
        $('.default-icon').addClass('d-none');
        $('.default-text').addClass('d-none');
        $('.chat-close-btn').removeClass('d-none');
        $('.user-icon').removeClass('d-none');
        $('#chatBox').removeClass('show');
        $('.chat-button').removeClass('bg-success');
        $('.chat-button').addClass('bg-white');

    });

    function toggleChat() {
        var chatBox = document.getElementById("chatBox");

        if (chatBox.classList.contains("show")) {
            minimize(); // If chat is already open, minimize it
        } else {
            chatBox.classList.add("show"); // Show chat box
            console.log(chatBox, 'test');

            // **Hide user icon and close button**
            document.querySelector('.user-icon').classList.add("d-none");
            document.querySelector('.chat-close-btn').classList.add("d-none");

            // **Show default icon and text**
            document.querySelector('.default-icon').classList.remove("d-none");
            document.querySelector('.default-text').classList.remove("d-none");
        }
    }


    function closeChat(event) {
        event.stopPropagation();
        var chatBox = document.getElementById("chatBox");
        var chatButton = document.getElementById("chatButton");
        var closeBtn = document.querySelector(".chat-close-btn");
        var defaultIcon = chatButton.querySelector('.default-icon');
        var defaultText = chatButton.querySelector('.default-text');
        var userIcon = chatButton.querySelector('.user-icon');
        chatBox.classList.remove("show");

    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneElements = document.querySelectorAll('.clickable-phone');

        phoneElements.forEach(element => {
            element.addEventListener('click', function() {
                const phone = element.getAttribute('data-phone');
                if (phone) {
                    navigator.clipboard.writeText(phone)
                        .then(() => {
                            alert('Phone number copied to clipboard!');
                        })
                        .catch(err => {
                            alert('Failed to copy phone number: ' + err);
                        });
                }
            });
        });
    });
</script>
{{-- @endpush --}}

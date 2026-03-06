@extends('website.layouts.master')

@section('content')
    <section class="inner-header pt-5 mt-5">
        <div class="container pt-sm-5 pt-4">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"> @lang('auth.home')</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> @lang('header.myaddress')</li>
                </ol>
            </nav>
        </div>

    </section>
    <section class="addresses">
        <div class="container pb-sm-5 pb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="my-4 fw-bold">@lang('header.myaddress')</h4>
                <button class="btn fw-bold fs-5" type="button"
                    onclick="window.location.href='{{ route('create.Address') }}'">
                    <span>+</span> @lang('auth.addaddress')
                </button>

            </div>

            @if ($address->isNotEmpty())
                <div class="card p-5 w-75 mx-auto mt-5">
                    <ul class="list-unstyled px-0">
                        @foreach ($address as $item)
                            <li class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <h5> <i class="fas fa-city text-muted fa-xs ms-2"></i>
                                        @if ($item->address_type === 'hotel')
                                            <small class="text-muted">

                                                {{ $item->hotel?->name }}
                                            </small>
                                        @else
                                            {{ $item->building }}
                                        @endif
                                    </h5>
                                    <small class="text-muted">
                                        @if ($item->building === 'apartment')
                                            {{ $item->address_type . ' , ' . $item->apartment_number . ' , ' . $item->floor_number . ' , ' . $item->floor_number . ' , ' . $item->address . ' , ' . $item->area?->name . ' , ' . $item->city?->name . ' , ' . $item->country?->name }}
                                        @elseif ($item->address_type === 'hotel')
                                            {{ $item->address_type . ' , ' . $item->apartment_number . ' , ' . $item->hotel?->address . ' , ' . $item->area?->name . ' , ' . $item->city?->name . ' , ' . $item->country?->name }}
                                            @else
                                            {{ $item->apartment_number . ' , ' . $item->area?->name . ' , ' .  $item->city?->name  . ' , ' . $item->country?->name }}
                                        @endif
                                    </small>

                                </div>
                                <div class="dropdown">
                                    <a id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @if ($item->is_default === 0)
                                            <li>
                                                <button class="dropdown-item w-100" data-bs-toggle="modal"
                                                    data-bs-target="#defaultddressModal" data-id="{{ $item->id }}">
                                                    @lang('header.makedefault')
                                                </button>
                                            </li>
                                        @endif
                                        @if ($item->has_inprogress_or_pending_orders == 0)
                                            <li>
                                                <button class="dropdown-item w-100"
                                                    onclick="window.location.href='{{ route('edit.Address', ['id' => $item->id]) }}'">
                                                    @lang('auth.edit')
                                                </button>
                                            </li>
                                            @if ($address->count() > 1 && $item->is_default === 0)
                                                <li>
                                                    <button class="dropdown-item w-100" data-bs-toggle="modal"
                                                        data-bs-target="#deleteaddressModal" data-id="{{ $item->id }}">
                                                        @lang('auth.delete')
                                                    </button>
                                                </li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="card p-5 w-50 text-center mx-auto mt-5">
                    <img class="noAddress-img"
                        src="{{ asset('front/AlKout-Resturant/SiteAssets/images/mdi_file-location.png') }}"
                        alt="" />
                    <h4 class="my-4 fw-bold">@lang('auth.noaddress')</h4>
                </div>
            @endif
        </div>
    </section>
    <div class="logout-modal modal fade" tabindex="-1" id="addressnotcart">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h4 class="mt-4"> @lang('auth.addressnotcart')</h4>
                </div>
                <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                    <button type="button" class="btn main-color w-25 mx-2" id="btn-switch-branch"> {{-- YES --}}
                        @lang('auth.ok')
                    </button>

                    <button type="button" class="btn reversed main-color w-25 mx-2" data-bs-dismiss="modal">
                        {{-- NO --}}
                        @lang('auth.no')
                    </button>

                </div>
            </div>
        </div>
    </div>
    <div class="logout-modal modal fade" tabindex="-1" id="deleteaddressModal">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="deleteaddressForm">
                    @csrf
                    <div class="modal-header border-0">
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
    <div class="logout-modal modal fade" tabindex="-1" id="defaultddressModal">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="defaultddressForm">
                    @csrf
                    <div class="modal-header border-0">
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-sign-out-alt main-color fs-1"></i>
                        <h4 class="mt-4"> @lang('auth.defaultddress')</h4>
                    </div>
                    <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                        <button type="submit" class="btn w-25 mx-2"> @lang('header.confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
{{-- show the modal only when the flash key exists --}}
@if (session('show_address_no'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // 1. get the real DOM element
                const modalEl = document.getElementById('addressnotcart');
                if (!modalEl) return; // safety guard

                // 2. create Bootstrap Modal instance
                const confirmOrderModal = new bootstrap.Modal(modalEl);

                // 3. open it
                confirmOrderModal.show();

                // 4. auto‑hide after 30 s
                setTimeout(() => confirmOrderModal.hide(), 30_000);
            });
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('[data-bs-target="#deleteaddressModal"]');
            const deleteForm = document.getElementById('deleteaddressForm');

            deleteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const addressId = button.getAttribute('data-id');
                    const deleteUrl = `{{ route('address.delete', ['id' => ':id']) }}`.replace(
                        ':id', addressId);
                    deleteForm.setAttribute('action', deleteUrl);
                });
            });
        });
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('[data-bs-target="#defaultddressModal"]');
            const deleteForm = document.getElementById('defaultddressForm');

            deleteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const addressId = button.getAttribute('data-id');
                    const deleteUrl = `{{ route('default.Address', ['id' => ':id']) }}`.replace(
                        ':id', addressId);
                    deleteForm.setAttribute('action', deleteUrl);
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const alertDivs = document.querySelectorAll('.auto-hide');
                alertDivs.forEach(div => {
                    div.classList.add('d-none');
                });
            }, 200);
        });
    </script>
@endpush

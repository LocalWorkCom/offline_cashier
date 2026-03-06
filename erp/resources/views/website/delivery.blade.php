@push('style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
@endpush

<!-- delivery details modal -->
<div class="delivery-modal modal fade" tabindex="-1" id="deliveryModal" aria-labelledby="deliveryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content" id="delivary">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"
                    onclick="showFirstPhase()"></button>
            </div>
            <div class="modal-body">
                <h3 class="text-center fw-bold mb-4">@lang('header.startorder')</h3>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-delivery" role="tabpanel"
                        aria-labelledby="pills-delivery-tab">

                        @if (Auth::guard('client')->user() && Auth::guard('client')->user()->flag == 'client')
                            @include('website.clientdeliverycase')
                        @else
                            @include('website.deliverywithoutlogin')
                        @endif
                    </div>
                    <div class="tab-pane fade" id="pills-takeaway" role="tabpanel" aria-labelledby="pills-takeaway-tab">
                        @include('website.receive')

                    </div>
                </div>
            </div>

        </div>
        <div class="modal-content d-none" id="notallowdelivary">
            <div class="modal-header border-0">
                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-map-marker-alt main-color fs-1"></i>
                <h4 class="mt-4">
                    @lang('header.notdeliverylocation')
                </h4>
            </div>
            <div class="modal-footer d-flex border-0 align-items-center justify-content-center">
                <button type="button" class="btn" onclick="reopenddelivery()">
                    @lang('header.changeaddress')
                </button> <button type="button" class="btn reversed main-color"
                    onclick="closeDeliveryModalAndOpenBranches()">
                    @lang('header.changebranch')</button>
            </div>
        </div>
    </div>
</div>
<!-- end delivery details modal -->

@push('scripts')
    <script>
   

        function reopenddelivery() {
            document.querySelector('#notallowdelivary').classList.add('d-none');
            document.querySelector('#delivary').classList.remove('d-none');
        }

        function closeDeliveryModalAndOpenBranches() {
            // Hide the delivery modal
            var deliveryModal = new bootstrap.Modal(document.getElementById('deliveryModal'));
            deliveryModal.hide();

            // Call the reopendbranch() function to show the branches modal
            var branchesModal = new bootstrap.Modal(document.getElementById('branchesModal'));
            branchesModal.show();
        }
    </script>
@endpush

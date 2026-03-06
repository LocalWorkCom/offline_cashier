@extends('layouts.master')

@section('styles')
@endsection

@section('content')
    <div class="container">
        <h1>@lang('order.add_order')</h1>
        <form action="{{ route('order.store') }}" method="POST">
            @csrf

            <!-- Language -->
            {{-- <div class="mb-3">
            <label for="lang" class="form-label">Language</label>
            <select class="form-select" name="lang" id="lang">
                <option value="ar">Arabic</option>
                <option value="en">English</option>
            </select>
        </div> --}}

            <!-- Type -->
            <div class="mb-3">
                <label for="type" class="form-label">Order Type</label>
                <select class="form-select" name="type" id="type" required>
                    <option value="Takeaway">Takeaway</option>
                    <option value="dine-in">Dine In</option>
                    <option value="Delivery">Delivery</option>
                    <option value="CallCenter">Call Center</option>
                </select>
            </div>

            <!-- Branch -->
            <div class="mb-3">
                <label for="branch_id" class="form-label">Branch</label>
                <select class="form-select" name="branch_id" id="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @if ($barnch->is_default) selected @endif>
                            {{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Table (for InRestaurant orders) -->
            <div class="mb-3">
                <label for="table_id" class="form-label">Table</label>
                <select class="form-select" name="table_id" id="table_id">
                    <option value="">Select Table (Optional)</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Coupon Code -->
            <div class="mb-3">
                <label for="coupon_code" class="form-label">Coupon Code</label>
                <input type="text" class="form-control" name="coupon_code" id="coupon_code">
            </div>

            <!-- Order Details -->
            <h4>Order Details</h4>
            <div id="order-details">
                <div class="order-detail mb-3">
                    <label>Dish</label>
                    <select class="form-select mb-2" name="details[0][dish_id]" required>
                        @foreach ($dishes as $dish)
                            <option value="{{ $dish->id }}">{{ $dish->name }}</option>
                        @endforeach
                    </select>
                    <label>Quantity</label>
                    <input type="number" class="form-control mb-2" name="details[0][quantity]" required>
                    <label>Note</label>
                    <textarea class="form-control mb-2" name="details[0][note]"></textarea>
                </div>
            </div>
            <button type="button" class="btn btn-primary mb-3" id="add-order-detail">Add More Dish</button>

            <!-- Add-ons -->
            <h4>Add-ons</h4>
            <div id="order-addons">
                <div class="order-addon mb-3">
                    <label>Add-on</label>
                    <select class="form-select mb-2" name="addons[0][dish_addon_id]" required>
                        @foreach ($addons as $addon)
                            <option value="{{ $addon->id }}">{{ $addon->name }}</option>
                        @endforeach
                    </select>
                    <label>Quantity</label>
                    <input type="number" class="form-control mb-2" name="addons[0][quantity]" required>
                </div>
            </div>

            <!-- Note -->
            <div class="mb-3">
                <label for="note" class="form-label">Note</label>
                <textarea class="form-control" name="note" id="note" rows="3"></textarea>
            </div>

            <button type="button" class="btn btn-primary mb-3" id="add-order-addon">Add More Add-on</button>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-success">Create Order</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        let orderDetailIndex = 1;
        let addonIndex = 1;

        document.getElementById('add-order-detail').addEventListener('click', () => {
            const detailTemplate = `
            <div class="order-detail mb-3">
                <label>Dish</label>
                <select class="form-select mb-2" name="details[${orderDetailIndex}][dish_id]" required>
                    @foreach ($dishes as $dish)
                        <option value="{{ $dish->id }}">{{ $dish->name }}</option>
                    @endforeach
                </select>
                <label>Quantity</label>
                <input type="number" class="form-control mb-2" name="details[${orderDetailIndex}][quantity]" required>
                <label>Note</label>
                <textarea class="form-control mb-2" name="details[${orderDetailIndex}][note]"></textarea>
            </div>
        `;
            document.getElementById('order-details').insertAdjacentHTML('beforeend', detailTemplate);
            orderDetailIndex++;
        });

        document.getElementById('add-order-addon').addEventListener('click', () => {
            const addonTemplate = `
            <div class="order-addon mb-3">
                <label>Add-on</label>
                <select class="form-select mb-2" name="addons[${addonIndex}][dish_addon_id]" required>
                    @foreach ($addons as $addon)
                        <option value="{{ $addon->id }}">{{ $addon->name }}</option>
                    @endforeach
                </select>
                <label>Quantity</label>
                <input type="number" class="form-control mb-2" name="addons[${addonIndex}][quantity]" required>
            </div>
        `;
            document.getElementById('order-addons').insertAdjacentHTML('beforeend', addonTemplate);
            addonIndex++;
        });
    </script>
@endsection

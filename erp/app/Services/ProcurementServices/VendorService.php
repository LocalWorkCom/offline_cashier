<?php

namespace App\Services\ProcurementServices;

use App\Models\Category;
use App\Models\Country;
use App\Models\HighValueRule;
use App\Models\PaymentType;
use App\Models\Vendor;
use App\Models\PaymentInterval;
use App\Models\PurchasingBudget;
use App\Models\PurchasingBudgetLog;
use App\Models\VendorCategory;
use App\Models\VendorInfo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorService
{
    public function index(Request $request)
    {
        $query = vendor::with([
            'country',          // Vendor → Country
            'info',             // Vendor → VendorInfo
            'categories',       // Vendor → VendorCategory
            'paymentMethods',    // Vendor → PaymentMethod
            'paymentTypes',      // Vendor → PaymentType
            'creator',          // Created By Employee
            'updater',          // Updated By Employee
            'deleter',          // Deleted By Employee
        ]);
        if ($request->filled('status')) {
            $query->where('is_active', $request->filled('status'));
        }
        if ($request->filled('from')) {
            $query->where('created_at', $request->filled('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', $request->filled('to'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->filled('type'));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->filled('shipment')) {
            $category = Category::where('name_en','shipment')->first();
            $query->whereHas('categories', function ($q) use ( $category) {
                $q->where('category_id',  $category->id);
            });
        }
        $query->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc');
        return $query;
    }
    public function show(Request $request, $id)
    {
        $query = vendor::with([
            'country',
            'info',
            'categories',
           'paymentMethods', 'paymentTypes',
            'creator',
            'updater',
            'deleter',
        ])->find($id);
        return $query;
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $country = Country::where('phone_code', $request->country_code)->first();
            $vendor = Vendor::create([
                'name_ar'          => $request->name_ar,
                'name_en'          => $request->name_en,
                'type'             => $request->type,
                'country_id'     => $country->id,
                'phone'            => $request->phone,
                'email'            => $request->email,
                'address'          => $request->address,
                'latitude'         => $request->latitude,
                'longitude'        => $request->longitude,
                'communication_method' => $request->communication_method,
                'remaining_credit' => 0,
                'credit_balance'   => 0,
                'is_active'        => $request->is_active,
                'rate'             => 0,
                'created_by'       => authActionSave()['by'],
            ]);

            VendorInfo::create([
                'vendor_id'                      => $vendor->id,
                'tax_card_number'                => $request->tax_card_number,
                'commercial_registration_number' => $request->commercial_registration_number,
                'contact_name'                   => $request->contact_name,
                'contact_phone'                  => $request->contact_phone,
                'contact_email'                  => $request->contact_email,
                'country_code'                   => $request->contact_country_code,
            ]);

            foreach ($request->categories as $index => $categoryId) {
                VendorCategory::create([
                    'vendor_id'      => $vendor->id,
                    'category_id'    => $categoryId,
                    'sub_category_id' => $request->sub_categories[$index] ?? null,                ]);
            }

            $vendor->paymentMethods()->sync($request->payment_methods);
            $vendor->paymentTypes()->sync($request->payment_types);

            DB::commit();

            return $vendor;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


  public function update(Request $request, Vendor $vendor)
{
    // Begin transaction
    DB::beginTransaction();
//    try {
        // Update Vendor
        $country = Country::where('phone_code', $request->country_code)->first();
        $vendor->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'type' => $request->type,
            'country_id' => $country->id,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'communication_method' => $request->communication_method,
            'remaining_credit' => $request->remaining_credit ?? $vendor->remaining_credit,
            'credit_balance' => $request->credit_balance?? $vendor->credit_balance,
            'is_active' => $request->is_active,
            'modified_by' => authActionSave()['by'],
        ]);

        // Update VendorInfo
        $vendor->info()->update([
            'tax_card_number' => $request->tax_card_number,
            'commercial_registration_number' => $request->commercial_registration_number,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'country_code' => $request->contact_country_code,
        ]);

        // Update categories
        $vendor->categories()->delete();
        foreach ($request->categories as $index => $categoryId) {
            VendorCategory::create([
                'vendor_id' => $vendor->id,
                'category_id' => $categoryId,
                'sub_category_id' => $request->sub_categories[$index] ?? null,
            ]);
        }

        // Update payment methods/types
        $vendor->paymentMethods()->sync($request->payment_methods);
        $vendor->paymentTypes()->sync($request->payment_types);

        DB::commit();

        return $vendor->fresh();
//    } catch (\Exception $e) {
//        DB::rollBack();
//        throw $e;
//    }
}

}

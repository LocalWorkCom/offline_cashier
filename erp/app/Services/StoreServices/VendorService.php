<?php


namespace App\Services\StoreServices;

use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

class VendorService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function getAllVendors($checkToken)
    {

        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        return Vendor::with('country')->get();
    }

    public function getVendor($id)
    {
        return Vendor::findOrFail($id);
    }

    public function createVendor($data, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $vendor = new Vendor();
        $vendor->name_ar = $data['name_ar'];
        $vendor->name_en = $data['name_en'];
        $vendor->contact_person = $data['contact_person'];
        $vendor->phone = $data['phone'];
        $vendor->email = $data['email'];
        $vendor->address_ar = $data['address_ar'];
        $vendor->address_en = $data['address_en'];
        $vendor->country_id = $data['country_id'];
        $vendor->created_by = Auth::user()->id;
        $vendor->created_at = now();
        $vendor->save();
    }

    public function updateVendor($data, $id, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $vendor = Vendor::findOrFail($id);
        $vendor->name_ar = $data['name_ar'];
        $vendor->name_en = $data['name_en'];
        $vendor->contact_person = $data['contact_person'];
        $vendor->phone = $data['phone'];
        $vendor->email = $data['email'];
        $vendor->address_ar = $data['address_ar'];
        $vendor->address_en = $data['address_en'];
        $vendor->country_id = $data['country_id'];
        $vendor->modified_by = Auth::user()->id;
        $vendor->updated_at = now();
        $vendor->save();
    }

    public function deleteVendor($id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $vendor = Vendor::find($id);
        $vendor->deleted_by = Auth::user()->id;
        $vendor->save();
        $vendor->delete();
    }
}

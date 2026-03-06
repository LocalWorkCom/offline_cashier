<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HotelController extends Controller
{
    protected $lang;

    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function index()
    {
        $hotels = Hotel::with('branch', 'area', 'city', 'country')->get();
        return ResponseWithSuccessData($this->lang, $hotels, 1);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'address_ar' => 'required|string',
            'address_en' => 'required|string',
            'country_id' => 'required|string|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'phone_number' => 'required|string',
            'building_number' => 'required|integer',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|boolean',
            'note' => 'nullable|string',
            'shiping_cost' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }
        $validatedData = $validator->validated();

        $hotels = Hotel::create($validatedData);
        return ResponseWithSuccessData($this->lang, $hotels, 1);
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'address_ar' => 'required|string',
            'address_en' => 'required|string',
            'country_id' => 'required|string|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'phone_number' => 'required|string',
            'building_number' => 'required|integer',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|boolean',
            'note' => 'nullable|string',
            'shiping_cost' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }
        $validatedData = $validator->validated();
        $hotel = Hotel::where('id',$id)->first();
        if ($hotel == null) {
            return respondError($this->lang === 'ar' ? 'لم يتم العثور علي الفندق .' : ' hotel id not found', 404);
        }
        $hotel->update($validatedData);
        return ResponseWithSuccessData($this->lang, $hotel, 1);
    }
    public function delete($id)
    {
        $hotel = Hotel::where('id',$id)->first();
        if ($hotel == null) {
            return respondError($this->lang === 'ar' ? 'لم يتم العثور علي الفندق .' : ' hotel id not found', 404);
        }
        $hotel->delete();
        $message = $this->lang === 'ar' ? 'تم حذف الفندق بنجاح' : 'hotel deleted successfully';
        return ResponseWithSuccessData($this->lang, $message, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $hotel = Hotel::with('branch', 'area', 'city', 'country')->where('id',$id)->first();
        if ($hotel == null) {
            return respondError($this->lang === 'ar' ? 'لم يتم العثور علي الفندق .' : ' hotel id not found', 404);
        }
        $hotel->status = $hotel->status == 0 ? ($lang === 'ar' ? 'غير نشط' : 'inactive') : ($lang === 'ar' ? 'نشط' : 'active');
        return ResponseWithSuccessData($this->lang, $hotel, 1);
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    protected $lang;

    public function __construct()
    {
        $this->lang = app()->getLocale();
    }
    public function index()
    {
        $hotels = Hotel::all();
        $countries = Country::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('dashboard.hotel.list', compact('hotels', 'countries'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validatedData = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'address_ar' => 'required|string',
            'address_en' => 'required|string',
            'country_id' => 'required|string|size:36',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'phone_number' => 'required|string',
            'building_number' => 'required|integer',
            'branch_name_en' => 'nullable|string',
            'branch_name_ar' => 'nullable|string',
            'status' => 'required',
            'note' => 'nullable|string',
            'shiping_cost' => 'nullable|integer',
        ]);

        Hotel::create($validatedData);
        $message = 'Hotel created successfully!';
        return redirect('dashboard/hotels')->with('message',$this->lang == 'en' ? $message : 'تم إنشاء الفندق بنجاح!');
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'address_ar' => 'required|string',
            'address_en' => 'required|string',
            'country_id' => 'required|string|size:36',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'phone_number' => 'required|string',
            'building_number' => 'required|integer',
            'branch_name_en' => 'nullable|string',
            'branch_name_ar' => 'nullable|string',
            'status' => 'required',
            'note' => 'nullable|string',
            'shiping_cost' => 'nullable|integer',
        ]);
        $hotel = Hotel::findOrFail($id);
        $hotel->update($validatedData);
        $message = 'Hotel updated successfully!';
        return redirect('dashboard/hotels')->with('message',$this->lang == 'en' ? $message : 'تم تحديث الفندق بنجاح!');
    }
    public function delete($id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->delete();
        $message = 'Hotel deleted successfully!';
        return redirect('dashboard/hotels')->with('message', $this->lang == 'en' ? $message : 'تم حذف الفندق بنجاح!');
    }
    public function city($country)
    {
        $cities = City::with('country')->where('country_id',$country)->whereNull('deleted_at')
            ->orderBy( 'created_at', 'desc')
            ->get();
            return response()->json($cities);
    }
    public function region($city)
    {
        $cities = Area::with('city')->where('city_id',$city)->whereNull('deleted_at')
            ->orderBy( 'created_at', 'desc')
            ->get();
            return response()->json($cities);

    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderSettingFormRequest;
use App\Models\Dish;
use App\Models\DishDiscount;
use App\Models\Offer;
use App\Models\OrderSetting;
use App\Services\SettingsServices\OrderSettingService;
use Illuminate\Support\Facades\File;

class OrderSettingController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $order_settings = OrderSetting::whereNull('deleted_at')->latest()->get();
//        dd($order_settings);
        $recordExists = OrderSetting::whereNull('deleted_at')->exists();
        return view('dashboard.order_settings.list', compact('order_settings','recordExists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.order_settings.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderSettingFormRequest $request)
    {
        $lang =  app()->getLocale();
        $data = $request->validated();
        $order_setting = new OrderSetting();
        $order_setting->tax_application = $data['tax_application'];
        $order_setting->coupon_application = $data['coupon_application'];
        $order_setting->tax_percentage = $data['tax_percentage'];
        $order_setting->time_cancellation = $data['time_cancellation'];
        $order_setting->delivery_time = $data['delivery_time'];
        $order_setting->delivery_difference = $data['delivery_difference'];
        $order_setting->created_by = auth('admin')->id() ?? 1 ;
        $order_setting->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/order_settings')->with('message',$message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order_setting = OrderSetting::findOrFail($id);
        return view('dashboard.order_settings.show', compact('order_setting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order_setting = OrderSetting::findOrFail($id);
        return view('dashboard.order_settings.edit', compact('order_setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderSettingFormRequest $request, string $id)
    {
        $lang =  app()->getLocale();
        $data = $request->validated();
        $order_setting = OrderSetting::findOrFail($id);
        $order_setting->tax_application = $data['tax_application'];
        $order_setting->coupon_application = $data['coupon_application'];
        $order_setting->tax_percentage = $data['tax_percentage'];
        $order_setting->time_cancellation = $data['time_cancellation'];
        $order_setting->delivery_time = $data['delivery_time'];
        $order_setting->delivery_difference = $data['delivery_difference'];
        $order_setting->modified_by = auth('admin')->id() ?? 1;
        $order_setting->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/order_settings')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang =  app()->getLocale();
        $order_setting = OrderSetting::findOrFail($id);
        $order_setting->deleted_by = auth('admin')->id() ?? 1;
        $order_setting->delete();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/order_settings')->with('message', $message);
    }
}

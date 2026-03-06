<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SliderFormRequest;
use App\Models\Discount;
use App\Models\Dish;
use App\Models\DishDiscount;
use App\Models\Offer;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SliderController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = Slider::with(['dish', 'offer', 'discount'])->get();
//        dd($sliders);
        return view('dashboard.slider.list', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dishes = Dish::where('is_active', 1)->get();
        $offers = Offer::whereHas('details')->where('is_active', 1)->get();
        $discounts = DishDiscount::with(['dish', 'discount'])
            ->whereHas('dish')
            ->whereHas('discount')
            ->get();
//        dd($discounts);
        return view('dashboard.slider.add', compact('dishes', 'offers', 'discounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SliderFormRequest $request)
    {
        $lang =  app()->getLocale();
        $data = $request->validated();
        $slider = new Slider();
        $slider->name_ar = $data['name_ar'];
        $slider->name_en = $data['name_en'];
        $slider->description_ar = $data['description_ar'];
        $slider->description_en = $data['description_en'];
        $slider->flag = $data['flag'];
        $slider->dish_id = $data['dish_id'] ?? null;
        $slider->offer_id = $data['offer_id'] ?? null;
        $slider->discount_id = $data['discount_id'] ?? null;
        $slider->created_by = auth('admin')->id() ?? 1 ;
        $image = $request->file('image');
//        dd($image);
        UploadFile('images/sliders', 'image', $slider, $image);
        $slider->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/sliders')->with('message',$message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $slider = Slider::findOrFail($id);
        return view('dashboard.slider.show', compact('slider'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $slider = Slider::findOrFail($id);
        $dishes = Dish::where('is_active', 1)->get();
        $offers = Offer::where('is_active', 1)->get();
        $discounts = DishDiscount::with(['dish', 'discount'])
            ->whereHas('dish')
            ->whereHas('discount')
            ->get();
        return view('dashboard.slider.edit', compact('slider','dishes','offers', 'discounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SliderFormRequest $request, string $id)
    {
        $lang =  app()->getLocale();
        $data = $request->validated();
        $slider = Slider::findOrFail($id);
        $slider->name_ar = $data['name_ar'];
        $slider->name_en = $data['name_en'];
        $slider->description_ar = $data['description_ar'];
        $slider->description_en = $data['description_en'];
        $slider->flag = $data['flag'];
        $slider->dish_id = $data['dish_id']??null;
        $slider->offer_id = $data['offer_id']??null;
        $slider->discount_id = $data['discount_id']??null;
        $slider->modified_by = auth('admin')->id() ?? 1;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Upload the new image and update the slider record
            UploadFile('images/sliders', 'image', $slider, $image);
        }
        $slider->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/sliders')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang =  app()->getLocale();
        $slider = Slider::findOrFail($id);

        if ($slider->image) {
            $imagePath = public_path('images/sliders/' . $slider->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }
        $slider->deleted_by = auth('admin')->id() ?? 1;

        $slider->delete();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/sliders')->with('message', $message);
    }
}

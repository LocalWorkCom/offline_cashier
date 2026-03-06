<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\ProductSize;
use App\Models\Size;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            // if (!CheckToken()) {
            //     return RespondWithBadRequest($lang, 5);
            // }
            $sizes = Size::where('deleted_at', null)->get();

            return ResponseWithSuccessData($lang, $sizes, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            // if (!CheckToken()) {
            //     return RespondWithBadRequest($lang, 5);
            // }
            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                "name_ar" => "required",
                "name_en" => "required",
                'category_id' => 'required',

            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            if (CheckExistColumnValue('sizes', 'name_ar', $request->name_ar) || CheckExistColumnValue('sizes', 'name_en', $request->name_en)) {
                return RespondWithBadRequest($lang, 9);
            }
            $size = new Size();
            $size->name_ar = $request->name_ar;
            $size->name_en = $request->name_en;
            $size->category_id = $request->category_id;
            $size->created_by = auth()->id();
            $size->save();
            return ResponseWithSuccessData($lang, $size, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckToken()) {
                return RespondWithBadRequest($lang, 5);
            }
            $size = Size::findOrFail($request->input('id'));
            return ResponseWithSuccessData($lang, $size, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                "name_ar" => "required",
                "name_en" => "required",
                'category_id' => 'required',

            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }
            $size = Size::findOrFail($request->input('id'));
            $size->name_ar = $request->name_ar;
            $size->name_en = $request->name_en;
            $size->category_id = $request->category_id;
            $size->modified_by = auth()->id();

            $size->save();

            return ResponseWithSuccessData($lang, $size, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $size = Size::findOrFail($request->input('id'));
            $is_allow = ProductSize::where('size_id', $request->input('id'))->first();
            if ($is_allow) {
                return RespondWithBadRequestData($lang, 5);
            } else {
                $size->deleted_by = auth()->id();
                $size->deleted_at = Carbon::now();

                return ResponseWithSuccessData($lang, $size, 1);
            }
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaticFormsRequest;
use App\Models\ReturnPolicy;
use Illuminate\Http\Request;

class ReturnPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $returns = ReturnPolicy::get();
        return view('dashboard.return.list', compact('returns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.return.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StaticFormsRequest $request)
    {
//        dd($request->all());
        $lang = app()->getLocale();
        $data = $request->validated();
        $return = new ReturnPolicy();
        $return->name_ar = $data['name_ar'];
        $return->name_en = $data['name_en'];
        $return->description_ar = $data['description_ar'];
        $return->description_en = $data['description_en'];
        $return->active = $data['active'];
        $return->created_by = auth('admin')->id() ?? 1 ;
        $return->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/returns')->with('message',$message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $return = ReturnPolicy::findOrFail($id);
        return view('dashboard.return.show', compact('return'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $return = ReturnPolicy::findOrFail($id);
        return view('dashboard.return.edit', compact('return'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StaticFormsRequest $request, string $id)
    {
        $lang = app()->getLocale();
        $data = $request->validated();
        $return = ReturnPolicy::findOrFail($id);
        $return->name_ar = $data['name_ar'];
        $return->name_en = $data['name_en'];
        $return->description_ar = $data['description_ar'];
        $return->description_en = $data['description_en'];
        $return->active = $data['active'];
        $return->modified_by = auth('admin')->id() ?? 1;
        $return->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/returns')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = app()->getLocale();
        $return = ReturnPolicy::findOrFail($id);
        $return->deleted_by = auth('admin')->id() ?? 1;
        $return->delete();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/returns')->with('message', $message);
    }
}

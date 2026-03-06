<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoFormRequest;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class JobTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $job_types = JobType::all();
        return view('dashboard.job_types.list', compact('job_types'));
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
    public function store(LogoFormRequest $request)
    {
        $lang =  app()->getLocale();
        $data = $request->validated();
        $job_type = new JobType();
        $job_type->name_ar = $data['name_ar'];
        $job_type->name_en = $data['name_en'];
        $job_type->created_by = auth('admin')->id() ?? 1 ;
        $job_type->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/job-types')->with('message',$message);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $job_type = JobType::find($id);

        if (!$job_type) {
            return response()->json(['error' => 'JobType not found'], 404);
        }

        return response()->json([
            'name_ar' => $job_type->name_ar,
            'name_en' => $job_type->name_en,
        ]);
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
    public function update(LogoFormRequest $request, string $id)
    {
        $lang =  app()->getLocale();
        $data = $request->validated();
        $job_type = JobType::findOrFail($id);
        $job_type->name_ar = $data['name_ar'];
        $job_type->name_en = $data['name_en'];
        $job_type->modified_by = auth('admin')->id() ?? 1;
        $job_type->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/job-types')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang =  app()->getLocale();
        $job_type = JobType::findOrFail($id);
        $job_type->deleted_by = auth('admin')->id() ?? 1;

        $job_type->save();
        $job_type->delete();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/job-types')->with('message', $message);
    }
}

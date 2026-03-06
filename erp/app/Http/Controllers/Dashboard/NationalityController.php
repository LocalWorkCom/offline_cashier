<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use App\Services\HR_Services\NationalityService;
use Illuminate\Http\Request;

class NationalityController extends Controller
{
    protected $nationalityService;
    protected $checkToken;


    public function __construct(NationalityService $nationalityService)
    {
        $this->nationalityService = $nationalityService;
        $this->checkToken = false;
    }
    public function index()
    {
        $nationalitiesQuery = $this->nationalityService->index($this->checkToken);
        $nationalities = $nationalitiesQuery->get(); // Execute the query

        return view('dashboard.nationalities.index', compact('nationalities'));
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
        $this->nationalityService->store($request, $this->checkToken);
        return redirect()->route('nationality.list')->with('success', 'Marital Status created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        $this->nationalityService->update($request, $id, $this->checkToken);
        return redirect()->route('nationality.list')->with('success', 'Marital Status created successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->nationalityService->delete($id, $this->checkToken);
        return redirect()->route('nationality.list')->with('success', 'Marital Status created successfully!');
    }
}

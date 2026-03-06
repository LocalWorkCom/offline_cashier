<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\BusinessActivity;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Violation;
use App\Models\violations;
use App\Models\ViolationType;
use App\Services\HR_Services\ViolationService;

class ViolationController extends Controller
{
    protected $ViolationService;
    protected $checkToken;
    protected $lang;

    public function __construct(ViolationService  $ViolationService)
    {
        $this->ViolationService  = $ViolationService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        $response  = $this->ViolationService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $violations = Violation::hydrate($responseData['data']);

        return view('dashboard.violation.list', compact('violations'));
    }

    public function create()
    {
        $violationTypes  = ViolationType::all(); // Example
        $employees = Employee::all();

        return view('dashboard.violation.add', compact('violationTypes', 'employees'));
    }

    public function store(Request $request)
    {
        $response = $this->ViolationService->store($request, $this->checkToken);
        $responseData = $response->original;

        if (!$responseData['status'] && isset($responseData['data'])) {
            return redirect()->back()->withErrors($responseData['data'])->withInput();
        }

        return redirect()->route('violations.list')->with('message', $responseData['message']);
    }

    public function show($id)
    {
        $violations = Violation::findOrFail($id);
        // $violations = BusinessActivity::all(); // Example

        return view('dashboard.violation.show', compact('violations', 'violations'));
    }

    public function edit($id)
    {
        $violations = Violation::findOrFail($id);
        $violations = BusinessActivity::where('is_active', 1)->whereNull('deleted_at')->get(); // Get all business activities

        return view('dashboard.violation.edit', compact('violations', 'violations', 'id'));
    }

    public function update(Request $request, $id)
    {
        $response = $this->ViolationService->update($request, $id, $this->checkToken);
        $responseData = $response->original;

        if (!$responseData['status'] && isset($responseData['data'])) {
            return redirect()->back()->withErrors($responseData['data'])->withInput();
        }

        return redirect()->route('violation.list')->with('message', $responseData['message']);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->ViolationService->destroy($request, $id, $this->checkToken);
        $responseData = $response->original;

        return redirect()->route('violation.list')->with('message', $responseData['message']);
    }
    public function resolve($id)
{
    $violation = Violation::findOrFail($id);
    $violation->status = 'resolved';
    $violation->save();

    return redirect()->back()->with('message', __('violation.StatusUpdated'));
}

}

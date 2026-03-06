<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Services\HR_Services\LeaveSettingPositionService;
use App\Models\LeaveType;
use App\Models\Country;
use App\Models\Position;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LeaveSettingPositionController extends Controller
{
    protected $leaveSettingPositionService;

    public function __construct(LeaveSettingPositionService $leaveSettingPositionService)
    {
        $this->leaveSettingPositionService = $leaveSettingPositionService;
    }

    public function index(Request $request)
    {
        $response = $this->leaveSettingPositionService->index($request);
        $LeaveSetting = $response->get();
        $leaveTypes = LeaveType::all();
        $countries = Country::all();
        $positions = Position::all();
        $roles = DB::table('roles')->where('guard_name', 'admin')->get();
        return view('dashboard.leave_setting_position.list', compact('LeaveSetting', 'countries', 'leaveTypes', 'positions', 'roles'));
    }

    public function show($id)
    {
        $response = $this->leaveSettingPositionService->show($id);
        $responseData = $response->original;
        return $LeaveType = $responseData['data'];
    }

    public function store(Request $request)
    {
        $response = $this->leaveSettingPositionService->add($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('leave-setting-positions.list')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('leave-setting-positions.list')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->leaveSettingPositionService->edit($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('leave-setting-positions.list')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('leave-setting-positions.list')->with('message', $message);
    }
    public function delete(Request $request, $id)
    {
        $response = $this->leaveSettingPositionService->delete($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect()->route('leave-setting-positions.list')->with('message', $message);
    }
}

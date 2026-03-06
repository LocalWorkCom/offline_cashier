<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Services\HR_Services\TimetableService;
use Illuminate\Http\Request;

class TimeTableController extends Controller
{
    protected $timeTableService;
    protected $checkToken;


    public function __construct(TimetableService $timeTableService)
    {
        $this->timeTableService = $timeTableService;
        $this->checkToken = false;
    }

    public function index()
    {
        $timeTables = $this->timeTableService->index()->get();
        return view('dashboard.timeTables.index', compact('timeTables'));
    }
    public function show($id)
    {
        $timeTable = $this->timeTableService->show($id);
        return view('dashboard.timeTables.show', compact('timeTable'));
    }
    public function create()
    {
        return view('dashboard.timeTables.create');
    }

    public function store(Request $request)
    {
        $result = $this->timeTableService->store($request);

        if ($result['status'] === 'error') {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('timeTable.index')->with('success', 'Timetable created successfully!');
    }
    public function edit($id)
    {
        $timetable = Timetable::findOrFail($id);
        return view('dashboard.timeTables.edit', compact('timetable'));
    }

    public function update(Request $request, $id)
    {
        $result = $this->timeTableService->update($request, $id, $this->checkToken);

        if ($result['status'] === 'error') {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('timeTable.index')->with('success', 'Timetable updated successfully!');
    }
    public function delete($id)
    {
        $this->timeTableService->delete($id, $this->checkToken);
        return redirect()->route('timeTable.index')->with('success', 'Timetable deleted successfully!');
    }
}

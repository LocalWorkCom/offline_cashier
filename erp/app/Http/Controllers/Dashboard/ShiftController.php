<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftDetail;
use App\Models\Timetable;
use App\Services\HR_Services\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    protected $shiftService;
    protected $checkToken;


    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
        $this->checkToken = false;
    }

    public function index()
    {
        $shifts = $this->shiftService->index()->get();
        $timetables = Timetable::all();
        return view('dashboard.shift.index', compact('shifts', 'timetables'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'shift_details' => 'required|array',
            'shift_details.*.timetable_id' => 'required|exists:timetables,id',
            'shift_details.*.day_index' => 'required|integer|min:0|max:6',
        ]);
        $validatedData['details'] = $validatedData['shift_details'];
        unset($validatedData['shift_details']);

        $this->shiftService->store($validatedData, $this->checkToken);
        return redirect()->route('shifts.list')->with('success', 'Shift created successfully!');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'shift_details' => 'required|array',
            'shift_details.*.id' => 'nullable|exists:shift_details,id',
            'shift_details.*.timetable_id' => 'required|exists:timetables,id',
            'shift_details.*.day_index' => 'required|integer|min:0|max:6',
        ]);
        $validatedData['details'] = $validatedData['shift_details'];
        unset($validatedData['shift_details']);

        $this->shiftService->update($validatedData, $id, $this->checkToken);
        return redirect()->route('shifts.list')->with('success', 'Shift updated successfully!');
    }
    public function delete($id)
    {
        $this->shiftService->delete($id, $this->checkToken);
        return redirect()->route('shifts.list')->with('success', 'Shift deleted successfully!');
    }

    public function details(Shift $shift)
    {
        try {
            // Get the timetable associated with the shift
            $timetable = Timetable::find($shift->id);

            // Get all shift details (days) for this shift
            $days = ShiftDetail::where('shift_id', $shift->id)
                ->orderBy('day_index')
                ->get();

            return response()->json([
                'success' => true,
                'shift' => $shift,
                'timetable' => $timetable,
                'days' => $days
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching shift details'
            ], 500);
        }
    }}

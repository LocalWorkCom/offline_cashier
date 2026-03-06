<?php


namespace App\Services\HR_Services;

use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\ShiftDetail;

class ShiftService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index()
    {
        return Shift::with('details.timetable');
    }

    public function show($id, $lang = 'ar')
    {
        $shifts = Shift::with('details.timetable')->find($id);
        if (!$shifts) {
            $message = $lang == 'en' ? 'Shift not found' : 'الوردية غير موجودة';
            return ['status' => 'error', 'message' => $message];
        }
        return $shifts;
    }

    public function store($data)
    {
        $created_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $shift = new Shift();
        $shift->name_ar = $data['name_ar'];
        $shift->name_en = $data['name_en'];
        $shift->description_ar = $data['description_ar'];
        $shift->description_en = $data['description_en'];
        $shift->created_by = $created_by;
        $shift->created_at = now();
        $shift->save();

        if (isset($data['details']) && is_array($data['details'])) {
            foreach ($data['details'] as $detail) {
                $shiftDetail = new ShiftDetail();
                $shiftDetail->shift_id = $shift->id;
                $shiftDetail->timetable_id = $detail['timetable_id'];
                $shiftDetail->day_index = $detail['day_index'];
                $shiftDetail->created_by = $created_by;
                $shiftDetail->created_at = now();
                $shiftDetail->save();
            }
        }

        return $shift->load('details.timetable');
    }

    public function update($data, $id)
    {
        $updated_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $shift = Shift::findOrFail($id);
        $shift->name_ar = $data['name_ar'];
        $shift->name_en = $data['name_en'];
        $shift->description_ar = $data['description_ar'];
        $shift->description_en = $data['description_en'];
        $shift->modified_by = $updated_by;
        $shift->updated_at = now();
        $shift->save();

        if (isset($data['details']) && is_array($data['details'])) {
            // Collect IDs of submitted shift details
            $submittedDetailIds = [];
            foreach ($data['details'] as $detail) {
                if (!empty($detail['id'])) {
                    $submittedDetailIds[] = $detail['id'];
                }
            }

            // Delete shift details not included in the submitted data
            ShiftDetail::where('shift_id', $shift->id)
                ->whereNotIn('id', $submittedDetailIds)
                ->delete();

            // Update or create shift details
            foreach ($data['details'] as $index => $detail) {
                $shiftDetailData = [
                    'shift_id' => $shift->id,
                    'timetable_id' => $detail['timetable_id'],
                    'day_index' => $detail['day_index'],
                    'modified_by' => $updated_by,
                    'updated_at' => now(),
                ];

                if (!empty($detail['id'])) {
                    $shiftDetail = ShiftDetail::find($detail['id']);
                    if ($shiftDetail && $shiftDetail->shift_id == $shift->id) {
                        $shiftDetail->update($shiftDetailData);
                    }
                } else {
                    $shiftDetailData['created_by'] = $updated_by;
                    $shiftDetailData['created_at'] = now();
                    ShiftDetail::create($shiftDetailData);
                }
            }
        } else {
            // If no details are submitted, delete all existing shift details
            ShiftDetail::where('shift_id', $shift->id)->delete();
        }

        return $shift->load('details.timetable');
    }

    public function delete($id, $lang = 'ar')
    {
        $deleted_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $shift = Shift::find($id);
        if (!$shift) {
            $message = $lang == 'en' ? 'Shift not found' : 'الوردية غير موجودة';
            return ['status' => 'error', 'message' => $message, 'code' => 404];
        }
        $shiftDetailsCount = ShiftDetail::where('shift_id', $id)->count();
        $shiftEmployeeCount = EmployeeSchedule::where('shift_id', $id)->count();
        if ($shiftDetailsCount || $shiftEmployeeCount) {
            $message = $lang == 'en'
                ? 'The time table cannot be deleted because it is linked to shift.'
                : 'لا يمكن حذف الجدول الزمني لأنه مرتبط بالورديات.';
            return ['status' => 'error', 'message' => $message, 'code' => 400];
        }
        ShiftDetail::where('shift_id', $id)->delete();
        $shift->deleted_by = $deleted_by;
        $shift->save();
        $shift->delete();

        $message = $lang == 'en' ? 'Timetable deleted successfully' : 'تم حذف الجدول الزمني بنجاح';
        return ['status' => 'success', 'message' => $message];
    }
}

<?php


namespace App\Services\HR_Services;

use App\Models\LeaveType;
use App\Models\LeaveSetting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeaveTypeService
{
    protected $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'en');
        App::setLocale($this->lang);
    }

    public function index(Request $request)
    {

        try {
            return $data = LeaveType::query();
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondError(($this->lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    // public function show($id, Request $request)
    // {
    //     try {
    //         $leave_type = LeaveType::findOrFail($id);
    //         $leave_type->makeHidden(['name'])->makeVisible(['name_ar', 'name_en']);
    //         return ResponseWithSuccessData($this->lang, $leave_type, 1);
    //     } catch (\Exception $e) {
    //         return RespondWithBadRequestData($this->lang, 2);
    //     }
    // }

    public function show($request, $id)
    {
        try {
            $employee = auth()->user();
            $check_leave = LeaveType::find($id);
            if (!$check_leave) {
                return respondError(($request->lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $request->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }
            $flag = LeaveSetting::where('leave_type_id', $id)
                ->where('country_id', $employee->country_id)
                ->first();

            // apply change directly to the model (not transform)
            $check_leave->required_doc = $flag?->upload_certificate;
            // $leave_type->makeHidden(['name'])->makeVisible(['name_ar', 'name_en']);
            return ResponseWithSuccessData($request->lang, $check_leave, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            // Validate the input
            // $validator = Validator::make($request->all(), [
            //     'name_ar' => 'required|string|unique:leave_types,name_ar',
            //     'name_en' => 'required|string|unique:leave_types,name_en'
            // ]);
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    Rule::unique('leave_types', 'name_ar')
                        ->whereNull('deleted_at'),
                ],
                'name_en' => [
                    'required',
                    'string',
                    Rule::unique('leave_types', 'name_en')
                        ->whereNull('deleted_at'),
                ],
                'details_ar' => ['nullable', 'string'],
                'details_en' => ['nullable', 'string'],
                'image' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:2048'],
            ]);

            if ($validator->fails()) {
                return respondError(($this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $craeted = authActionSave();
            $created_by = $craeted['by'];
            $created_by_type = $craeted['type'];

            // Create the new leave
            $leave = new LeaveType();
            $leave->name_ar = $request->name_ar;
            $leave->name_en = $request->name_en;
            $leave->details_ar = $request->details_ar ?? null;
            $leave->details_en = $request->details_en ?? null;
            $leave->created_by = $created_by;
            $leave->created_by_type = $created_by_type;

            $image = $request->file('image');
            UploadFile('images/leaveTypes', 'image', $leave, $image);

            $leave->save();
            $leave->makeHidden('name', 'name_site');
            $leave->makeVisible('name_ar', 'name_en');
            return ResponseWithSuccessData($this->lang, $leave, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching leave: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondError(($this->lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    Rule::unique('leave_types', 'name_ar')
                        ->ignore($id)
                        ->whereNull('deleted_at'),
                ],
                'name_en' => [
                    'required',
                    'string',
                    Rule::unique('leave_types', 'name_en')
                        ->ignore($id)
                        ->whereNull('deleted_at'),
                ],
                'details_ar' => ['nullable', 'string'],
                'details_en' => ['nullable', 'string'],
            ]);
            if ($validator->fails()) {
                return respondError(
                    $this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $check_leave = LeaveType::findOrFail($request->id);
            if (!$check_leave) {
                return respondError(($this->lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            if($check_leave->name_en =='Compensatory Leave' || $check_leave->name_en =='Sick Leave' || $check_leave->name_en =='Public Holidays'){
                return respondError(($this->lang == 'en' ? ['Can nott edit this item'] : ['لا تسطيع التعديل']), 400, $this->lang == 'en' ? ['Cannot edit this item because default leave type'] : ['لا يمكن تعديل هذا العنصر لأنه نوع إجازة افتراضي']);
            }

            $name_ar = $request->name_ar;
            $name_en = $request->name_en;

            $craeted = authActionSave();
            $modified_by = $craeted['by'];
            $modified_by_type = $craeted['type'];

            $check_leave->name_ar = $name_ar;
            $check_leave->name_en = $name_en;
            $check_leave->details_ar = $request->details_ar ? $request->details_ar : $check_leave->details_ar;
            $check_leave->details_en = $request->details_en ? $request->details_en : $check_leave->details_en;
            $check_leave->modified_by = $modified_by;
            $check_leave->modified_by_type = $modified_by_type;
            $check_leave->save();
            $check_leave->makeHidden('name', 'name_site');
            $check_leave->makeVisible('name_ar', 'name_en');
            return ResponseWithSuccessData($this->lang, $check_leave, 1);
        } catch (ModelNotFoundException $e) {
            $message = $this->lang === 'ar' ? 'نوع الإجازة غير موجود' : 'Leave type not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating leave type: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return RespondWithBadRequestData($this->lang, 2);
        }
    }


    public function delete(Request $request, $id)
    {

        try {

            $check_leave = LeaveType::find($id);
            if (!$check_leave) {
                return respondError(($this->lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            if($check_leave->name_en =='Compensatory Leave' || $check_leave->name_en =='Sick Leave' || $check_leave->name_en =='Public Holidays'){
                return respondError(($this->lang == 'en' ? ['Can nott delete this item'] : ['لا تسطيع الحذف']), 400, $this->lang == 'en' ? ['Cannot delete this item because default leave type'] : ['لا يمكن حذف هذا العنصر لأنه نوع إجازة افتراضي']);
            }

            $check_leave_setting = LeaveSetting::where('leave_type_id', $id)->exists();
            if ($check_leave_setting) {
                return respondError(($this->lang == 'en' ? ['Can nott delete this item'] : ['لا تسطيع الحذف']), 400, $this->lang == 'en' ? ['Cannot delete this item because related with leaves setting'] : ['لا يمكن حذف هذا العنصر لأنه مرتبط بإعدادات الاجازات']);
            }

            $craeted = authActionSave();
            $deleted_by = $craeted['by'];
            $deleted_by_type = $craeted['type'];
            $check_leave->deleted_by = $deleted_by;
            $check_leave->deleted_by_type = $deleted_by_type;
            $check_leave->save();
            $delete_color = $check_leave->delete();

            return RespondWithSuccessRequest($this->lang, 1);
        } catch (ModelNotFoundException $e) {
            $message = $this->lang === 'ar' ? 'نوع الإجازة غير موجود' : 'Leave type not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating leave type: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return RespondWithBadRequestData($this->lang, 2);
        }
    }
}

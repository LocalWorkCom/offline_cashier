<?php


namespace App\Services\ReportServices;

use App\Models\Branch;
use App\Models\DeliveryComplaints;
use App\Services\HR_Services\leaveSettingPositionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class DeliveryComplaintsReportService
{
    public function index(Request $request, $lang)
    {
        App::setLocale($lang);

        $complaints = DeliveryComplaints::with('order')->get();
        foreach ($complaints as $complaint) {
            $employee = $complaint->employee;
            $order    = $complaint->order;

            $complaint->delivery_name  = $employee ? ($employee->first_name . ' ' . $employee->last_name) : '';
            $complaint->delivery_phone = $employee->phone_number ?? '';

            $complaint->client_name  = $order?->client_name ?? '';
            $complaint->client_phone = $order?->client_phone ?? '';
            $complaint->branch       = $order?->branch?->name ?? '';
            $complaint->order_num    = $order?->order_number ?? '';

            unset($complaint->client);
        }


        return $complaints;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id, $lang)
    {
        try {


            App::setLocale($lang);

            $complaints = DeliveryComplaints::find($id);
            //            dd($complaints->order->branch->name_en);
            return $complaints;
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $complaints = DeliveryComplaints::find($id);
            $complaints->delete();
            $complaints->deleted_by = $admin->id ?? null;
            $complaints->save();
            return ResponseWithSuccessData($lang, $complaints, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "status" => "required|in:hold,done",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $complaint = DeliveryComplaints::find($id);

            $complaint->status = $request->status;
            $complaint->save();

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
}

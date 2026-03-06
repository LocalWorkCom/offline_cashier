<?php


namespace App\Services;

use App\Models\Branch;
use App\Models\Complaint;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class FeedbackService
{
    public function index(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $status = $request->input('status', 'all');
            $branch = $request->input('branch', 'all');
            $dateFrom = $request->input('date_from', null);
            $dateTo = $request->input('date_to', null);

            // Build the query
            $complaints = Complaint::with(['client', 'order'])->where('manage', 'admin');

            if ($status !== 'all') {
                $complaints = $complaints->where('status', $status);
            }

            if ($branch !== 'all') {
                $complaints = $complaints->whereHas('order', function($query) use ($branch) {
                    $query->where('branch_id', $branch);
                });
            }

            if ($dateFrom) {
                $complaints = $complaints->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $complaints = $complaints->whereDate('created_at', '<=', $dateTo);
            }

            $complaints = $complaints->get();

            // Process complaints data
            foreach ($complaints as $complaint) {
                $complaint->name = $complaint->client->name ?? '';
                $complaint->phone = $complaint->client->phone ?? '';
                $complaint->branch_en = Branch::find($complaint->order->branch_id)?->name_en ?? '';
                $complaint->branch_ar = Branch::find($complaint->order->branch_id)?->name_ar ?? '';
                $complaint->order_num = $complaint->order->order_number ?? '';
//                unset($complaint->client);
//                unset($complaint->order);
            }

            return ResponseWithSuccessData($lang,$complaints ,1);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "client_id" => "required|exists:users,id",
                "order_id" => "required|exists:orders,id",
                "rate" => "nullable|in:1,2,3,4,5",
                "status" => "required|in:pending,inprogress,solved",
                "complain" => "required|string",
                "comment" => "nullable|string",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
//            dd(Employee::find(auth('employee')->user()->id)->user_id);
            $complaint = Complaint::create($validator->validated()
                + [
                    'created_by' => Employee::find(auth('employee')->user()->id)->user_id,
                ]
            );

            return ResponseWithSuccessData($lang,$complaint,1);
        }
        catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $complaints = Complaint::find($id);
//            dd($complaints->order->branch->name_en);
            return ResponseWithSuccessData($lang,$complaints ,1);

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

            $complaints = Complaint::find($id);
            $complaints->delete();
            $complaints->deleted_by = $admin->id ?? null;
            $complaints->save();
            return ResponseWithSuccessData($lang,$complaints ,1);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function addComment(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "complaint_id" => "required|exists:complaints,id",
                "comment" => "required|string",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $complaint = Complaint::find($request->complaint_id);

            if ($complaint->comment != null) {
                return respondErrorData($lang== 'en'? 'Duplicated comment.' : 'تعليق مكرر.', 400, $lang== 'en'? ['Comment sent before.'] : ['تم ارسال تعليق من قبل.']);
            }

            $complaint->comment = $request->comment;
            $complaint->save();

            return ResponseWithSuccessData($lang,$complaint,1);
        }
        catch (\Exception $e) {
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
                "status" => "required|in:pending,inprogress,solved",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $complaint = Complaint::find($id);

            $complaint->status = $request->status;
            $complaint->save();

            return ResponseWithSuccessData($lang,$complaint,1);
        }
        catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
}

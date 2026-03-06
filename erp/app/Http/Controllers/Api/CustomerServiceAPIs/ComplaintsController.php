<?php

namespace App\Http\Controllers\Api\CustomerServiceAPIs;

use App\Events\NotifySent;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComplaintsController extends Controller
{
    private string $lang;
    private $messages;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
        App::setLocale($this->lang);
        $this->middleware(function ($request, $next) {
            if (!auth('employee')->check() || auth('employee')->user()->flag != 'customer_service') {
                return RespondWithBadRequest($this->lang, 4);
            }
            return $next($request);
        });
        $this->messages = [];
        if ($this->lang == 'en') {
            $this->messages = [
                'complaint_id.exists' => 'The complaint doesn\'t exist.',
            ];
        }
    }

    /**
     * Display a listing of complaints.
     */
    public function index()
    {
        try {
            $complaints = Complaint::with(['client', 'order'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($complaint) {
                    return $this->formatComplaint($complaint);
                });

            return ResponseWithSuccessData($this->lang, $complaints, 1);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        try {
            $validator = $this->validateComplaintRequest($request);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $complaint = Complaint::create($this->buildComplaintData($validator->validated()));
            return ResponseWithSuccessData($this->lang, $complaint, 1);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Display the specified complaint.
     */
    public function show($id)
    {
        try {
            $complaint = Complaint::findOrFail($id);
            return ResponseWithSuccessData($this->lang, $complaint, 1);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Remove the specified complaint.
     */
    public function destroy($id)
    {
        try {
            $complaint = Complaint::findOrFail($id);
            $complaint->delete();
            return ResponseWithSuccessData($this->lang, $complaint, 1);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Add comment to a complaint.
     */
    public function addComment(Request $request)
    {
        try {
            $validator = $this->validateCommentRequest($request);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $complaint = $this->updateComplaintComment(
                $request->complaint_id,
                $request->comment
            );

            return ResponseWithSuccessData($this->lang, $complaint, 1);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Change complaint status.
     */
    public function changeStatus(Request $request)
    {
        try {
            $validator = $this->validateStatusRequest($request);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $complaint = $this->updateComplaintStatus(
                $request->complaint_id,
                $request->status
            );

            return ResponseWithSuccessData($this->lang, $complaint, 1);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Send complaint to manager.
     */
    public function sendRequest(Request $request)
    {
        try {
            $validator = $this->validateSendRequest($request);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $result = $this->processComplaintRequest($request->complaint_id);
            return ResponseWithSuccessData($this->lang, $result['data'], $result['status']);
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /***********************
     * Helper Methods Below *
     ***********************/

    private function formatComplaint($complaint)
    {
        $branch_id = $complaint->order ?  $complaint->order->branch_id : null;
        $complaint->name = $complaint->client->name ?? '';
        $complaint->phone = $complaint->client->phone ?? '';
        $complaint->branch = $branch_id ? Branch::find($branch_id)?->name : '';
        $complaint->order_num = $complaint->order->order_number ?? '';
        $complaint->status = $this->translateStatus($complaint->status);

        unset($complaint->client, $complaint->order);
        return $complaint;
    }

    private function translateStatus($status)
    {
        $translations = [
            'solved' => __('complaints.Solved'),
            'inprogress' => __('complaints.InProgress'),
            'pending' => __('complaints.Pending')
        ];
        return $translations[$status] ?? __('complaints.Pending');
    }

    private function validateComplaintRequest($request)
    {
        return Validator::make($request->all(), [
            "client_id" => "required|exists:users,id,deleted_at,NULL",
            "order_id" => "required|exists:orders,id,deleted_at,NULL",
            "rate" => "nullable|in:1,2,3,4,5",
            "complain" => "required|string",
            "comment" => "nullable|string",
        ]);
    }

    private function validateCommentRequest($request)
    {
        return Validator::make($request->all(), [
            "complaint_id" => "required|exists:complaints,id,deleted_at,NULL",
            "comment" => "required|string",
        ], $this->messages);
    }

    private function validateStatusRequest($request)
    {
        return Validator::make($request->all(), [
            "complaint_id" => "required|exists:complaints,id,deleted_at,NULL",
            "status" => "required|in:pending,inprogress,solved",
        ], $this->messages);
    }

    private function validateSendRequest($request)
    {
        return Validator::make($request->all(), [
            "complaint_id" => "required|numeric|exists:complaints,id,deleted_at,NULL"
        ], $this->messages);
    }

    private function buildComplaintData($validatedData)
    {
        return array_merge($validatedData, [
            'created_by' => auth('employee')->user()->id
        ]);
    }

    private function updateComplaintComment($complaintId, $comment)
    {
        $complaint = Complaint::findOrFail($complaintId);
        if ($complaint->comment) {
            throw new \Exception(json_encode([
                'message' => $this->lang == 'en' ? 'Duplicated comment.' : 'تعليق مكرر.',
                'errorData' => [
                    'error' => $this->lang == 'en'
                        ? ['You cannot add more than one comment to a complaint.']
                        : ['لا يمكنك إضافة أكثر من تعليق واحد على الشكوى.']
                ]
            ]), 400);
        }
        $complaint->comment = $comment;
        $complaint->save();
        return $complaint;
    }

    private function updateComplaintStatus($complaintId, $status)
    {
        $complaint = Complaint::findOrFail($complaintId);
        $complaint->status = $status;
        $complaint->save();
        return $complaint;
    }

    private function processComplaintRequest($complaintId)
    {
        $complaint = Complaint::findOrFail($complaintId);

        if ($complaint->manage == 'admin') {
            throw new \Exception(json_encode([
                'message' => $this->lang == 'en' ? 'Sent before.' : 'تم الارسال مسبقا.',
                'errorData' => [
                    'error' => $this->lang == 'en'
                        ? ['Complaint sent before.']
                        : ['تم ارسال الشكوى مسبقا.']
                ]
            ]), 400);
        }

        $notification = $this->sendManagerNotification($complaintId);

        $complaint->update([
            'status' => 'inprogress',
            'manage' => 'admin'
        ]);
//        dd($complaint);

//        dd($notification);
        if (!$notification['status']) {
            throw new \Exception(json_encode([
                'message' => $this->lang == 'en' ? 'Notification failed.' : 'فشل ارسال اشعار.',
                'errorData' => [
                    'error' => $this->lang == 'en'
                        ? ['Complaint Sent but notification sending failed.']
                        : ['تم ارسال الشكوى لكن فشل ارسال اشعار.']
                ]
            ]), 400);
        }

        return $notification;
    }

    private function sendManagerNotification($complaintId)
    {
        $employee = auth('employee')->user();
        $managerId = Branch::where('id', $employee->branch_id)->value('employee_id');

        if (!$managerId) {
            throw new \Exception(json_encode([
                'message' => $this->lang == 'en' ? 'No Manager found.' : 'لم يتم العثور على مدير.',
                'errorData' => [
                    'error' => $this->lang == 'en'
                        ? ['No Manager found.']
                        : ['لم يتم العثور على مدير.']
                ]
            ]), 400);
        }

        $userManager = Employee::find($managerId)->user_id;
        $userFcm = User::find($userManager)->fcm_token;

        if (!$userFcm) {
            throw new \Exception(json_encode([
                'message' => $this->lang == 'en' ? "Manager can't receive notification." : 'المدير لا يمكنه استقبال اشعار.',
                'errorData' => [
                    'error' => $this->lang == 'en'
                        ? ["Manager can't receive notification."]
                        : ['المدير لا يمكنه استقبال اشعار.']
                ]
            ]), 400);
        }

        $url = $this->generateComplaintUrl($complaintId);

        $data = send_push_notification(
            $userFcm,
            'يوجد شكوى مرسلة',
            'New complaint received',
            'شكوى جديدة',
            'New complaint',
            'admin',
            $userManager,
            $userManager,
            $complaintId,
            $this->lang,null,
            $url,
        );

//        sendManagerNotification(
//            'admin',
//            'يوجد شكوى مرسلة',
//            'New complaint received',
//            'شكوى جديدة',
//            'New complaint',
//            $userManager,
//            $userManager,
//            $this->lang,
//            $complaintId,
//            $url,
//        );

        if($data)
        {
            broadcast(new NotifySent(User::find($userManager), $data));
        }
        return [
            'data' => $data ?: null,
            'status' => (bool)$data
        ];
    }

    private function generateComplaintUrl($complaintId)
    {
        $fullUrl = url()->current();
        $apiBaseUrl = Str::before($fullUrl, '/api');
        return $apiBaseUrl . '/dashboard/complaints/show/' . $complaintId;
    }

    private function validationErrorResponse($validator)
    {
        return respondError(
            $this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
            400,
            $validator->errors()
        );
    }

    private function handleError(\Exception $e)
    {
        $message = $e->getMessage();
        $errorData = null;

        if ($this->isJson($message)) {
            $parsed = json_decode($message, true);
            $message = $parsed['message'] ?? 'Error';
            $errorData = $parsed['errorData'] ?? null;
        }

        return respondError($message, $e->getCode() ?: 500, $errorData);
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

}

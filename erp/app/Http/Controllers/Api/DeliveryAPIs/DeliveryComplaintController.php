<?php

namespace App\Http\Controllers\Api\DeliveryAPIs;

use App\Http\Controllers\Controller;
use App\Models\DeliveryComplaints;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderCancellationReason;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class DeliveryComplaintController extends Controller
{
    protected $user;
    protected $userFlag;
    protected $lang;

    /**
     * Initialize common controller properties
     */
    protected function initialize(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
        App::setLocale($this->lang);

        if (auth('api')->check()) {
            $this->user = auth('api')->user();
            $this->userFlag = "client";
        } elseif (auth('employee')->check()) {
            $this->user = auth('employee')->user();
            $this->userFlag = $this->user->flag;
        }
    }

    /**
     * Display a listing of cancellation reasons
     */
    public function index(Request $request)
    {
        $this->initialize($request);

        if (!$this->user) {
            return RespondWithBadRequest($this->lang, 4);
        }

        $column = $this->lang === 'ar' ? 'reason_ar' : 'reason_en';
        $reasons = OrderCancellationReason::select('id', $column . ' as reason')
            ->whereJsonContains('type', $this->user->flag)
            ->get();

        return ResponseWithSuccessData($this->lang, $reasons, 1);
    }

    /**
     * Store a new delivery complaint
     */
    public function store(Request $request)
    {
        try {
            $this->initialize($request);

            if (!$this->user || $this->user->flag != 'driver') {
                return RespondWithBadRequest($this->lang, 4);
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|numeric|min:1|exists:orders,id',
                'reason_id' => 'nullable|numeric|min:1|exists:order_cancellation_reasons,id',
                'message' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return respondError(
                    $this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $order = Order::find($request->order_id);

            $latestTracking = $order->tracking()->latest()->first()->order_status;

            if (!$order || $order->delivery_id != $this->user->id) {
                return respondErrorData(
                    $this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $this->lang == 'en' ? ['Order not found.'] :[ 'لم يتم العثور على الطلب.']
                );
            }
            if ($latestTracking == 'delivered' || $latestTracking == 'completed') {
                return respondErrorData(
                    $this->lang == 'en' ? 'Order delivered.' : 'تم توصيل الطلب.',
                    400,
                    $this->lang == 'en' ? ['Order delivered.'] :[ 'تم توصيل الطلب.']
                );
            }

            // Create the complaint without notification fields
            $complaint = DeliveryComplaints::create([
                'employee_id' => $this->user->id,
                'order_id' => (int) $request->order_id,
                'message' => $request->message,
                'reason_id' => $request->reason_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]);
//            dd($complaint);

            // Send notifications and get responses
            $notificationResponses = $this->sendNotifications($order, $complaint);

            // Build the response with notification data
            $responseData = [
                'employee_id' => $complaint->employee_id,
                'order_id' => $complaint->order_id,
                'message' => $complaint->message,
                'reason_id' => $complaint->reason_id,
                'latitude' => $complaint->latitude,
                'longitude' => $complaint->longitude,
                'id' => $complaint->id,
                ...$notificationResponses // Spread the notification responses
            ];

            return response()->json([
                'status' => true,
                'message' => $this->lang == 'ar' ? 'طلب صحيح' : 'Valid request',
                'code' => 200,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Get branch contact information
     */
    public function contactUs(Request $request)
    {
        $this->initialize($request);

        $employee = $this->user;
        if (!$employee) {
            return RespondWithUnauthorizedRequest($this->lang, 4);
        }

        $branch = $employee->branch;
        if (!$branch) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => $this->lang == 'en' ? 'Branch not found' : 'الفرع غير موجود',
                'errorData' => ['error' => $this->lang == 'en' ? ['Branch not found'] : ['الفرع غير موجود']],
                'data' => null
            ], 404);
        }

        $responseData = [
            'branch_id' => $branch->id,
            'branch_name' => $this->lang === 'ar' ? $branch->name_ar : $branch->name_en,
            'branch_phone' => $branch->phone,
            'branch_address' => $this->lang === 'ar' ? $branch->address_ar : $branch->address_en,
            'latitude' => $branch->latitute,
            'longitude' => $branch->longitute,
        ];

        return ResponseWithSuccessData($this->lang, $responseData, 1);
    }

    /**
     * Send notifications and return their responses
     */
    protected function sendNotifications(Order $order, DeliveryComplaints $complaint)
    {
        $responses = [
            'notification_to_customer_service' => false,
            'notification_to_client' => null
        ];

        // Send to customer service if available
        $customerServiceInfo = $this->getCustomerServiceInfo($order->id);
        if ($customerServiceInfo && $customerServiceInfo[1]) {
            $responses['notification_to_customer_service'] = send_push_notification(
                $customerServiceInfo[1],
                'يوجد شكوى توصيل مرسلة على طلب رقم '. $order->order_number . ' بسبب ' . $complaint->message,
                'New delivery complaint received on order ' . $order->order_number . ' due to ' . $complaint->message,
                'شكوى توصيل جديدة',
                'New delivery complaint',
                'customer_service',
                $customerServiceInfo[0],
                $this->user->id,
                $complaint->id,
                $this->lang,
                null
            );
        }

        // Send to client if available
        $clientInfo = $this->getClientInfo($order->id);
        if ($clientInfo && $clientInfo[1]) {
            $responses['notification_to_client'] = send_push_notification(
                $clientInfo[1],
                'سيتم تأخير طلبك رقم '. $order->order_number . ' بسبب ' . $complaint->message,
                'Your delivery order '.$order->order_number.' is delayed due to ' . $complaint->message,
                'تأخير استلام طلب',
                'Delivery order delay',
                'client',
                $clientInfo[0],
                $this->user->id,
                $complaint->id,
                $this->lang,
                null
            );
        }

        return $responses;
    }

    /**
     * Get customer service info for an order
     */
    protected function getCustomerServiceInfo(string $orderId)
    {
        $order = Order::find($orderId);
        $customerService = $order ? Employee::find($order->customer_service_id) : null;
        return $customerService ? [$customerService->id, $customerService->device_token] : null;
    }

    /**
     * Get client info for an order
     */
    protected function getClientInfo(string $orderId)
    {
        $order = Order::find($orderId);
        $client = $order ? User::find($order->client_id) : null;
        return $client ? [$client->id, $client->fcm_token] : null;
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationSendController extends Controller
{


    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $user = auth('employee')->user();

        $notifications = Notification::with('category')->where('type', $user->flag)->where('user_id', $user->id)->get();
        if ($notifications->isEmpty()) {
            return ResponseWithSuccessData($lang, null, 1);
        }

        if ($user->flag == 'purchase') {
            $formatted = $notifications->groupBy('notify_type')->map(function ($group, $type) use ($lang) {

                // Name of group based on language
                $category = $group->first()->category;

                $type_name = $category
                    ? ($lang === 'ar' ? $category->name_ar : $category->name_en)
                    : null;
                // Format each notification inside this type
                $formatted_items = $group->map(function ($notification) use ($lang) {
                    $dateTime = Carbon::parse($notification->date_time)->locale($lang);

                    return [
                        'id'             => $notification->id,
                        'title'          => $lang === 'ar' ? $notification->title_ar : $notification->title_en,
                        'description'    => $lang === 'ar' ? $notification->description_ar : $notification->description_en,
                        'is_read'        => (bool) $notification->status,
                        'date'           => $dateTime->translatedFormat('Y-m-d'),
                        'day'            => $dateTime->translatedFormat('l'),
                        'time'           => $dateTime->translatedFormat('h:i A'),
                        'date_in_words'  => $dateTime->translatedFormat('j F Y'),
                    ];
                });

                return [
                    'notify_type'      => $type,
                    'notify_type_name' => $type_name,
                    'notifications'    => $formatted_items,
                ];
            })->values();
        } else {
            // Format each notification
            $formatted = $notifications->map(function ($notification) use ($lang) {
                $dateTime = Carbon::parse($notification->date_time)->locale($lang);

                return [
                    'id'             => $notification->id,
                    'title'          => $lang === 'ar' ? $notification->title_ar : $notification->title_en,
                    'description'    => $lang === 'ar' ? $notification->description_ar : $notification->description_en,
                    'is_read'        => (bool) $notification->status,
                    'date'           => $dateTime->translatedFormat('Y-m-d'),
                    'day'            => $dateTime->translatedFormat('l'),
                    'time'           => $dateTime->translatedFormat('h:i A'),
                    'date_in_words'  => $dateTime->translatedFormat('j F Y'), // e.g. 24 June 2025 or ٢٤ يونيو ٢٠٢٥
                ];
            });
        }


        return ResponseWithSuccessData($lang, $formatted, 1);
    }

    public function read(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|numeric|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        // Get notification
        $notification = Notification::find($request->notification_id);

        if (!$notification) {
            return RespondWithBadRequest($lang, 2);
        }
        $notification->status = 1;
        $notification->save();

        $dateTime = Carbon::parse($notification->date_time)->locale($lang);

        $formatted = [
            'id'             => $notification->id,
            'title'          => $lang === 'ar' ? $notification->title_ar : $notification->title_en,
            'description'    => $lang === 'ar' ? $notification->description_ar : $notification->description_en,
            'is_read'        => (bool) $notification->status,
            'date'           => $dateTime->translatedFormat('Y-m-d'),
            'day'            => $dateTime->translatedFormat('l'),
            'time'           => $dateTime->translatedFormat('h:i A'),
            'date_in_words'  => $dateTime->translatedFormat('j F Y'), // e.g. 24 June 2025 or ٢٤ يونيو ٢٠٢٥
        ];
        return ResponseWithSuccessData($lang, $formatted, 1);
    }

    public function setToken(Request $request)
    {
        $token = $request->input('fcm_token');

        $user = User::where('id', $request->user_id)->first();
        $user->fcm_token = $token;
        $user->save();
        return response()->json([
            'message' => 'Successfully Updated FCM Token'
        ]);
    }

    public function send(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $current_user = auth('employee')->user();

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|numeric|exists:employees,id',
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'body_ar' => 'required|string',
            'body_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        // Get employee
        $employee = Employee::find($request->employee_id);

        if ($employee->device_token != null) {
            $notification = send_push_notification(
                $employee->device_token, // Device token
                $request->body_ar, // Arabic body
                $request->body_en, // English body
                $request->title_ar, // Arabic title
                $request->title_en, // English title
                'hr', // notification_type
                $employee->id, // receiver_id
                $current_user->id, // created_by (sender)
                $employee->id,
                $lang,
                11
            );

            return ResponseWithSuccessData($lang, $notification, 1);
        }
        return respondErrorData(
            $lang === 'en' ? 'Notification failed.' : 'فشل ارسال اشعار.',
            400,
            [$lang === 'en' ? "Employee can't receive notification." : 'الموظف لا يمكنه استقبال اشعار.']
        );
    }
}

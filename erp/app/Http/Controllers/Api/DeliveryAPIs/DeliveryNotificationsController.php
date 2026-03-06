<?php

namespace App\Http\Controllers\Api\DeliveryAPIs;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class DeliveryNotificationsController extends Controller
{
    public function getNotifications(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        Carbon::setLocale($lang);

        $user = auth('employee')->user();

        $notificationsQuery = Notification::with('category');
        $notificationsQuery->where('user_id', $user->id)->where('status', 0);

        if ($user->flag == "Head Chef") {
            $flag = "head_chef";
            $notificationsQuery->where('type', 'like', $flag);
        } else {
            $notificationsQuery->where('type', $user->flag);
        }

        $notificationsQuery->whereDate('created_at', '>=', Carbon::now()->subDays(2))
            ->orderBy('created_at', 'desc');

        $notifications = $notificationsQuery->get();
        $groupedNotifications = [];

        if ($notifications->count() > 0) {
            // Group notifications by date (already in Y-m-d format)
            $grouped = $notifications->groupBy(function ($item) {
                return $item->date_time->format('Y-m-d');
            });

            foreach ($grouped as $date => $dayNotifications) {
                // Prepare the notifications array for this date
                $notificationsArray = [];
                foreach ($dayNotifications as $notification) {
                    $notificationsArray[] = [
                        'id' => $notification->id,
                        'notification_type' => $notification->category->name_en, 
                        'statusId' => $notification->product_id,
                        'title' => $notification->title,
                        'description' => $notification->description,
                        'is_read' => boolval($notification->status),
                        'date_time' => $notification->date_time->format('Y-m-d H:i:s'),
                    ];
                }

                $groupedNotifications[] = [
                    'date' => $date, // Already in Y-m-d format from the grouping
                    'notifications' => $notificationsArray
                ];
            }
        }

        return ResponseWithSuccessData($lang, $groupedNotifications, 1);
    }

    public function markNotificationAsRead(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        Carbon::setLocale($lang);

        $user = auth('employee')->user();

        $notificationQuery = Notification::where('id', $id)
            ->where('user_id', $user->id);
        // ->where('type', $user->flag)
        //         ->first();
        // Add the same flag check logic as in getNotifications()
        if ($user->flag == "Head Chef") {
            $flag = "head_chef";
            $notificationQuery->where('type', 'like', $flag);
        } else {
            $notificationQuery->where('type', $user->flag);
        }

        $notification = $notificationQuery->first();




        if (!$notification) {
            return respondError('error', 400, ['error' => __('order.notification_not_found')]);
        }

        $notification->status = 1;
        $notification->save();

        $result = [
            'id' => $notification->id,
            'notification_type' => $notification->notify_type,
            'statusId' => $notification->product_id,
            'title' => $notification->title,
            'description' => $notification->description,
            'is_read' => boolval($notification->status),
            'date_time' => $notification->date_time->format('Y-m-d H:i:s'),
        ];

        return ResponseWithSuccessData($lang, $result, 1);
    }
}

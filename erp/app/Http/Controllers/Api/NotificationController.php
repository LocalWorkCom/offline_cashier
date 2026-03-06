<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided

        $notifications = Notification::all();
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        // Define columns that need translation
        $translateColumns = ['title', 'description']; // Add other columns as needed

        // Define columns to remove (translated columns)
        $columnsToRemove = array_map(function($col) {
            return [$col . '_ar', $col . '_en'];
        }, $translateColumns);
        $columnsToRemove = array_merge(...$columnsToRemove);

        // Map categories to include translated columns and remove unnecessary columns
        $notifications = $notifications->map(function ($category) use ($lang, $translateColumns, $columnsToRemove) {
            // Convert category model to an array
            $data = $category->toArray();

            // Get translated data
            $data = translateDataColumns($data, $lang, $translateColumns);

            // Remove translated columns from data
            $data = removeColumns($data, $columnsToRemove);

            return $data;
        });

        return ResponseWithSuccessData($lang, $notifications, code: 1);
    }

    public function markAsRead($id, $user_id)
    {
        $notification = Notification::find($id);
        if ($notification && $notification->user_id == $user_id) {
            $notification->update(['status' => 1]);
            $notification->save();
        }
        return redirect($notification->url);
    }
}

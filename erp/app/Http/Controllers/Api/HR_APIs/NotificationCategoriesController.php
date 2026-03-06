<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvanceResource;
use App\Models\Advance;
use App\Services\HR_Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationCategoriesController extends Controller
{
    private $NotificationService;

    public function __construct(NotificationService $NotificationService)
    {
        $this->NotificationService = $NotificationService;
    }


    public function index(Request $request)
    {
        $lang = $request->header('lang') ?? 'en';
        $types = $this->NotificationService->index();

        $response = paginateOrGetAll($types, $request);

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $type = $this->NotificationService->show($id);

            return ResponseWithSuccessData($lang, $type, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'فئات الأشعارات غير موجودة' : 'notification category not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching notification category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'is_active' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $validator->validated();

            $notification = $this->NotificationService->store($data);

            return ResponseWithSuccessData($lang, $notification, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'فئة الإشعار غير موجودة' : 'notification category not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching notification category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            if (in_array((int)$id, range(1, 11), true)) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    ['error' => $lang == 'en' ? 'Cannot update this notification category.' : 'لا يمكن تحديث فئة الإشعار هذه.']
                );
            }
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'is_active' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $validator->validated();
            $notificationCategory = $this->NotificationService->update($data, $id);

            return ResponseWithSuccessData($lang, $notificationCategory, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'فئات الإشعارات غير موجودة' : 'notification category not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching notification category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}

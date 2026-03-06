<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\HR_Services\PositionService;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $positionService;
    protected $hidden = ['name', 'name_site'];
    protected $visible;

    public function __construct(PositionService $positionService)
    {
        $this->positionService = $positionService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {

            $data = $this->positionService->getAllPositions();
            $response = paginateOrGetAll($data, $request, $this->hidden, $this->visible);
            $meta = $response['meta'];
            $data = $response['data'];
            $responseData = [];
            foreach ($data as $position) {
                $responseData[] = [
                    'id' => $position->id,
                    'name' => $position->name,
                    'name_ar' => $position->name_ar,
                    'name_en' => $position->name_en,
                    'description' => $position->description,
                    'description_ar' => $position->description_ar,
                    'description_en' => $position->description_en,
                    'department_id' => $position->department ? $position->department_id : null,
                    'department_name' => $position->department ? $position->department->name : null,
                    'parent_id' => $position->parent ? $position->parent_id : null,
                    'parent_name' => $position->parent ? $position->parent->name : null,
                    'application_path' => $position->application_path,
                    'application_link' => $position->application_link,
                    'parent' => $position->parent ? $position->parent : null,
                    'employees_count' => $position->employees_count,
                    'employees' => $position->employees,
                ];
                // if ($position->parent) {
                //     $position->parent->makeHidden(['name', 'name_site']);
                // }
            }
            $response = ['data' => $responseData, 'meta' => $meta];

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {

            $data = $this->positionService->getPosition($id);
            return ResponseWithSuccessData($lang, $data->makeHidden($this->hidden), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوظيفه  غير موجودة' : 'Position not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating position: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        try {
            $validator = Validator::make($request->all(), [
                'department_id' => 'required|integer|exists:departments,id',
                'parent_id'     => 'nullable|integer|exists:positions,id',
                'name_ar'       => 'required|string',
                'name_en'       => 'required|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
            ]);


            if ($validator->fails()) {
                return respondError(
                    __('Validation.error'),
                    400,
                    $validator->errors()
                );
            }

            $validatedDataArray = $validator->validated();

            $position = $this->positionService->createPosition($validatedDataArray);

            return ResponseWithSuccessData($lang, $position->makeHidden($this->hidden), 1);
        } catch (\Exception $e) {
            Log::error('Error creating position: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {


            $validator = Validator::make($request->all(), [
                'department_id' => 'required|integer|exists:departments,id',
                'parent_id' => 'nullable|integer|exists:positions,id',
                'name_ar' => 'required|string',
                'name_en' => 'required|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return respondError(
                    __('Validation.error'),
                    400,
                    $validator->errors()
                );
            }
            $position = $this->positionService->updatePosition($request->all(), $id);
            return ResponseWithSuccessData($lang, $position->makeHidden($this->hidden), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوظيفه  غير موجودة' : 'Position not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating position: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $this->positionService->deletePosition($id);
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوظيفه  غير موجودة' : 'Position not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error deleting position: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}

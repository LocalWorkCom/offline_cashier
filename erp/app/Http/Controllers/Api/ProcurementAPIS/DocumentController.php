<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use App\Models\PaymentMethod;
use App\Services\ProcurementServices\DocumntService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentSequenceResource;
use App\Models\DocumentSequence;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    protected $documntService;

    public function __construct(DocumntService $documntService)
    {
        $this->documntService = $documntService;
    }

    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
        return $employee;
    }
    public function listDocumntType(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to get the query builder
            $list = $this->documntService->listDocumnt($request);
            $list = $list->map(function ($doc) {
                return [
                    'id'   => $doc->id,
                    'code' => $doc->code,
                    'name' => $doc->name,
                ];
            });
            return ResponseWithSuccessData($lang, $list, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to get the query builder
            $query = $this->documntService->index($request);

            $list = paginateOrGetAll($query, $request, null, null);

            $result = DocumentSequenceResource::collection($list['data'])->resolve();

            return ResponseWithSuccessDataPaginated($lang, ['data' => $result, 'meta' => $list['meta']], 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $paymentMethodExists = DocumentSequence::where('id', $id)->exists();

            if (!$paymentMethodExists) {
                return respondError($lang === 'ar' ? 'غير موجود' : ' Document Sequence not found', 404);
            }

            // Use service to get the base query
            $result = $this->documntService->show($request, $id);

            return ResponseWithSuccessData($lang, new DocumentSequenceResource($result), 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');
            $validator = Validator::make($request->all(), [
                'prefix' => 'nullable|string',
                'suffix' => 'nullable|string',
                'type' => 'required|exists:document_name_types,id|unique:DocumentSequence,type',
                'numbering_style' => 'nullable|in:sequential,alphanumeric,numerical',
                'format' => 'nullable|string',
                'include_year' => 'nullable|boolean',
                'year_format' => 'required_if:include_year,Y,Y',
                'include_branch' => 'nullable|boolean',
                'branch_id' => 'required_if:include_branch,1|exists:branches,id',
                'logo_location' => 'nullable|string',
                'header_text_en' => 'nullable|string',
                'header_text_ar' => 'nullable|string',
                'footer_text_en' => 'nullable|string',
                'footer_text_ar' => 'nullable|string',
                'font_type' => 'nullable|string',
                'font_size' => 'nullable|integer',
                'compliance_text_ar' => 'nullable|string',
                'compliance_text_en' => 'nullable|string',
                'signeter_count' => 'nullable|integer',
                'signeter' => 'nullable|array',
                'signeter.*.name_en' => 'required|string',
                'signeter.*.name_ar' => 'required|string',
            ]);
            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
            }

            // Use service to handle creation
            $result = $this->documntService->store($request);

            return ResponseWithSuccessData($lang, new DocumentSequenceResource($result), 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        // try {
        $this->getAuthenticatedEmployee();
        $lang = $request->header('lang', 'en');
        $docSequence = DocumentSequence::findOrFail($id);
        if (!$docSequence) {
            return respondError($lang === 'ar' ? 'غير موجود' : 'Not found', 404);
        }
        $validator = Validator::make($request->all(), [
            'prefix' => 'nullable|string',
            'suffix' => 'nullable|string',
            'type' => 'required|exists:document_name_types,id',
            'numbering_style' => 'nullable|in:sequential,alphanumeric,numerical',
            'format' => 'nullable|string',
            'include_year' => 'nullable|boolean',
            'year_format' => 'required_if:include_year,Y,Y',
            'include_branch' => 'nullable|boolean',
            'branch_id' => 'required_if:include_branch,1|exists:branches,id',
            'logo_location' => 'nullable|string',
            'header_text_en' => 'nullable|string',
            'header_text_ar' => 'nullable|string',
            'footer_text_en' => 'nullable|string',
            'footer_text_ar' => 'nullable|string',
            'font_type' => 'nullable|string',
            'font_size' => 'nullable|integer',
            'compliance_text_ar' => 'nullable|string',
            'compliance_text_en' => 'nullable|string',
            'signeter_count' => 'nullable|integer',
            'signeter' => 'nullable|array',
            'signeter.*.name_en' => 'required|string',
            'signeter.*.name_ar' => 'required|string',
        ]);

        if ($validator->fails()) {
            return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
        }

        // Fetch existing document sequence


        $result = $this->documntService->update($request, $id);

        // Return resource
        return ResponseWithSuccessData($lang, new DocumentSequenceResource($result), 1);
        // }catch (\Exception $e) {
        //     return response()->json([
        //         'message' => $lang === 'ar' ? 'حدث خطأ أثناء التحديث' : 'An error occurred while updating',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }
}

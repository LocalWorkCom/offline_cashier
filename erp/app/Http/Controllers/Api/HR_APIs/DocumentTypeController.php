<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\DocumentTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentTypeController extends Controller
{
    protected $documentTypeService;

    public function __construct(DocumentTypeService $documentTypeService)
    {
        $this->documentTypeService = $documentTypeService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $documentTypes = $this->documentTypeService->getAll();
        $documentTypes = paginateOrGetAll($documentTypes, $request, null, null);
        return ResponseWithSuccessDataPaginated($lang, $documentTypes, 1);
        // return ResponseWithSuccessData($lang, $documentTypes, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');


        $rules = [
            'name_ar' => 'required|string|max:255|unique:document_types,name_ar',
            'name_en' => 'required|string|max:255|unique:document_types,name_en',
        ];
        $messages = [
            'name_ar.required' => __('document_types.custom.name_ar.required'),
            'name_ar.string'   => __('document_types.custom.name_ar.string'),
            'name_ar.max'      => __('document_types.custom.name_ar.max'),
            'name_ar.unique'   => __('document_types.custom.name_ar.unique'),

            'name_en.required' => __('document_types.custom.name_en.required'),
            'name_en.string'   => __('document_types.custom.name_en.string'),
            'name_en.max'      => __('document_types.custom.name_en.max'),
            'name_en.unique'   => __('document_types.custom.name_en.unique'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        $documentType = $this->documentTypeService->create($request->all());
        return ResponseWithSuccessData($lang, $documentType, 1);
    }
}

<?php

namespace App\Http\Controllers\Api\HR_APIs;


use App\Http\Controllers\Controller;
use App\Services\HR_Services\EmployeeDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeDocumentController extends Controller
{
    protected $employeeDocumentService;

    public function __construct(EmployeeDocumentService $employeeDocumentService)
    {
        $this->employeeDocumentService = $employeeDocumentService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $documents = $this->employeeDocumentService->getAll($request->all());
        $documents = paginateOrGetAll($documents, $request, null, null);
        return ResponseWithSuccessDataPaginated($lang, $documents, 1);
        // return ResponseWithSuccessData($lang, $documents, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $rules = [
            'document_type_id' => 'required|exists:document_types,id',
            'employee_id'      => 'required|exists:employees,id',
            'date'             => 'nullable|date',
            'file'             => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ];

        $messages = [
            'document_type_id.required' => __('employee_document.custom.document_type_id.required'),
            'document_type_id.exists'   => __('employee_document.custom.document_type_id.exists'),
            'employee_id.required'      => __('employee_document.custom.employee_id.required'),
            'employee_id.exists'        => __('employee_document.custom.employee_id.exists'),
            'file.required'             => __('employee_document.custom.file.required'),
            'file.file'                 => __('employee_document.custom.file.file'),
            'file.mimes'                => __('employee_document.custom.file.mimes'),
            'file.max'                  => __('employee_document.custom.file.max'),
        ];


        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        // Handle file upload if present
        $data = $request->all();

        $document = $this->employeeDocumentService->create($data);
         if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            UploadFile('files/employees/documents', 'file', $document, $file);
        }
        return ResponseWithSuccessData($lang, $document, 1);
    }
}

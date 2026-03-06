<?php

namespace App\Services\ProcurementServices;

use App\Models\DocumentFormat;
use App\Models\DocumentFormatSigneter;
use App\Models\DocumentNameType;
use App\Models\DocumentSequence;
use App\Models\PaymentMethod;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DocumntService
{
    public function listDocumnt(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $list = DocumentNameType::where('active', 1)->get();
        return $list;
    }
    public function index(Request $request)
    {
        $query = DocumentSequence::with(['formats.signeters'])
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at');
            
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        return  $query;
    }

    public function show(Request $request, $id)
    {
        // Use the model, not the query builder directly
        return DocumentSequence::with(['formats.signeters'])->find($id);
    }
    public function store(Request $request)
    {
        // $documnt_type = DocumentNameType::find($request->type);
        $docSequence = DocumentSequence::create([
            'prefix' => $request->prefix,
            'suffix' => $request->suffix,
            'document_type_id' => $request->type,
            'numbering_style' => $request->numbering_style,
            'format' => $request->format,
            'include_year' => $request->include_year,
            'year_format' => $request->year_format,
            'include_branch' => $request->include_branch,
            'branch_id' => $request->branch_id,
            'active' => 1,
            'created_by' => authActionSave()['by'],
        ]);
        if ($docSequence) {
            $docFormat = DocumentFormat::create([
                'document_sequnce_id' => $docSequence->id,
                'logo_location' => $request->logo_location,
                'header_text_en' => $request->header_text_en,
                'header_text_ar' => $request->header_text_ar,
                'footer_text_en' => $request->footer_text_en,
                'footer_text_ar' => $request->footer_text_ar,
                'font_type' => $request->font_type,
                'font_size' => $request->font_size,
                'compliance_text_ar' => $request->compliance_text_ar,
                'compliance_text_en' => $request->compliance_text_en,
                'signeter_count' => $request->signeter_count,
                'active' => 1,
                'created_by' => authActionSave()['by'],
            ]);
            if ($docFormat) {
                if (!empty($request->signeter)) {
                    foreach ($request->signeter as $position => $signeter) {
                        DocumentFormatSigneter::create([
                            'document_format_id' => $docFormat->id,
                            'name_en' => $signeter['name_en'],
                            'name_ar' => $signeter['name_ar'],
                            'position' => $position + 1,
                        ]);
                    }
                }
            }
        }


        return $docSequence;
    }

    public function update(Request $request, $id)
    {
        $docSequence = DocumentSequence::findOrFail($id);

        // Update main sequence
        $docSequence->update([
            'prefix' => $request->prefix,
            'suffix' => $request->suffix,
            'document_type_id' => $request->type,
            'numbering_style' => $request->numbering_style,
            'format' => $request->format,
            'include_year' => $request->include_year,
            'year_format' => $request->year_format,
            'include_branch' => $request->include_branch,
            'branch_id' => $request->branch_id,
            'active' => $request->active ?? 1,
            'updated_by' => authActionSave()['by'],
        ]);

        $docFormat = DocumentFormat::firstOrCreate(
            ['document_sequnce_id' => $docSequence->id]
        );

        $docFormat->update([
            'logo_location' => $request->logo_location,
            'header_text_en' => $request->header_text_en,
            'header_text_ar' => $request->header_text_ar,
            'footer_text_en' => $request->footer_text_en,
            'footer_text_ar' => $request->footer_text_ar,
            'font_type' => $request->font_type,
            'font_size' => $request->font_size,
            'compliance_text_ar' => $request->compliance_text_ar,
            'compliance_text_en' => $request->compliance_text_en,
            'signeter_count' => $request->signeter_count,
            'updated_by' => authActionSave()['by'],
        ]);

        // Handle signeters if present
        if ($request->filled('signeter') && is_array($request->signeter)) {
            $docFormat->signeters()->delete();

            foreach ($request->signeter as $position => $signeter) {
                DocumentFormatSigneter::create([
                    'document_format_id' => $docFormat->id,
                    'name_en' => $signeter['name_en'],
                    'name_ar' => $signeter['name_ar'],
                    'position' => $position + 1,
                ]);
            }
        }
        return $docSequence->load('formats.signeters');
    }

    public function delete($id)
    {
        $lang = app()->getLocale();

        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return respondError(
                $lang == 'en'
                    ? 'Payment method not found'
                    : 'طريقة الدفع غير موجودة',
                404
            );
        }

        try {
            // if ($paymentMethod->vendors()->exists()) {
            //     // Deactivate instead of delete
            //     $paymentMethod->update([
            //         'status' => 0,
            //         'modified_by' => authActionSave()['by'],
            //         'modified_by_type' => authActionSave()['type']
            //     ]);

            //     return respondError(
            //         $lang == 'en'
            //             ? 'This payment method is now inactive. Existing vendors remain linked, but it cannot be used for new assignments.'
            //             : 'هذه الطريقة غير نشطة الآن. الموردون الحاليون يظلون مرتبطين.',
            //         400
            //     );
            // }

            // Delete normally
            $paymentMethod->deleted_by = authActionSave()['by'];
            $paymentMethod->deleted_by_type = authActionSave()['type'];
            $paymentMethod->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en'
                    ? 'Something went wrong'
                    : 'حدث خطأ ما',
                500
            );
        }
    }
}

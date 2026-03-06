<?php

namespace App\Services\ClientServices;

use App\Models\Complaint;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RateService
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('client')->user();

            if ((!$user)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "order_id" => "required|exists:orders,id,deleted_at,NULL",
                "rate" => "required|in:1,2,3,4,5",
                "complain" => "nullable|string",
                "comment" => "nullable|string",
            ], [
                'rate.required' => $lang == 'en' ? 'Rating is required' : 'التقييم مطلوب',
                'rate.in' => $lang == 'en' ? 'Invalid rating value' : 'قيمة التقييم غير صالحة'
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
//            dd(Employee::find(auth('employee')->user()->id)->user_id);
            $complaint = Complaint::create($validator->validated()
                + [
                    'client_id' => auth('client')->user()->id ?? null,
                    'created_by' => auth('client')->user()->id ?? null,
                ]
            );

            return ResponseWithSuccessData($lang,$complaint,1);
        }
        catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
    }
}

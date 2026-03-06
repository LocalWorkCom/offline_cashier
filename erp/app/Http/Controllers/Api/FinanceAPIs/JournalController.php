<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateJournalRequest;
use App\Http\Requests\Finance\UpdateJournalRequest;
use App\Http\Resources\Finance\JournalResource;
use App\Services\FinanceServices\JournalService;
use App\Models\Journal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{

    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $validator = Validator::make(request()->all(), [
        //     'facility_id' => 'required|integer|exists:facilities,id',
        // ], [
        //     'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        // ]);

        // if ($validator->fails()) {
        //     return respondError(
        //         $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
        //         400,
        //         $validator->errors()->all()
        //     );
        // }

        $data = $this->journalService->getAll($request);
        $response = paginateOrGetAll($data, $request, [], []);

        return new JournalResource([
            'data' => $response['data'],
            'meta' => $response['meta'],
            'status' => true,
            'message' => ApiCode(1)->{'api_code_message_' . ($lang == 'ar' ? 'ar' : 'en')},
            'code' => 200,
        ]);
    }

    public function store(CreateJournalRequest $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $journal = $this->journalService->add($request);
            return ResponseWithSuccessData($lang, new JournalResource($journal), 1);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
                400,
                $lang == 'en'
                    ? ['An error occurred during the addition process. Please try again.']
                    : ['حصل خطأ أثناء عملية الإضافة من فضلك حاول مرة أخرى']
            );
        }
    }

    public function show($id)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        return $data = $this->journalService->show(request());
    }

    public function list()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $validator = Validator::make(request()->all(), [
        //     'facility_id' => 'required|integer|exists:facilities,id',
        // ], [
        //     'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        // ]);

        // if ($validator->fails()) {
        //     return respondError(
        //         $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
        //         400,
        //         $validator->errors()->all()
        //     );
        // }

        return $data = $this->journalService->list(request());
    }

    public function showList()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $validator = Validator::make(request()->all(), [
        //     'facility_id' => 'required|integer|exists:facilities,id',
        // ], [
        //     'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        // ]);

        // if ($validator->fails()) {
        //     return respondError(
        //         $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
        //         400,
        //         $validator->errors()->all()
        //     );
        // }

        return $data = $this->journalService->showList(request());
    }

    public function select_list()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make(request()->all(), [
            // 'facility_id' => 'required|integer|exists:facilities,id',
            'currency_id' => 'required|integer|exists:currencies,id',
        ], [
            // 'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
            // 'facility_id.exists'   => $lang == 'en' ? 'Facility does not exist' : 'المنشأة غير موجودة',
            'currency_id.required' => $lang == 'en' ? 'Currency is required' : 'العملة مطلوبة',
            'currency_id.exists'   => $lang == 'en' ? 'Currency does not exist' : 'العملة غير موجودة',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
                400,
                $validator->errors()->all()
            );
        }

        request()->merge(['type' => 'no_child']);
        return $data = $this->journalService->select_list(request());
    }

    public function update(UpdateJournalRequest $request, $id)
    {
        $lang = $request->header('lang', 'en');
        // try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            return $journal = $this->journalService->edit($request);
        // } catch (\Exception $e) {
        //     return respondError(
        //         $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
        //         400,
        //         $lang == 'en'
        //             ? ['An error occurred during the addition process. Please try again.']
        //             : ['حصل خطأ أثناء عملية الإضافة من فضلك حاول مرة أخرى']
        //     );
        // }
    }

    public function archive(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            return $journal = $this->journalService->archive($request);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
                400,
                $lang == 'en'
                    ? ['An error occurred during the addition process. Please try again.']
                    : ['حصل خطأ أثناء عملية الإضافة من فضلك حاول مرة أخرى']
            );
        }
    }

    public function destroy($id)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $data = $this->journalService->delete(request());
    }

    public function account_statement(Request $request, $id)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $validator = Validator::make(request()->all(), [
        //     'facility_id' => 'required|integer|exists:facilities,id',
        // ], [
        //     'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        // ]);

        // if ($validator->fails()) {
        //     return respondError(
        //         $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
        //         400,
        //         $validator->errors()->all()
        //     );
        // }

        $data = $this->journalService->account_statement(request());
        return ResponseWithSuccessData(request()->header('lang', 'ar'), $data, 1);
    }

    public function chart_account(Request $request, $id)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $validator = Validator::make(request()->all(), [
        //     'facility_id' => 'required|integer|exists:facilities,id',
        // ], [
        //     'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        // ]);

        // if ($validator->fails()) {
        //     return respondError(
        //         $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
        //         400,
        //         $validator->errors()->all()
        //     );
        // }

        $data = $this->journalService->chartAccount(request());
        return ResponseWithSuccessData(request()->header('lang', 'ar'), $data, 1);
    }

    public function trialBalance(Request $request)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $validator = Validator::make(request()->all(), [
        //     'facility_id' => 'required|integer|exists:facilities,id',
        // ], [
        //     'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        // ]);

        // if ($validator->fails()) {
        //     return respondError(
        //         $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
        //         400,
        //         $validator->errors()->all()
        //     );
        // }
        $data = $this->journalService->trialBalance(request());
        return ResponseWithSuccessData(request()->header('lang', 'ar'), $data, 1);
    }

    public function templateExportJournals(Request $request)
    {
        return $data = $this->journalService->exportJournals(request());
        // return response()->download($filePath, 'import-journal-tempelate.xlsx');
    }

    public function importJournals(Request $request)
    {
        return $data = $this->journalService->importJournals(request());
        return ResponseWithSuccessData(request()->header('lang', 'ar'), $data, 1);
    }

}

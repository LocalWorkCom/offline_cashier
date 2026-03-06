<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateJournalEntryRequest;
use App\Http\Requests\Finance\UpdateJournalEntryRequest;
use App\Http\Resources\Finance\JournalEntryResource;
use App\Services\FinanceServices\JournalEntryService;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetails;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class JournalEntryController extends Controller
{

    protected $journalEntryService;

    public function __construct(JournalEntryService $journalEntryService)
    {
        $this->journalEntryService = $journalEntryService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $data = $this->journalEntryService->getAll($request);
        $response = paginateOrGetAll($data, $request, [], []);

        return new JournalEntryResource([
            'data' => $response['data'],
            'meta' => $response['meta'],
            'status' => true,
            'message' => ApiCode(1)->{'api_code_message_' . ($lang == 'ar' ? 'ar' : 'en')},
            'code' => 200,
        ]);
    }

    public function store(CreateJournalEntryRequest $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $journal = $this->journalEntryService->add($request);
            return ResponseWithSuccessData($lang, new JournalEntryResource($journal), 1);
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

        return $data = $this->journalEntryService->show(request());
    }

    public function list()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        if (!request()->facility_id) {
            return respondError(
                $lang == 'en' ? 'facility is required' : 'المنشاة مطلوبة',
                400,
                $lang == 'en'
                    ? ['facility is required.']
                    : ['المنشاة مطلوبة']
            );
        }
        return $data = $this->journalEntryService->list(request());
    }

    public function update(UpdateJournalEntryRequest $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            return $journal = $this->journalEntryService->edit($request);
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

    public function archive(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            return $journal = $this->journalEntryService->archive($request);
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
        return $data = $this->journalEntryService->delete(request());
    }
}

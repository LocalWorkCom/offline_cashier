<?php

namespace App\Services\FinanceServices;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\Finance\FacilityResource;
use App\Models\EmployeeFacility;
use Illuminate\Support\Facades\Storage;
use App\Models\Journal;

use Google\Service\Datastream\Merge;

class FacilityService
{
    public function getAll(Request $request)
    {
        $facilities = Facility::query();
        return $facilities;
    }

    public function show(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = Facility::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            return ResponseWithSuccessData(request()->header('lang', 'ar'), new FacilityResource($data), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $employee = Auth::guard('employee')->user();

            $result = collect($request->validated())->except('image')->toArray();
            $facility = Facility::create(array_merge(
                $result,
                [
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]
            ));

            if (isset($request->logo)) {
                UploadFile('images/facilities', 'logo', $facility, $request->logo);
            }

            $data = EmployeeFacility::Create(
            [
                'employee_id' => $employee->id,
                'facility_id' => $facility->id,
                'is_active'       => 1,
                'created_by'      => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ]
        );

            $add_journal = addJournalToFacility($facility);
            $add_currency_exchange = addCurrencyExchangeToFacility($facility);

            return $facility;
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function edit(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $facility = Facility::find($request->id);
            if (!$facility) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            $result = collect($request->validated())->except('logo','_method')->toArray();
            $result = array_merge($result, [
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ]);

            $facility->update($result);

            if (isset($request->logo)) {
                if ($facility->logo && Storage::exists($facility->logo)) {
                    Storage::delete($facility->logo);
                }
                UploadFile('images/facilities', 'logo', $facility, $request->logo);
            }

            return ResponseWithSuccessData($lang, new FacilityResource($facility), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }


    public function delete(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = Facility::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            if(count($data->costCenter) > 0){
                return respondError(($lang == 'en'? 'Has linked cost centers': 'لديه مراكز تكلفة مرتبطة'), 404, $lang == 'en'? 'This item has linked cost centers. Delete them first before deleting this item.': 'هذا العنصر لديه مراكز تكلفة مرتبطة به .. احذفها أولاً ثم قم بالحذف');
            }

            if(count($data->journalEntry) > 0){
                return respondError(($lang == 'en'? 'Has linked journal enter': 'لديه قيود مرتبطة'), 404, $lang == 'en'? 'This item has linked journal enter. Delete them first before deleting this item.': 'هذا العنصر لديه قيود مرتبطة به .. احذفها أولاً ثم قم بالحذف');
            }

            $data->deleted_by = authActionSave()['by'];
            $data->deleted_by_type = authActionSave()['type'];
            $data->save();
            $data->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }
}

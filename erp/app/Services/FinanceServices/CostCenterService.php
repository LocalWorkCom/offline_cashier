<?php

namespace App\Services\FinanceServices;

use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\Finance\CostCenterResource;
use App\Models\Branch;
use App\Models\CompanyProfileSetting;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;

class CostCenterService
{
    public function getAll(Request $request)
    {
        $employee = auth('employee')->user();        
        $facility_id = $employee->employeeFacility->facility_id;
 
        $costCenters = CostCenter::where('facility_id', $facility_id)->with('facility', 'company', 'branch')->where('parent_id', null);
        return $costCenters;
    }

    public function show(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = CostCenter::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            return ResponseWithSuccessData(request()->header('lang', 'ar'), new CostCenterResource($data), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $employee = auth('employee')->user();
            $facility_id = $employee->employeeFacility->facility_id;

            $name = $request->name_ar;
            if($request->branch_id != null){
                $name = Branch::where('id', $request->branch_id)->value('name_ar');
            }elseif($request->company_id != null){
                $name = CompanyProfileSetting::where('id', $request->company_id)->value('name_ar');
            }

            $result = collect($request->validated())->except('name_ar', 'facility_id')->toArray();
            $costCenter = CostCenter::create(array_merge(
                $result,
                [
                    'name_ar' => $name,
                    'facility_id' => $facility_id,
                    'active' => 1,
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]
            ));

            return $costCenter;
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function edit(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $costCenter = CostCenter::find($request->id);
            if (!$costCenter) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            // $result = collect($request->validated())->except('_method')->toArray();
            // $result = array_merge($result, [
            //     'modified_by'      => authActionSave()['by'],
            //     'modified_by_type' => authActionSave()['type'],
            // ]);

            if($request->name_ar != null){
                $name = $request->name_ar;
                $branch_id = null;
                $company_id = null;
            }
            elseif($request->branch_id != null){
                $name = Branch::where('id', $request->branch_id)->value('name_ar');
                $company_id = null;
                $branch_id = $request->branch_id;
            }elseif($request->company_id != null){
                $name = CompanyProfileSetting::where('id', $request->company_id)->value('name_ar');
                $branch_id = null;
                $company_id = $request->company_id;
            }
            // $result = collect($request->validated())->toArray();
            $result = collect($request->validated())->except('_method')->toArray();
            $result = [
                'name_ar'          => $name,
                'company_id'       => $company_id,
                'branch_id'        => $branch_id,
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ];

            $costCenter->update($result);

            return ResponseWithSuccessData($lang, new CostCenterResource($costCenter), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }


    public function delete(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = CostCenter::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            if(count($data->child) > 0){
                return respondError(($lang == 'en'? 'Has linked cost centers': 'لديه مراكز تكلفة مرتبطة'), 404, $lang == 'en'? 'This item has linked cost centers. Delete them first before deleting this item.': 'هذا العنصر لديه مراكز تكلفة مرتبطة به .. احذفها أولاً ثم قم بالحذف');
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

    public function comparison(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = [];
            foreach ($request->cost_centers as $detail) {
                $cost_center = CostCenter::where('id',$detail)->with(['journalEntryDetails', 'child.journalEntryDetails'])->first();
                if (!$cost_center) {
                    return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
                }
                $data[] = $cost_center;
            }

            // return $data;
            return ResponseWithSuccessData(request()->header('lang', 'ar'), $data, 1);
            // return ResponseWithSuccessData(request()->header('lang', 'ar'), new CostCenterResource($data), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function archive(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $cost_center = CostCenter::find($request->id);
            if (!$cost_center) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            $is_active = $request->has('is_active') ? (int)$request->is_active : 0;
            $updateData = [
                'is_active'        => $is_active,
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ];
            $this->updateCostCenterTree($cost_center->id, $updateData);
            $cost_center->refresh();
            return ResponseWithSuccessData($lang, new CostCenterResource($cost_center), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    private function updateCostCenterTree($costlId, array $updateData)
    {
        $allIds = [$costlId];
        $this->collectDescendantIds($costlId, $allIds);
        CostCenter::whereIn('id', $allIds)->update($updateData);
    }

    private function collectDescendantIds($parentId, array &$allIds)
    {
        $children = CostCenter::where('parent_id', $parentId)->pluck('id');
        foreach ($children as $childId) {
            $allIds[] = $childId;
            $this->collectDescendantIds($childId, $allIds);
        }
    }

    public function showList(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $flattenTree = function ($accounts) use ($lang, &$flattenTree) {
                $result = [];

                foreach ($accounts as $account) {
                    if($account->is_active == 1){
                        $result[] = [
                            'id'       => $account->id,
                            'name'     => $lang === 'ar' ? $account->name_ar : $account->name_en,
                            'code'     => $account->code,
                            'parent_id'=> $account->parent_id,
                        ];
                    }

                    if ($account->childrenRecursive->isNotEmpty()) {
                        $result = array_merge($result, $flattenTree($account->childrenRecursive));
                    }                    
                }

                return $result;
            };


            $roots = CostCenter::with(['childrenRecursive'])                
                ->where('facility_id', $request->facility_id)
                ->whereNull('parent_id')
                ->orderBy('code')
                ->get();

            $allTree = $flattenTree($roots);

            return ResponseWithSuccessData($lang, $allTree, 1);


        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

}

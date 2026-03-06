<?php


namespace App\Services\HR_Services;

use App\Models\MilitaryServiceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MilitaryServiceStatusService
{

    public function index()
    {

        $militaryStatuses = MilitaryServiceStatus::with(['country'])->withCount('employees')->with('employees');

        return $militaryStatuses;
    }

    public function store(Request $request)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'country_id' => 'required|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        if (CheckExistColumnValue('military_service_statuses', 'name_ar', $name_ar) || CheckExistColumnValue('military_service_statuses', 'name_en', $name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        $created_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $MilitaryStatus = new MilitaryServiceStatus();
        $MilitaryStatus->name_ar = $name_ar;
        $MilitaryStatus->name_en = $name_en;
        $MilitaryStatus->country_id = $request->country_id;
        $MilitaryStatus->created_by = $created_by;

        $MilitaryStatus->save();

        return $MilitaryStatus;
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'country_id' => 'required|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $MilitaryStatus = MilitaryServiceStatus::find($id);
        if (!$MilitaryStatus) {
            return RespondWithBadRequestData($lang, 8);
        }

        $exists_ar = MilitaryServiceStatus::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = MilitaryServiceStatus::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return RespondWithBadRequest($lang, 9);
        }

        $modified_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $MilitaryStatus->name_ar = $request->name_ar;
        $MilitaryStatus->name_en = $request->name_en;
        $MilitaryStatus->country_id = $request->country_id;
        $MilitaryStatus->modified_by = $modified_by;

        $MilitaryStatus->save();

        return $MilitaryStatus;
    }


    public function delete($id)
    {
        $lang = app()->getLocale();
        $MilitaryStatus = MilitaryServiceStatus::find($id);
        if (!$MilitaryStatus) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $MilitaryStatus->delete();

        return $MilitaryStatus;
    }
}

<?php


namespace App\Services\HR_Services;

use App\Models\FiledOfStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FiledOfStudyService
{
    /**
     * Display a listing of the resource.
     */


    public function index($checkToken = true)
    {
        $query = FiledOfStudy::query();

        // Add a subquery for the employee count for each university
        $query->addSelect([
            'employees_count' => DB::table('employee_educations')
                ->whereColumn('employee_educations.university_id', 'filed_of_studies.id')
                ->distinct('employee_educations.employee_id')
                ->selectRaw('count(employee_educations.employee_id)')
        ]);

        // Fetch the results with the employees_count included


        return $query;
    }

    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return respondError('ValidationError', 400, $validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        // Check if either name already exists
        if (CheckExistColumnValue('filed_of_studies', 'name_ar', $name_ar) || CheckExistColumnValue('filed_of_studies', 'name_en', $name_en)) {
            return respondError($lang == 'en' ? 'The name must be unique.' : 'يجب أن يكون الاسم فريدًا.', 400, ['error' => $lang == 'en' ? 'The name must be unique.' : 'يجب أن يكون الاسم فريدًا.']);
        }


        $filedOfStudy = new FiledOfStudy();
        $filedOfStudy->name_ar = $name_ar;
        $filedOfStudy->name_en = $name_en;
        $filedOfStudy->created_by = authActionSave()['by'];
        $filedOfStudy->created_by_type = authActionSave()['type'];
        $filedOfStudy->save();
        $dataArray = [
            'id' => $filedOfStudy->id,
            'name_ar' => $filedOfStudy->name_ar,
            'name_en' => $filedOfStudy->name_en,
        ];

        return ResponseWithSuccessData($lang, $dataArray, 1);
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return respondError('ValidationError', 400, $validator->errors());
        }

        $filedOfStudy = FiledOfStudy::find($id);
        if (!$filedOfStudy) {

            return respondErrorData('Error', 400, $lang == 'en' ? 'No record found.' : 'لم يتم العثور على البيانات.');
        }

        // Check if names changed
        if ($filedOfStudy->name_ar == $request->name_ar && $filedOfStudy->name_en == $request->name_en) {
            return respondError('Error', 400, 'No changes detected.');
        }

        // Check uniqueness but exclude the current record
        $exists_ar = FiledOfStudy::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = FiledOfStudy::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return RespondWithBadRequest($lang, 9);
        }
      

        $filedOfStudy->name_ar = $request->name_ar;
        $filedOfStudy->name_en = $request->name_en;
        $filedOfStudy->modified_by = authActionSave()['by'];
        $filedOfStudy->modified_by_type = authActionSave()['type'];
        $filedOfStudy->save();
        $dataArray = [
            'id' => $filedOfStudy->id,
            'name_ar' => $filedOfStudy->name_ar,
            'name_en' => $filedOfStudy->name_en,
        ];
        return ResponseWithSuccessData($lang, $dataArray, 1);
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the filedOfStudy by ID, or throw a 404 if not found
        $filedOfStudy = FiledOfStudy::find($id);
        if (!$filedOfStudy) {

            return respondErrorData('Error', 400, $lang == 'en' ? 'No record found.' : 'لم يتم العثور على البيانات.');
        }
        $filedOfStudy->deleted_by = authActionSave()['by'];
        $filedOfStudy->deleted_by_type = authActionSave()['type'];
        // Delete the filedOfStudy
        $filedOfStudy->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}

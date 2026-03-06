<?php


namespace App\Services\HR_Services;

use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UniversityService
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    // 
    public function indexQuery($isManager = false, $branchId = null)
    {
        return University::query()
            ->with(['country:id,name_ar,name_en'])
            ->withCount(['educations as employees_count' => function ($q) use ($isManager, $branchId) {
                $q->select(DB::raw('count(distinct employee_id)'))
                    ->whereHas('employee', function ($sub) use ($isManager, $branchId) {
                        if ($isManager && $branchId) {
                            $sub->where('branch_id', $branchId);
                        }
                    });
            }]);
    }
    public function show($id)
    {
        return University::with(['country:id,name_ar,name_en'])->findOrFail($id);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'universities' => 'required|array|min:1',
            'universities.*.name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('universities', 'name_ar')->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id)->whereNull('deleted_at');
                })
            ],
            'universities.*.name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('universities', 'name_en')->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id)->whereNull('deleted_at');
                })
            ],
            'universities.*.logo' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'universities.min' => __('validation.at_least_one_university'),
            'universities.*.name_ar.unique' => __('validation.university_ar_exists_country'),
            'universities.*.name_en.unique' => __('validation.university_en_exists_country'),
        ]);

        // Check for duplicates in the same request
        $submittedNamesAr = [];
        $submittedNamesEn = [];
        foreach ($request->universities as $uniData) {
            if (in_array($uniData['name_ar'], $submittedNamesAr)) {
                $validator->errors()->add('universities', __('validation.duplicate_ar_in_request'));
            }
            if (in_array($uniData['name_en'], $submittedNamesEn)) {
                $validator->errors()->add('universities', __('validation.duplicate_en_in_request'));
            }

            $submittedNamesAr[] = $uniData['name_ar'];
            $submittedNamesEn[] = $uniData['name_en'];
        }

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }


        $created_by = Auth::guard('admin')->user()->id ?? auth('employee')->user()->id;

        foreach ($request->universities as $index => $uniData) {
            $university = new University();
            $university->name_ar = $uniData['name_ar'];
            $university->name_en = $uniData['name_en'];
            $university->country_id = $request->country_id;
            $university->created_by = $created_by;

            // Correct way to handle multiple file uploads
            if (isset($uniData['logo']) && $uniData['logo'] instanceof \Illuminate\Http\UploadedFile) {
                UploadFile('images/university', 'logo', $university, $uniData['logo']);
            }

            $university->save();
        }

        return RespondWithSuccessRequest('ar', 1);
    }
    public function update(Request $request, $id = null)
    {
        // If id is not provided in $request (single update), merge it
        if ($id) {
            $request->merge(['id' => $id]);
        }

        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'id' => 'required|exists:universities,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $modified_by = Auth::guard('admin')->user()->id ?? auth('employee')->user()->id;
        $created_by = Auth::guard('admin')->user()->id ?? auth('employee')->user()->id;

        if ($request->has('universities') && is_array($request->universities)) {
            foreach ($request->universities as $index => $uniData) {
                $this->processUniversity($uniData, $request->country_id, $modified_by, $created_by, $index, $request);
            }
        } else {
            $this->processSingleUniversity($request, $modified_by, $created_by, 'ar');
        }

        return RespondWithSuccessRequest('ar', 1);
    }


    protected function processUniversity($uniData, $country_id, $modified_by, $created_by, $index, $request)
    {
        // Check if the university ID exists in the request
        $university = isset($uniData['id']) ? University::find($uniData['id']) : new University();

        // If it exists, set it as the university to update
        if (isset($uniData['id']) && !$university) {
            return; // Skip if university not found (shouldn't happen due to validation)
        }


        // Flag to indicate if this is a new university or an existing one
        $isNew = !$university->exists;

        // Always update country_id and modified_by
        $university->country_id = $country_id;
        $university->modified_by = $modified_by;

        // Set created_by only when creating new university
        if ($isNew) {
            $university->created_by = $created_by;
        }

        // Update names if provided
        if (isset($uniData['name_ar'])) {
            $university->name_ar = $uniData['name_ar'];
        }

        if (isset($uniData['name_en'])) {
            $university->name_en = $uniData['name_en'];
        }

        // Handle logo upload if provided and replace the old one if not new
        $logoKey = "universities.$index.logo";
        if ($request->hasFile($logoKey)) {
            if (!$isNew && $university->logo) {
                // Delete old logo if it's not a new university
                DeleteFile('images/university', $university->logo);
            }
            // Upload new logo
            UploadFile('images/university', 'logo', $university, $request->file($logoKey));
        }

        // Save the university (either creating or updating)
        $university->save();
    }


    protected function processSingleUniversity($request, $created_by, $modified_by, $lang)
    {
        $university = University::find($request->id);

        if (!$university) {
            return RespondWithBadRequestData($lang, 8); // University not found
        }

        // Check if any fields have changed
        $nameArChanged = $request->has('name_ar') && $university->name_ar != $request->name_ar;
        $nameEnChanged = $request->has('name_en') && $university->name_en != $request->name_en;
        $countryIdChanged = $request->has('country_id') && $university->country_id != $request->country_id;
        $logoChanged = $request->hasFile('logo');



        // Validate uniqueness of name_ar or name_en with country_id
        if ($nameArChanged || $countryIdChanged) {
            $exists = University::where('name_ar', $request->name_ar ?? $university->name_ar)
                ->where('country_id', $request->country_id)
                ->where('id', '!=', $university->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return RespondWithBadRequestData($lang, __('validation.university_ar_exists_country'));
            }
        }

        if ($nameEnChanged || $countryIdChanged) {
            $exists = University::where('name_en', $request->name_en ?? $university->name_en)
                ->where('country_id', $request->country_id)
                ->where('id', '!=', $university->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return RespondWithBadRequestData($lang, __('validation.university_en_exists_country'));
            }
        }

        // Apply updates
        $university->country_id = $request->country_id;
        $university->modified_by = $modified_by;

        // Update names if provided
        $university->name_ar = $request->name_ar ?? $university->name_ar;
        $university->name_en = $request->name_en ?? $university->name_en;

        // Handle logo upload
        if ($logoChanged) {
            // Delete old logo if exists
            DeleteFile('images/university', $university->logo);
            // Upload new logo
            UploadFile('images/university', 'logo', $university, $request->file('logo'));
        }

        // Save the updated university
        $university->save();
    }



    // public function update(Request $request)
    // {
    //     $lang = app()->getLocale();

    //     // Validate the request
    //     $validator = Validator::make($request->all(), [
    //         'country_id' => 'required|exists:countries,id',
    //         'universities' => 'sometimes|required|array|min:1',
    //         'universities.*.id' => 'nullable|exists:universities,id',
    //         'universities.*.name_ar' => [
    //             'required_without:universities.*.name_en|nullable',
    //             'string',
    //             'max:255',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 if ($request->has('universities')) {
    //                     foreach ($request->universities as $uniData) {
    //                         $id = $uniData['id'] ?? null;
    //                         if (isset($uniData['name_ar'])) {
    //                             $query = University::where('name_ar', $uniData['name_ar'])
    //                                 ->where('country_id', $request->country_id)
    //                                 ->whereNull('deleted_at');

    //                             if ($id) {
    //                                 $query->where('id', '!=', $id);
    //                             }

    //                             if ($query->exists()) {
    //                                 return $fail(__('validation.university_ar_exists_country'));
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         ],
    //         'universities.*.name_en' => [
    //             'required_without:universities.*.name_ar|nullable',
    //             'string',
    //             'max:255',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 if ($request->has('universities')) {
    //                     foreach ($request->universities as $uniData) {
    //                         $id = $uniData['id'] ?? null;
    //                         if (isset($uniData['name_en'])) {
    //                             $query = University::where('name_en', $uniData['name_en'])
    //                                 ->where('country_id', $request->country_id)
    //                                 ->whereNull('deleted_at');

    //                             if ($id) {
    //                                 $query->where('id', '!=', $id);
    //                             }

    //                             if ($query->exists()) {
    //                                 return $fail(__('validation.university_en_exists_country'));
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         ],
    //         'universities.*.logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //     ]);

    //     if ($validator->fails()) {
    //         return RespondWithBadRequestWithData($validator->errors());
    //     }

    //     $modified_by = Auth::guard('admin')->user()->id;

    //     // Handle multiple universities update or creation
    //     if ($request->has('universities')) {
    //         foreach ($request->universities as $index => $uniData) {
    //             // Find university by ID if exists, otherwise create a new one
    //             $university = isset($uniData['id']) ? University::find($uniData['id']) : new University();

    //             // If university exists but not found (shouldn't happen due to validation)
    //             if (isset($uniData['id']) && !$university) {
    //                 continue; // Skip or handle error
    //             }

    //             $isNew = !$university->exists;

    //             // Assign common values
    //             $university->country_id = $request->country_id;
    //             $university->modified_by = $modified_by;

    //             // Update name fields if provided
    //             if (isset($uniData['name_ar'])) {
    //                 $university->name_ar = $uniData['name_ar'];
    //             }

    //             if (isset($uniData['name_en'])) {
    //                 $university->name_en = $uniData['name_en'];
    //             }

    //             // Handle logo upload if provided
    //             $logoKey = "universities.$index.logo";
    //             if ($request->hasFile($logoKey)) {
    //                 if (!$isNew && $university->logo) {
    //                     // Delete old logo if updating
    //                     DeleteFile('images/university', $university->logo);
    //                 }
    //                 // Upload the new logo
    //                 UploadFile('images/university', 'logo', $university, $request->file($logoKey));
    //             }

    //             // Save the university (either updating or creating)
    //             $university->save();
    //         }
    //     } else {
    //         // Handle single university update (from edit modal)
    //         $university = University::find($request->id);
    //         if (!$university) {
    //             return RespondWithBadRequestData($lang, 8); // University not found
    //         }

    //         // Check if no changes were made
    //         $nameArChanged = $request->has('name_ar') && $university->name_ar != $request->name_ar;
    //         $nameEnChanged = $request->has('name_en') && $university->name_en != $request->name_en;
    //         $country_idChanged = $request->has('country_id') && $university->country_id != $request->country_id;

    //         $logoChanged = $request->hasFile('logo');

    //         if (!$nameArChanged && !$nameEnChanged && !$logoChanged && !$country_idChanged) {
    //             return RespondWithBadRequestData($lang, 10); // No changes
    //         }

    //         $university->country_id = $request->country_id;
    //         $university->modified_by = $modified_by;

    //         if ($request->has('name_ar')) {
    //             $university->name_ar = $request->name_ar;
    //         }

    //         if ($request->has('name_en')) {
    //             $university->name_en = $request->name_en;
    //         }

    //         if ($request->hasFile('logo')) {
    //             DeleteFile('images/university', $university->logo);
    //             UploadFile('images/university', 'logo', $university, $request->file('logo'));
    //         }

    //         $university->save();
    //     }

    //     return RespondWithSuccessRequest($lang, 1);
    // }


    public function delete($id)
    {
        $lang = app()->getLocale();

        $University = University::findOrFail($id);
        DeleteFile('images/university', $University->logo);
        // Delete the University
        $University->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}

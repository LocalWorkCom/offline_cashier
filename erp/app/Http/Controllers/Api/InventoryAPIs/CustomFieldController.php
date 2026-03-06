<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Resources\Inventory\CategoryResource2;
use App\Models\Size;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use App\Models\CustomField;
use Illuminate\Http\Request;
use App\Models\TableDropdown;
use App\Models\ProductTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Inventory\CategoryResource;
use App\Http\Resources\Inventory\CustomFieldResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class CustomFieldController extends Controller
{
    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
        return $employee;
    }

    public function tablesName(Request $request)
    {
        $this->getAuthenticatedEmployee();
        $lang = $request->header('lang', 'en');

        $names = TableDropdown::all();
        return ResponseWithSuccessData($lang, $names, 1);
    }

    public function showModel(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');

            $data = DB::table($request->model)->get();
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "record not found"], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => "record not found"], 404);
        }
    }

    public function categoriesName(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $this->getAuthenticatedEmployee();
        $categories = Category::pluck('name_en', 'id');
        return ResponseWithSuccessData($lang, $categories, 1);
    }
    // custom field
    public function indexCustomField(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $fields = CustomField::with([
                'category' => function ($query) {
                    $query->select('id', 'name_ar', 'name_en');
                },
                'createdBy',
                'modifiedBy'
            ])->orderByDesc('updated_at');
            // 🧩 Apply filters
            $fields->when($request->category_id, function ($query) use ($request) {
                $query->where('id', $request->category_id);
            });

            if ($request->has('visible')) {
                $fields->where('visible', $request->visible == 'visible' ? 1 : 0);
            }


            if ($request->filled('from')) {
                $fields->where('created_at', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $fields->where('created_at', '<=', $request->to);
            }

            // Apply date filter
            $fields = applyDateFilter($fields, $request->date_filter);

            $fields = paginateOrGetAll($fields, $request, null, null);

            // Use CustomFieldResource for transformation
            if (isset($fields['data'])) {
                $fields['data'] = CustomFieldResource::collection($fields['data'])->toArray($request);
            } else {
                $fields = CustomFieldResource::collection($fields)->toArray($request);
            }

            return ResponseWithSuccessDataPaginated($lang, $fields, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching custom fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showCustomField($id, Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');

            $customField = CustomField::with([
                'category' => function ($query) {
                    $query->select('id', 'name_ar', 'name_en');
                },
                'createdBy',
                'modifiedBy'
            ])->find($id);

            if (!$customField) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => 'Custom Field not found',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }

            // Use CustomFieldResource for transformation
            $customFieldResource = new CustomFieldResource($customField, $lang);

            return ResponseWithSuccessData($lang, $customFieldResource, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching custom field',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeCustomField(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $validator  = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'type' => 'required|string|in:Text,Number,Dropdown,Checkbox,Date,Toggle',
                'category_id' => 'required|exists:categories,id',
                'required' => 'required|boolean',
                'visible' => 'required|boolean',
                'value' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            $category = Category::where('id', $request->category_id)
                ->where('active', 1)
                ->whereNull('deleted_at')
                ->first();

            if (!$category) {
                return respondError(
                    $lang == 'en'
                        ? 'Cannot assign this category because it is inactive or deleted.'
                        : 'لا يمكن استخدام هذا التصنيف لأنه غير نشط أو تم حذفه.',
                    400
                );
            }

            $fieldData = [
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en ?? null,
                'value' => $request->value ?? null,
                'required' => $request->required ?? null,
                'visible' => $request->visible ?? null,
                'type' => $request->type ?? null,
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
                'category_id' => $request->category_id,
            ];

            $field = CustomField::create($fieldData);

            // Load category with the field
            $field->load(['category' => function ($query) {
                $query->select('id', 'name_ar', 'name_en');
            }]);

            // Prepare response with all fields including localized names
            return ResponseWithSuccessData($lang, new CustomFieldResource($field), 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating custom fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCustomField(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $CustomField = CustomField::find($id);

            if (!$CustomField) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => 'Custom Field not found',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'type' => 'required|string|in:Text,Number,Dropdown,Checkbox,Date,Toggle',
                'category_id' => 'required|exists:categories,id',
                'required' => 'required|boolean',
                'visible' => 'required|boolean',
                'value' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $category = Category::where('id', $request->category_id)
                ->where('active', 1)
                ->whereNull('deleted_at')
                ->first();

            if (!$category) {
                return respondError(
                    $lang == 'en'
                        ? 'Cannot assign this category because it is inactive or deleted.'
                        : 'لا يمكن استخدام هذا التصنيف لأنه غير نشط أو تم حذفه.',
                    400
                );
            }
            $CustomField->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'type' => $request->type,
                'category_id' => $request->category_id,
                'required' => $request->required,
                'visible' => $request->visible,
                'value' => $request->value,
                'updated_by' => authActionSave()['by'],
                'updated_by_type' => authActionSave()['type'],
            ]);

            // Load category with the field
            $CustomField->load(['category' => function ($query) {
                $query->select('id', 'name_ar', 'name_en');
            }]);

            return ResponseWithSuccessData($lang, new CustomFieldResource($CustomField), 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating custom field',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyCustomField(Request $request, $id = null)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        try {
            // Determine IDs to delete: either route ID or array from request
            $ids = $id ? [$id] : $request->input('ids');

            if (!$ids || !is_array($ids) || empty($ids)) {
                return respondError(
                    $lang === 'ar' ? 'يرجى تحديد الحقل المخصص للحذف' : 'Please provide custom field ID(s) to delete',
                    400
                );
            }

            // Fetch custom fields that exist
            $customFields = CustomField::with('category')->whereIn('id', $ids)->get();

            if ($customFields->isEmpty()) {
                return respondError(
                    $lang === 'ar' ? 'الحقل المخصص غير موجود' : 'Custom field(s) not found',
                    404
                );
            }

            // Check if any custom field is linked to a category
            $linkedFields = $customFields->filter(function ($field) {
                return $field->category !== null;
            });

            if ($linkedFields->isNotEmpty()) {
                $fieldNames = $linkedFields->pluck('name')->join(', ');
                return respondError(
                    $lang === 'ar'
                        ? "لا يمكن حذف الحقول المخصصة التالية لأنها مرتبطة بفئة: {$fieldNames}"
                        : "Cannot delete the following custom fields as they are linked to a category: {$fieldNames}",
                    400
                );
            }

            // Delete the custom fields
            CustomField::whereIn('id', $customFields->pluck('id'))->delete();

            return RespondWithSuccessRequest($lang, ['deleted_ids' => $customFields->pluck('id')]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting custom field(s)',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // category
    public function index(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $categories = Category::with(['customFields', 'products', 'createdBy', 'modifiedBy'])
                ->withCount(['customFields', 'products'])
                ->orderByDesc('updated_at');

            // 🧩 Apply filters
            $categories->when($request->category_id, function ($query) use ($request) {
                $query->where('id', $request->category_id);
            });
            if ($request->boolean('check_product')) {
                $categories->whereHas('products.productBrands');
            }

            if ($request->has('active')) {
                $active = $request->boolean('active');
                $categories->where('active', $active);
            }

            if ($request->filled('from')) {
                $categories->where('created_at', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $categories->where('created_at', '<=', $request->to);
            }
            // if ($request->has('parent')) {
            //     if ($request->boolean('parent')) {
            //         $categories->whereNull('parent_id');
            //     } else {
            //         $categories->whereNotNull('parent_id');
            //     }
            // }
            if ($request->has('parent')) {
                if ($request->boolean('parent')) {
                    $categories->whereNull('parent_id')
                        ->whereDoesntHave('children');
                } else {
                    $categories->whereNotNull('parent_id')->whereHas('children');
                }
            }
            $categories->when($request->parent_id, function ($query) use ($request) {
                $query->where('parent_id', $request->parent_id);
            });

            // 📅 Date filter (today, yesterday, before_yesterday, this_week, this_month)
            $categories = applyDateFilter($categories, $request->date_filter);


            // 📄 Pagination or all results
            $categories = paginateOrGetAll($categories, $request, null, null);

            // 🧾 Transform the response
            if (isset($categories['data'])) {
                $categories['data'] = new CategoryResource(collect($categories['data']), $lang);
            } else {
                $categories = new CategoryResource($categories->get(), $lang);
            }

            return ResponseWithSuccessDataPaginated($lang, $categories, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index2(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $categories = Category::with([
                'customFields',
                'products',
                'children.children',          // 🔥 load nested children
                'createdBy',
                'modifiedBy'
            ])
                ->withCount(['customFields', 'products'])
                ->orderByDesc('updated_at');

            // 🧩 Apply filters
            $categories->when($request->category_id, function ($query) use ($request) {
                $query->where('id', $request->category_id);
            });
            if ($request->boolean('check_product')) {
                $categories->whereHas('products.productBrands');
            }

            if ($request->has('active')) {
                $active = $request->boolean('active');
                $categories->where('active', $active);
            }

            if ($request->filled('from')) {
                $categories->where('created_at', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $categories->where('created_at', '<=', $request->to);
            }

            $categories->when($request->parent_id, function ($query) use ($request) {
                $query->where('parent_id', $request->parent_id);
            });

            // 📅 Date filter (today, yesterday, before_yesterday, this_week, this_month)
            $categories = applyDateFilter($categories, $request->date_filter);


            // 📄 Pagination or all results
            $categories = paginateOrGetAll($categories, $request, null, null);

            // 🧾 Transform the response
            // $categories['data'] = new CategoryResource2(collect($categories['data']), $lang);

            // $result = CategoryResource2::collection($categories['data'])
            //     ->map(function ($resource) use ($lang) {
            //         return (new CategoryResource2($resource, $lang))->resolve();
            //     });
            return ResponseWithSuccessDataPaginated($lang, $categories, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'ar');
            app()->setLocale($lang);
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name_ar')->whereNull('deleted_at'),
                ],
                'name_en' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name_en')->whereNull('deleted_at'),
                ],
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'active' => 'required|boolean',
                'parent_id' => 'nullable|array',
                'parent_id.*' => 'integer|exists:categories,id',
                'custom_fields' => 'nullable|array',
                'custom_fields.*' => 'integer',
            ]);

            if ($validator->fails()) {
                return respondError(
                    __('Validation.error'),
                    400,
                    $validator->errors()
                );
            }

            $user = auth('employee')->user();
            $GetLastID = GetLastID('categories');

            // Create the new category
            $category = new Category();
            $category->name_ar = $request->name_ar;
            $category->name_en = $request->name_en;
            $category->description_ar = $request->description_ar;
            $category->description_en = $request->description_en;
            //new
            $category->code = GenerateCode('categories');
            $category->active = $request->boolean('active'); // Use boolean() method
            $category->created_by = $user->id;
            $category->save();

            $parentCategories = [];

            // If parent_id array sent → make this new category their parent
            if (!empty($request->parent_categories) && is_array($request->parent_categories)) {
                // Check if any selected parent category is inactive
                $inactiveParents = Category::whereIn('id', $request->parent_categories)
                    ->where('active', 0)
                    ->pluck('name_' . $lang)
                    ->toArray();

                if (!empty($inactiveParents)) {
                    return respondError(
                        $lang == 'ar'
                            ? 'لا يمكن تعيين فئة غير مفعلة كفئة رئيسية: ' . implode(', ', $inactiveParents)
                            : 'Cannot assign inactive categories as parent categories: ' . implode(', ', $inactiveParents),
                        400
                    );
                }

                // ✅ Assign active categories as parent
                Category::whereIn('id', $request->parent_categories)
                    ->update(['parent_id' => $category->id]);

                $parentCategories = Category::whereIn('id', $request->parent_categories)
                    ->select('id', 'name_ar', 'name_en', 'code', 'active', 'parent_id', 'created_at', 'updated_at')
                    ->get();
            }

            // ✅ Handle custom fields (copy existing ones)
            $fields = [];
            if ($request->has('custom_fields') && is_array($request->custom_fields)) {
                $customFields = CustomField::whereIn('id', $request->custom_fields)->get();

                foreach ($customFields as $existingField) {
                    $fieldCopy = $existingField->replicate();
                    $fieldCopy->category_id = $category->id;
                    $fieldCopy->created_at = now();
                    $fieldCopy->updated_at = now();
                    $fieldCopy->save();
                    $fields[] = $fieldCopy;
                }
            }

            // ✅ Handle newly added custom fields
            if ($request->add_fields) {
                $names_ar = $request->input('name_ar_cus', []);
                $names_en = $request->input('name_en_cus', []);
                $values = $request->input('value', []);
                $requireds = $request->input('required', []);
                $types = $request->input('type', []);

                $fieldsCount = count($names_ar);
                for ($i = 0; $i < $fieldsCount; $i++) {
                    $fieldData = [
                        'name_ar' => $names_ar[$i] ?? null,
                        'name_en' => $names_en[$i] ?? null,
                        'value' => $values[$i] ?? null,
                        'required' => $requireds[$i] ?? null,
                        'type' => $types[$i] ?? null,
                        'category_id' => $category->id,
                    ];

                    $fieldValidator = Validator::make($fieldData, [
                        'name_ar' => 'required|string|max:255',
                        'name_en' => 'required|string|max:255',
                        'required' => 'required|boolean',
                        'type' => 'required|string|max:255',
                    ]);

                    if ($fieldValidator->fails()) {
                        return respondError(
                            __('Validation.Validation failed for custom field'),
                            400,
                            $fieldValidator->errors()
                        );
                    }

                    $field = CustomField::create($fieldData);
                    $fields[] = $field;
                }
            }

            // ✅ Refresh category with all relationships needed for the resource
            $category->load(['customFields', 'products', 'createdBy', 'modifiedBy']);
            $category->loadCount(['customFields', 'products']);

            // ✅ Use the same CategoryResource as index and show
            $categoriesCollection = collect([$category]);
            $responseData = new CategoryResource($categoriesCollection, $lang);

            // Since it's a single category, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating category and custom fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update($category_id, Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'ar');
            app()->setLocale($lang);

            $category = Category::with(['createdBy', 'modifiedBy'])->find($category_id);
            if (!$category) {
                return respondError(
                    __('Validation.Category not found'),
                    404,
                );
            }

            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name_ar')->whereNull('deleted_at')->ignore($category_id),
                ],
                'name_en' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name_en')->whereNull('deleted_at')->ignore($category_id),
                ],
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'active' => 'required|boolean',
                'parent_id' => 'nullable|array',
                'parent_id.*' => 'integer|exists:categories,id',
                'custom_fields' => 'nullable|array',
                'custom_fields.*' => 'integer',
            ]);

            if ($validator->fails()) {
                return respondError(
                    __('Validation.error'),
                    400,
                    $validator->errors()
                );
            }

            $user = auth('employee')->user();

            // ✅ Update main category data with boolean active
            $category->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'description_ar' => $request->description_ar,
                'description_en' => $request->description_en,
                'active' => $request->boolean('active'), // Use boolean() method
                'modify_by' => $user->id,
            ]);

            $parentCategories = [];

            if (!empty($request->parent_categories) && is_array($request->parent_categories)) {
                // ✅ Check if any selected parent category is inactive
                $inactiveParents = Category::whereIn('id', $request->parent_categories)
                    ->where('active', 0)
                    ->pluck('name_' . $lang)
                    ->toArray();

                if (!empty($inactiveParents)) {
                    return respondError(
                        $lang == 'ar'
                            ? 'لا يمكن تعيين فئة غير مفعلة كفئة رئيسية: ' . implode(', ', $inactiveParents)
                            : 'Cannot assign inactive categories as parent categories: ' . implode(', ', $inactiveParents),
                        400
                    );
                }

                // ✅ Assign active categories as parent
                Category::whereIn('id', $request->parent_categories)
                    ->update(['parent_id' => $category->id]);

                $parentCategories = Category::whereIn('id', $request->parent_categories)
                    ->select('id', 'name_ar', 'name_en', 'code', 'active', 'parent_id', 'created_at', 'updated_at')
                    ->get();
            }


            // ✅ Handle custom fields duplication from existing fields
            $fields = [];
            if ($request->has('custom_fields') && is_array($request->custom_fields)) {
                $customFields = CustomField::whereIn('id', $request->custom_fields)->get();

                foreach ($customFields as $existingField) {
                    $fieldCopy = $existingField->replicate();
                    $fieldCopy->category_id = $category->id;
                    $fieldCopy->created_at = now();
                    $fieldCopy->updated_at = now();
                    $fieldCopy->save();
                    $fields[] = $fieldCopy;
                }
            }

            // ✅ Handle newly added or updated custom fields
            if ($request->add_fields) {
                $field_ids = $request->input('field_id', []);
                $names_ar = $request->input('name_ar_cus', []);
                $names_en = $request->input('name_en_cus', []);
                $values = $request->input('value', []);
                $requireds = $request->input('required', []);
                $types = $request->input('type', []);

                $fieldsCount = count($names_ar);

                for ($i = 0; $i < $fieldsCount; $i++) {
                    $fieldData = [
                        'name_ar' => $names_ar[$i] ?? null,
                        'name_en' => $names_en[$i] ?? null,
                        'value' => $values[$i] ?? null,
                        'required' => $requireds[$i] ?? null,
                        'type' => $types[$i] ?? null,
                        'category_id' => $category_id,
                    ];

                    $fieldValidator = Validator::make($fieldData, [
                        'name_ar' => 'required|string|max:255',
                        'name_en' => 'required|string|max:255',
                        'required' => 'required|boolean',
                        'type' => 'required|string|max:255',
                    ]);

                    if ($fieldValidator->fails()) {
                        return respondError(
                            __('validation.error'),
                            400,
                            $fieldValidator->errors()
                        );
                    }

                    $field_id = $field_ids[$i] ?? null;

                    if ($field_id) {
                        $customField = CustomField::where('id', $field_id)
                            ->where('category_id', $category_id)
                            ->first();

                        if ($customField) {
                            $customField->update($fieldData);
                            $fields[] = $customField;
                        }
                    } else {
                        $customField = CustomField::create($fieldData);
                        $fields[] = $customField;
                    }
                }
            }

            // ✅ Clean up: delete old custom fields not in update
            if ($request->has('custom_fields') || $request->add_fields) {
                $fieldIdsToKeep = collect($fields)->pluck('id')->toArray();
                CustomField::where('category_id', $category_id)
                    ->whereNotIn('id', $fieldIdsToKeep)
                    ->delete();
            }

            // ✅ Refresh category with all relationships needed for the resource
            $category->refresh();
            $category->load(['customFields', 'products', 'createdBy', 'modifiedBy']);
            $category->loadCount(['customFields', 'products']);

            // ✅ Use the same CategoryResource as index and show
            $categoriesCollection = collect([$category]);
            $responseData = new CategoryResource($categoriesCollection, $lang);

            // Since it's a single category, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update category and custom fields',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');
            app::setLocale($lang);
            $categoryExists = Category::where('id', $id)->exists();

            if (!$categoryExists) {
                return respondError(
                    __('branch_menu_category.not_found'),
                    404
                );
            }

            $category = Category::with(['customFields', 'products', 'createdBy', 'modifiedBy'])
                ->withCount(['customFields', 'products'])
                ->findOrFail($id);

            // Use the same Resource as index method
            $categoriesCollection = collect([$category]);
            $responseData = new CategoryResource($categoriesCollection, $lang);

            // Since it's a single category, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $ids = $request->input('ids', []); // array of category ids

        if (!$ids) {
            return respondError($lang === 'ar' ? 'يرجى تحديد الفئة للحذف' : 'Please provide category IDs to delete', 400);
        }

        $categories = Category::whereIn('id', $ids)->get();

        $cannotDelete = [];

        foreach ($categories as $category) {
            $usedAsParent = Category::where('parent_id', $category->id)->whereNull('deleted_at')->exists();
            $hasProducts  = Product::where('category_id', $category->id)
                ->orWhere('sub_category_id', $category->id)
                ->whereNull('deleted_at')->exists();
            $hasPOs       = PurchaseOrder::where('category_id', $category->id)->whereNull('deleted_at')->exists();

            if ($usedAsParent || $hasProducts || $hasPOs) {
                $cannotDelete[] = $category->name;
                // Optionally deactivate instead
                $category->update(['active' => 0]);
            } else {
                $category->delete();
            }
        }

        if (!empty($cannotDelete)) {
            return respondError(
                $lang == 'en'
                    ? 'Cannot delete categories: ' . implode(', ', $cannotDelete) . '. They are linked to products, subcategories, or purchase orders.'
                    : 'لا يمكن حذف الفئات: ' . implode(', ', $cannotDelete) . '. مرتبطة بالمنتجات أو الفئات الفرعية أو أوامر الشراء.',
                400
            );
        }

        return RespondWithSuccessRequest($lang, 1);
    }

    /**
     * Remove category relation from a product
     */
    public function removeCategoryFromProduct(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|integer|exists:products,id',
                'category_id' => 'required|integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return [
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validator->errors(),
                    'validation_type' => true
                ];
            }

            $productId = $request->product_id;
            $categoryId = $request->category_id;

            // Find the product
            $product = Product::find($productId);

            if (!$product) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => 'Product not found',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }

            // Check if the product actually has this category
            if ($product->category_id != $categoryId) {
                return [
                    'code' => 400,
                    'status' => false,
                    'message' => $lang == 'en'
                        ? 'This product does not belong to the specified category'
                        : 'هذا المنتج لا ينتمي إلى الفئة المحددة',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }

            // Get user info for logging
            $user = auth('employee')->user();

            // Remove the category relation by setting category_id to null
            $product->category_id = null;
            $product->modify_by = $user->id;
            $product->save();

            // Prepare response data
            $responseData = [
                'product' => [
                    'id' => $product->id,
                    'name' => $lang == 'en' && !empty($product->name_en) ? $product->name_en : $product->name_ar,
                    'name_ar' => $product->name_ar,
                    'name_en' => $product->name_en,
                    'code' => $product->code,
                    'category_id' => null, // Now null after removal
                    'category_name' => null,
                ],
                'removed_category' => [
                    'id' => $categoryId,
                    'name' => $lang == 'en'
                        ? Category::find($categoryId)->name_en
                        : Category::find($categoryId)->name_ar,
                ]
            ];

            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove category from product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove category relation from a custom field
     */
    public function removeCategoryFromCustomField(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $validator = Validator::make($request->all(), [
                'custom_field_id' => 'required|integer|exists:custom_fields,id',
                'category_id' => 'required|integer|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return [
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validator->errors(),
                    'validation_type' => true
                ];
            }

            $customFieldId = $request->custom_field_id;
            $categoryId = $request->category_id;

            // Find the custom field
            $customField = CustomField::where('id', $customFieldId)
                ->where('category_id', $categoryId)
                ->first();

            if (!$customField) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => $lang == 'en'
                        ? 'Custom field not found or does not belong to the specified category'
                        : 'الحقل المخصص غير موجود أو لا ينتمي إلى الفئة المحددة',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }

            // Remove category relation by setting category_id to null
            $customField->category_id = null;
            $customField->save();

            // Prepare response data
            $responseData = [
                'message' => $lang == 'en'
                    ? 'Category successfully removed from custom field'
                    : 'تم إزالة الفئة من الحقل المخصص بنجاح',
                'custom_field' => [
                    'id' => $customField->id,
                    'name' => $lang == 'en' && !empty($customField->name_en) ? $customField->name_en : $customField->name_ar,
                    'name_ar' => $customField->name_ar,
                    'name_en' => $customField->name_en,
                    'value' => $customField->value,
                    'required' => $customField->required,
                    'type' => $customField->type,
                    'category_id' => null, // Now null
                ],
                'removed_category_id' => $categoryId
            ];

            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove category from custom field',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function category(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Verify category exists
            $categoryExists = Category::where('id', $id)->exists();

            if (!$categoryExists) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => 'Category not found',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }

            // Get category to include its name in response
            $category = Category::find($id);

            // Get only custom fields for this category
            $customFields = CustomField::where('category_id', $id)->get();

            // Prepare custom fields with all name fields
            $fieldsData = [];
            foreach ($customFields as $field) {
                $fieldData = [
                    'id' => $field->id,
                    'name' => $lang == 'en' && !empty($field->name_en) ? $field->name_en : $field->name_ar,
                    'name_ar' => $field->name_ar,
                    'name_en' => $field->name_en,
                    'value' => $field->value,
                    'required' => $field->required,
                    'type' => $field->type,
                    'category_id' => $field->category_id,
                    'category_name' => $lang == 'en' && !empty($category->name_en) ? $category->name_en : $category->name_ar,
                    'category_name_ar' => $category->name_ar,
                    'category_name_en' => $category->name_en
                ];

                // Only add data field if it's not null or empty
                if (!empty($field->data)) {
                    if (is_string($field->data)) {
                        $decoded = json_decode($field->data, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $fieldData['data'] = $decoded;
                        } else {
                            $fieldData['data'] = $field->data;
                        }
                    } else {
                        $fieldData['data'] = $field->data;
                    }
                }

                $fieldsData[] = $fieldData;
            }

            return ResponseWithSuccessData($lang, $fieldsData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve custom fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function storeColor(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'string',
            'hexa_code' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $hexa_code = $request->hexa_code;
        $created_by =  Auth::guard('employee')->user()->id;

        $color = new Color();
        $color->name_ar = $name_ar;
        $color->name_en = $name_en;
        $color->hexa_code = $hexa_code; // Store the hex code
        $color->created_by = $created_by;
        $color->save();

        $colors = Color::all();

        return ResponseWithSuccessData($lang, $colors, 1);
    }

    public function storeSize(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'category_id' => 'required|exists:categories,id', // Validate the category_id
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $category_id = $request->category_id; // Get the category_id from the request

        $created_by =  Auth::guard('employee')->user()->id;

        $size = new Size();
        $size->name_ar = $name_ar;
        $size->name_en =  $name_en;
        $size->category_id = $category_id; // Store the category_id
        $size->created_by =  $created_by;
        $size->save();

        $sizes = Size::all();

        return ResponseWithSuccessData($lang, $sizes, 1);
    }
}

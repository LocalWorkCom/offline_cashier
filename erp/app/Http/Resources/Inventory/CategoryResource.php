<?php

namespace App\Http\Resources\Inventory;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\ProductBrand;
use App\Models\ProductTransaction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryResource extends ResourceCollection
{
    private $lang;
    private $inventoryActive;
    private $purchasesActive;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;

        // Check module statuses
        $this->inventoryActive = checkModuleStatus('Inventory');
        $this->purchasesActive = checkModuleStatus('Purchases');
    }

    public function toArray($request)
    {
        return $this->collection->map(function ($category) {
            return $this->formatCategoryResponse($category);
        })->toArray();
    }

    private function formatCategoryResponse($category)
    {
        $isArray = is_array($category);

        // Get child categories (what you call parent_categories)
        $categoryId = $isArray ? $category['id'] : $category->id;
        $childCategories = Category::where('parent_id', $categoryId)->get();

        $parentCategories = $childCategories->map(function ($child) {
            return [
                'id' => $child->id,
                'name_ar' => $child->name_ar,
                'name_en' => $child->name_en,
                'name' => $this->lang == 'en' && !empty($child->name_en) ? $child->name_en : $child->name_ar,
                'code' => $child->code,
                'active' => $child->active,
                'parent_id' => $child->parent_id,
                'created_at' => $child->created_at ? Carbon::parse($child->created_at)->format('Y-m-d H:i:s') : null,
                'updated_at' => $child->updated_at ? Carbon::parse($child->updated_at)->format('Y-m-d H:i:s') : null,
            ];
        })->toArray();

        // Prepare category data
        $categoryData = [
            'id' => $isArray ? $category['id'] : $category->id,
            'name' => $this->getTranslatedField($category, 'name'),
            'name_ar' => $isArray ? $category['name_ar'] : $category->name_ar,
            'name_en' => $isArray ? $category['name_en'] : $category->name_en,
            'description' => $this->getTranslatedField($category, 'description'),
            'description_ar' => $isArray ? ($category['description_ar'] ?? null) : $category->description_ar,
            'description_en' => $isArray ? ($category['description_en'] ?? null) : $category->description_en,
            'code' => $isArray ? $category['code'] : $category->code,
            'active' => $isArray ? (int) $category['active'] : (int) $category->active,
            'parent_id' => $isArray ? ($category['parent_id'] ?? null) : $category->parent_id,
            'created_at' => $this->formatDateTime($isArray ? $category['created_at'] : $category->created_at),
            'updated_at' => $this->formatDateTime($isArray ? $category['updated_at'] : $category->updated_at),
            'created_by' => $this->getEmployeeName($isArray ? ($category['created_by'] ?? null) : $category->createdBy),
            'modify_by' => $this->getEmployeeName($isArray ? ($category['modify_by'] ?? null) : $category->modifiedBy),
        ];

        // Add counts only if inventory is active
        if ($this->inventoryActive) {
            $categoryData['no_custom_fields'] = $isArray
                ? ($category['custom_fields_count'] ?? count($category['custom_fields'] ?? []))
                : ($category->custom_fields_count ?? $category->customFields->count());
            $categoryData['products_count'] = $isArray
                ? ($category['products_count'] ?? count($category['products'] ?? []))
                : ($category->products_count ?? $category->products->count());
        }

        // Prepare custom fields - only if inventory is active
        $fieldsData = [];
        if ($this->inventoryActive) {
            $customFields = $isArray ? ($category['custom_fields'] ?? []) : $category->customFields;

            if (!empty($customFields)) {
                foreach ($customFields as $field) {
                    $fieldIsArray = is_array($field);
                    $fieldsData[] = [
                        'id' => $fieldIsArray ? $field['id'] : $field->id,
                        'name' => $this->getTranslatedField($field, 'name'),
                        'name_ar' => $fieldIsArray ? $field['name_ar'] : $field->name_ar,
                        'name_en' => $fieldIsArray ? $field['name_en'] : $field->name_en,
                        'value' => $fieldIsArray ? $field['value'] : $field->value,
                        'required' => $fieldIsArray ? $field['required'] : $field->required,
                        'type' => $fieldIsArray ? $field['type'] : $field->type,
                        'category_id' => $fieldIsArray ? $field['category_id'] : $field->category_id,
                        'category_name' => $categoryData['name'],
                        'category_name_ar' => $categoryData['name_ar'],
                        'category_name_en' => $categoryData['name_en']
                    ];
                }
            }
        }

        // Prepare products data - only if inventory is active
        $productsData = [];
        if ($this->inventoryActive) {
            $products = $isArray ? ($category['products'] ?? []) : $category->products;

            if (!empty($products)) {
                foreach ($products as $product) {
                    $productIsArray = is_array($product);
                    $productId = $productIsArray ? $product['id'] : $product->id;

                    $totalQuantity = $this->calculateStockAvailability($productId);
                    $unitData = $this->getUnitData($productId);

                    $productsData[] = [
                        'id' => $productId,
                        'name' => $this->getTranslatedField($product, 'name'),
                        'name_ar' => $productIsArray ? $product['name_ar'] : $product->name_ar,
                        'name_en' => $productIsArray ? $product['name_en'] : $product->name_en,
                        'code' => $productIsArray ? $product['code'] : $product->code,
                        'active' => $productIsArray ? $product['active'] : $product->active,
                        'price' => $productIsArray ? $product['price'] : $product->price,
                        'description' => $this->getTranslatedField($product, 'description'),
                        'description_ar' => $productIsArray ? $product['description_ar'] : $product->description_ar,
                        'description_en' => $productIsArray ? $product['description_en'] : $product->description_en,
                        'image' => $productIsArray ? $product['image'] : $product->image,
                        'category_id' => $productIsArray ? $product['category_id'] : $product->category_id,
                        'stock_availability' => $totalQuantity,
                        'unit' => $unitData,
                        'created_at' => $this->formatDateTime($productIsArray ? $product['created_at'] : $product->created_at),
                        'updated_at' => $this->formatDateTime($productIsArray ? $product['updated_at'] : $product->updated_at),
                    ];
                }
            }
        }

        // Build response based on module status
        $response = [
            'category' => $categoryData,
            'parent_categories' => $parentCategories,
        ];

        // Add fields only if inventory is active
        if ($this->inventoryActive) {
            $response['fields'] = !empty($fieldsData) ? $fieldsData : 'no custom fields defined';
        }

        // Add products only if inventory is active
        if ($this->inventoryActive) {
            $response['products'] = $productsData;
        }

        return $response;
    }

    private function getTranslatedField($item, $field)
    {
        $isArray = is_array($item);
        $fieldAr = $field . '_ar';
        $fieldEn = $field . '_en';

        if ($isArray) {
            return $this->lang == 'en' && !empty($item[$fieldEn]) ? $item[$fieldEn] : $item[$fieldAr];
        } else {
            return $this->lang == 'en' && !empty($item->$fieldEn) ? $item->$fieldEn : $item->$fieldAr;
        }
    }

    private function formatDateTime($date)
    {
        return $date ? Carbon::parse($date)->format('Y-m-d H:i:s') : null;
    }

    private function getEmployeeName($employee)
    {
        if (!$employee) {
            return null;
        }

        if (is_array($employee)) {
            return trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
        }

        return trim($employee->first_name . ' ' . $employee->last_name);
    }

    private function calculateStockAvailability($productId)
    {
        try {
            $brandIds = ProductBrand::where('product_id', $productId)->pluck('id');
            return ProductTransaction::whereIn('product_brand_id', $brandIds)->sum('quantity');
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getUnitData($productId)
    {
        $productBrandWithUnit = ProductBrand::where('product_id', $productId)
            ->whereNotNull('default_unit_id')
            ->with('defaultUnit')
            ->first();

        if ($productBrandWithUnit && $productBrandWithUnit->defaultUnit) {
            return [
                'id' => $productBrandWithUnit->defaultUnit->id,
                'name_ar' => $productBrandWithUnit->defaultUnit->name_ar,
                'name_en' => $productBrandWithUnit->defaultUnit->name_en
            ];
        }

        return null;
    }
}

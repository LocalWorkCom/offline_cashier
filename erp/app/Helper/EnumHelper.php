<?php

namespace App\Helper;

use Illuminate\Support\Facades\Request;

class EnumHelper
{
    protected static function getLang(): string
    {
        return Request::header('lang', 'ar');
    }

    public static function depreciation(): array
    {
        return self::mapTranslated([
            'yes' => ['en' => 'Yes', 'ar' => 'نعم'],
            'no' => ['en' => 'No', 'ar' => 'لا'],
        ]);
    }

    public static function depreciationMethod(): array
    {
        return self::mapTranslated([
            'fixed' => ['en' => 'Fixed', 'ar' => 'ثابت'],
            'declining' => ['en' => 'Declining', 'ar' => 'تناقصي'],
        ]);
    }

    public static function depreciationPeriodType(): array
    {
        return self::mapTranslated([
            'year' => ['en' => 'Year', 'ar' => 'سنة'],
            'month' => ['en' => 'Month', 'ar' => 'شهر'],
            'week' => ['en' => 'Week', 'ar' => 'أسبوع'],
            'day' => ['en' => 'Day', 'ar' => 'يوم'],
        ]);
    }

    public static function depreciationType(): array
    {
        return self::mapTranslated([
            'automatic' => ['en' => 'Automatic', 'ar' => 'تلقائي'],
            'manual' => ['en' => 'Manual', 'ar' => 'يدوي'],
        ]);
    }

    public static function stored(): array
    {
        return self::mapTranslated([
            'branch' => ['en' => 'Branch', 'ar' => 'فرع'],
            'store' => ['en' => 'Store', 'ar' => 'مخزن'],
        ]);
    }

    public static function status(): array
    {
        return self::mapTranslated([
            'active' => ['en' => 'Active', 'ar' => 'نشط'],
            'inactive' => ['en' => 'Inactive', 'ar' => 'غير نشط'],
            'sold' => ['en' => 'Sold', 'ar' => 'تم البيع'],
            'in_depreciation' => ['en' => 'In Depreciation', 'ar' => 'تحت الاستهلاك'],
        ]);
    }

    public static function productiveLifeType(): array
    {
        return self::mapTranslated([
            'year' => ['en' => 'Year', 'ar' => 'سنة'],
            'month' => ['en' => 'Month', 'ar' => 'شهر'],
        ]);
    }

    private static function mapTranslated(array $items): array
    {
        $lang = self::getLang();

        return collect($items)->map(function ($translations, $key) use ($lang) {
            return [
                'id' => $key,
                'name' => $translations[$lang] ?? $translations['en'],
            ];
        })->values()->toArray();
    }

    public static function getValueByKey(string $group, string $key): ?string
    {
        $enumMap = [
            'depreciation' => self::depreciation(),
            'depreciation_method' => self::depreciationMethod(),
            'depreciation_period_type' => self::depreciationPeriodType(),
            'depreciation_type' => self::depreciationType(),
            'stored' => self::stored(),
            'status' => self::status(),
            'productive_life_type' => self::productiveLifeType(),
        ];

        $list = $enumMap[$group] ?? [];

        foreach ($list as $item) {
            if ($item['id'] === $key) {
                return $item['name'];
            }
        }

        return null;
    }

}

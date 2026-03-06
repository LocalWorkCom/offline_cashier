<?php

namespace App\Http\Resources\Finance;
use Illuminate\Support\Collection;

class CompanyResource extends AbstractFinanceResource
{
    private bool $withRelations;

    public function __construct($resource, bool $withRelations = true)
    {
        parent::__construct($resource);
        $this->withRelations = $withRelations;
    }

    public function formatItem($item): array
    {
        $lang = request()->header('lang', 'en');

        $socialMedia = $this->withRelations ? $item->socialMediaInformation : collect();
        $companyPolicy = $this->withRelations ? $item->companyPolicy->where('is_active', 1) : collect();
        $branch = $this->withRelations ? $item->branch->where('is_active', 1) : collect();
        $contactInformationSetting = $this->withRelations ? $item->contactInformationSetting: collect();

        return [
            'id'                          => $item->id,
            'name'                        => $lang === 'ar' ? $item->name_ar : $item->name_en,
            'description'                 => $lang === 'ar' ? $item->description_ar : $item->description_en,
            'branch_count'                => $item->branch?->count(),
            'is_active'                   => (bool) $item->is_active,
            'logo'                        => $item->logo,
            'trade_license'               => $item->trade_license,
            'license_expiry_date'         => $item->license_expiry_date,
            'tax_registration_number'     => $item->tax_registration_number,
            'capital'                     => $item->capital,
            'scanned_trade_license'       => $item->scanned_trade_license,
            'business_activity'           => $item->business_activity,
            'businessActivity' => $item->businessActivity
                ? ($lang === 'ar' ? $item->businessActivity->name_ar : $item->businessActivity->name_en)
                : null,
            'socialMediaInformation' => $this->withRelations
                ? $this->formatSocialMedia($socialMedia, $lang)
                : [],

            'companyPolicy' => $this->withRelations
                ? $this->formatCompanyPolicy($companyPolicy, $lang)
                : [],

            'branch' => $this->withRelations
                ? $this->formatBranch($branch, $lang)
                : [],

            'contactInformationSetting' => $this->withRelations
                ? $this->formatContactInformationSetting($contactInformationSetting, $lang)
                : [],
            'created_at' => $item->created_at?->toDateTimeString(),
            'updated_at' => $item->updated_at?->toDateTimeString(),
            'deleted_at' => $item->deleted_at?->toDateTimeString(),
            //
            'companySize' => null,
            'foundedYear' => null,
            'industries' => null,
            'headOffice' => null,
            'strategicPartners' => null,
            'expansionPlans' => null,
            'awards' => null,
            'trainingPrograms' => null,

        ];
    }

    private function formatSocialMedia($socialMedia, string $lang): array
    {
        if (!$socialMedia || $socialMedia->isEmpty()) {
            return [];
        }

        if ($socialMedia instanceof Collection) {
            return $socialMedia->map(function ($social) use ($lang) {
                return [
                    'id'        => $social->id,
                    'android_app_link'        => $social->android_app_link,
                    'ios_app_link'        => $social->ios_app_link,
                    'facebook'       => $social->facebook,
                    'instagram'       => $social->instagram,
                    'snapchat'       => $social->snapchat,
                    'twitter'       => $social->twitter,
                    'tiktok'       => $social->tiktok,
                ];
            })->all();
        }

        return [
            'id'       => $socialMedia->id,
            'android_app_link'        => $socialMedia->android_app_link,
            'ios_app_link'        => $socialMedia->ios_app_link,
            'facebook'       => $socialMedia->facebook,
            'instagram'       => $socialMedia->instagram,
            'snapchat'       => $socialMedia->snapchat,
            'twitter'       => $socialMedia->twitter,
            'tiktok'       => $socialMedia->tiktok,
        ];
    }

    private function formatCompanyPolicy($companyPolicy, string $lang): array
    {
        if (!$companyPolicy || $companyPolicy->isEmpty()) {
            return [];
        }

        if ($companyPolicy instanceof Collection) {
            return $companyPolicy->map(function ($policy) use ($lang) {
                return [
                    'id'        => $policy->id,
                    'title'        => $policy->title,
                    'description'        => $policy->description,
                    'file_path'       => $policy->file_path,
                ];
            })->all();
        }

        return [
            'id'        => $companyPolicy->id,
            'title'        => $companyPolicy->title,
            'description'        => $companyPolicy->description,
            'file_path'       => $companyPolicy->file_path,
        ];
    }

    private function formatBranch($branch, string $lang): array
    {
        if (!$branch || $branch->isEmpty()) {
            return [];
        }

        if ($branch instanceof Collection) {
            return $branch->map(function ($data) use ($lang) {
                return [
                    'id'        => $data->id,
                    'name_ar'        => $data->name_ar,
                    'name_en'        => $data->name_en,
                    'address'        => $lang === 'ar' ? $data->address_ar : $data->address_en,
                    'phone'       => $data->phone,
                    'company'       => $lang === 'ar' ? $data->company?->name_ar : $data->company?->name_en,
                ];
            })->all();
        }

        return [
            'id'        => $branch->id,
            'name_ar'        => $branch->name_ar,
            'name_en'        => $branch->name_en,
            'address'        => $lang === 'ar' ? $branch->address_ar : $branch->address_en,
            'phone'       => $branch->phone,
            'company'       => $lang === 'ar' ? $branch->company?->name_ar : $branch->company?->name_en,
        ];
    }

    private function formatContactInformationSetting($contactInformationSetting, string $lang): array
    {
        if (!$contactInformationSetting || $contactInformationSetting->isEmpty()) {
            return [];
        }

        if ($contactInformationSetting instanceof Collection) {
            return $contactInformationSetting->map(function ($data) use ($lang) {
                return [
                    'id'        => $data->id,
                    'address'        => $lang === 'ar' ? $data->company_address_ar : $data->company_address_en,
                    'map_link'       => $data->map_link,
                    'phone_number'       => $data->phone_number,
                    'email'       => $data->email,
                    'website_link'       => $data->website_link,
                ];
            })->all();
        }

        return [
            'id'        => $contactInformationSetting->id,
            'address'        => $lang === 'ar' ? $contactInformationSetting->company_address_ar : $contactInformationSetting->company_address_en,
            'map_link'       => $contactInformationSetting->map_link,
            'phone_number'       => $contactInformationSetting->phone_number,
            'email'       => $contactInformationSetting->email,
            'website_link'       => $contactInformationSetting->website_link,
        ];
    }

}

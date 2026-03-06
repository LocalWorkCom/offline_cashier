<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Spatie\Activitylog\Models\Activity;


class EmployeeResource extends JsonResource
{
    use Paginatable;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    protected $module = null;
    protected $profile = null;

    public function setModule($module ,$request)
    {
        $lang = $request->header('lang', 'ar'); // ✅ always has value

        $this->module = $module;
        return $this;
    }
    public function setProfile($profile)
    {
        $this->$profile = $profile;
        return $this;
    }
    public function toArray(Request $request): array
    {

        $module = $this->module;
        $data = [
            'id'            => $this->id,
            'employee_code' => $this->employee_code,
            'full_name'     => $this->first_name . ' ' . $this->last_name,
            'phone'         => $this->phone_number,
            'country_code'  => $this->country_code,
            'national_id'   => $this->national_id,
            'email'         => $this->email,
            'position' => match ($module) {
                'purchase' => $this->position_name ?? null,
                'inventory' => ($this->inventoryStores->first()
                    ? $this->inventoryStores->first()->pivot->position
                    : null),
                default => null,
            },
            'department' => match ($module) {
                'purchase' => $this->department ? [
                    'id'   => $this->department->id,
                    'name' => $this->department->name,
                ] : null,
                'inventory' =>  $this->department_id
                    ? $this->department->name
                    : ($this->inventoryStores->first()
                        ? $this->inventoryStores->first()->pivot->department
                        : null),
                default => null,
            },

            'created_at' => $this->created_at ? $this->created_at->toDateString() : null,
            'last_update' => $this->updated_at ? $this->updated_at->toDateString() : null,
            'is_active' => $module === 'inventory'
                ? ($this->employee_status_id == 7 ? false : (bool) $this->employee_status_id)
                : $this->employee_status_id,
            'is_user' => (bool) $this->is_active,
            'role' => $this->roles->first() ? [
                'id' => $this->roles->first()->id,
                'name' => $this->roles->first()->name,
            ] : null,


        ];
        if ($this->profile === 'profile') {

            $lastLoginAt = Activity::where('event', 'login')
                ->where('causer_type', \App\Models\Employee::class)
                ->where('causer_id', $this->id)
                ->latest('created_at')
                ->value('created_at');

            if ($lastLoginAt) {
                $lang = $request->header('lang');
                $loginDate = Carbon::parse($lastLoginAt);
                $now = Carbon::now();

                if ($loginDate->isToday()) {
                    $label = $lang === 'ar' ? 'اليوم' : 'Today';
                } elseif ($loginDate->isYesterday()) {
                    $label = $lang === 'ar' ? 'أمس' : 'Yesterday';
                } else {
                    // Day name (Monday / الاثنين)
                    $label = $loginDate
                        ->locale($lang)
                        ->translatedFormat('l');
                }

                $data['last_login'] = formatDateTime($loginDate, $lang).' '.$label;
            } else {
                $data['last_login'] = null;
            }
            $activities = Activity::where('causer_type', \App\Models\Employee::class)
                ->where('causer_id', $this->id)
                ->whereNotIn('event', ['login', 'logout'])
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->map(function ($activity) use ($lang) {

                    $date = Carbon::parse($activity->created_at);

                    return [
                        'id'          => $activity->id,
                        'description' => $activity->description,
                        'event'       => $activity->event,
                        'subject'     => class_basename($activity->subject_type),
                        'subject_id'  => $activity->subject_id,
                        'time'        => formatDateTime($date, $lang),
                    ];
                });

            $data['recent_activities'] = $activities;
        }

        if ($module === 'inventory') {
            $data = array_merge($data, [
                'branch' => $this->branch ? [
                    'id'   => $this->branch->id,
                    'name' => $this->branch->name,
                ] : null,
                'image'    => $this->image,
                'id_photo' => $this->national_id_photo,
                'contract' => $this->contract_file,
                'warehouses' => $module === 'inventory'
                    ? $this->inventoryStores->map(function ($store) {
                        return ['id' => $store->id, 'name' => $store->name,];
                    })
                    : [],
            ]);
        }
        if ($module === 'inventory') {

            $data['is_active'] = ($this->employee_status_id == 7)
                ? false
                : (bool) $this->employee_status_id;
        } else {

            $data['is_active'] = [
                'key'   => $this->employee_status_id,
                'value' => $this->employeeStatus->name ?? null,
            ];
        }
        return $data;
    }
}

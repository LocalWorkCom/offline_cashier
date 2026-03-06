<?php

namespace App\Services\HR_Services;

use App\Models\NotificationCategory;

class NotificationService
{


    
    public function index()
    {
        return  NotificationCategory::query();
    }
    public function show($id)
    {
        return NotificationCategory::findOrFail($id);
    }
    public function store($data)
    {
        return NotificationCategory::create($data);
    }


    public function update($request, $id)
    {
        $notificationCategory = NotificationCategory::findOrFail($id);
        $notificationCategory->update($request);

        return $notificationCategory;
    }
}

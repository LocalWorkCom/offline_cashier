<?php

namespace App\Services\SettingsServices;

use App\Models\MenusIntegration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MenusIntegrationService
{

    public function index(Request $request)
    {
        // try {
            return $data = MenusIntegration::where('is_active', 1);
        // } catch (\Exception $e) {
        //     Log::error('Error fetching color: ' . $e->getMessage(), [
        //         'stack' => $e->getTraceAsString(),
        //     ]);
        //     return respondError(($this->lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }
}

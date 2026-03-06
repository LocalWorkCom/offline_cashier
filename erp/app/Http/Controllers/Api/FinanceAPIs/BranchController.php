<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $data = Branch::where('is_active', 1)->select('id', 'name_ar', 'name_en')->get();
        $branches = $data->map(function ($branch) use ($lang) {
            return[
                'id' => $branch->id,
                'name' => $lang === 'ar' ? $branch->name_ar : $branch->name_en
            ];
        });
        return ResponseWithSuccessData(request()->header('lang', 'ar'), $branches, 1);
    }

}

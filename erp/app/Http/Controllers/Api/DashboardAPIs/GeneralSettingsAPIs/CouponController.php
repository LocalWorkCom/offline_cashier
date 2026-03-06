<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Branch;
use App\Services\SettingsServices\CouponService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    protected $couponService;


    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        // try {
            $employee = auth('employee')->user();
            $query = $this->couponService->index();

            // dd($query);
            // Restrict if employee is branch manager
            if ($employee && $employee->flag === 'branch manager') {
                $branchId = $employee->branch_id;

                $query = $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            }
            $response = paginateOrGetAll($query, $request, null);
            // Transform the data to hide branch fields
            $response['data'] = CouponResource::collection(collect($response['data']))->resolve();
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error fetching coupon : ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $employee = auth('employee')->user();

            // Check if employee is a branch manager
            if ($employee && $employee->flag === 'branch manager') {
                $branchId = $employee->branch_id;
                // Only eager load their own branch (with pivot)
                $coupon = Coupon::with(['branches' => function ($query) use ($branchId) {
                    $query->where('branches.id', $branchId);
                }])->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                })->find($id);

            } else {
                // For admin or other roles, load all branches
                $coupon = Coupon::with(['branches' => function ($query) {
                    $query->select('branches.id', 'name_ar', 'name_en');
                }])->find($id);
            }
            return ResponseWithSuccessData($lang, $coupon, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            $message = $lang === 'ar' ? 'كوبون غير موجود' : 'Coupon not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $selectedBranches = $request['branches'] ?? [];
        $guard = authActionSave();
        if (
            $guard['type'] === 'employee' &&
            auth('employee')->user()->flag === 'branch manager'
        ) {
            $user = auth('employee')->user();
            $allowedBranch = $user->branch_id;

            $selectedBranches = $request['branches'] ?? [];
            if (count($selectedBranches) !== 1 || $selectedBranches[0] != $allowedBranch) {
                return CustomRespondWithBadRequest(__('coupon.must_assign_only_his_branch'));
            }
        }
        $coupon = $this->couponService->store($request, $lang);

        // If the service returned a JsonResponse (error), return it directly
        if ($coupon instanceof \Illuminate\Http\JsonResponse) {
            return $coupon;
        }

        return ResponseWithSuccessData($lang, $coupon, 1);
    }


   public function update(Request $request, $id)
{
    $lang = $request->header('lang', 'ar');
    try {
        $coupon = Coupon::find($id);
        $selectedBranches = $request['branches'] ?? [];
        $result = $this->couponService->update($request, $id, $lang);

    // If the service returned a JsonResponse (error), return it directly
    if ($result instanceof \Illuminate\Http\JsonResponse) {
        return $result;
    }

    return ResponseWithSuccessData($lang, $result, 1);
     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'كوبون غير موجود' : 'Coupon not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
}


    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $coupon = Coupon::withTrashed()->findOrFail($id);
            $coupon->restore();

            return ResponseWithSuccessData($lang, $coupon, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Increment usage of the coupon.
     */
    public function incrementUsage($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            // Check if coupon has a usage limit and if it's reached
            if ($coupon->usage_limit && $coupon->count_usage >= $coupon->usage_limit) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Coupon usage limit reached.',
                ], 200);
            }

            // Increment the count_usage
            $coupon->increment('count_usage');

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Coupon usage incremented successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error incrementing coupon usage: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'Error incrementing coupon usage.',
            ], 500);
        }
    }

    /**
     * Check if the coupon is still valid based on usage and date.
     */
    public function isCouponValid(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        if (auth('api')->check()) {
            $user_id = Auth::guard('api')->user()->id;
        } elseif (auth('employee')->check()) {
            $user_id = Auth::guard('employee')->user()->id;
        } else {
            return RespondWithBadRequest($lang, 4);
        }
        return $this->couponService->isCouponValid($request, $user_id);
    }
}

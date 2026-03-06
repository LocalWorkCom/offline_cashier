<?php


namespace App\Services;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\DeliveryComplaints;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryComplaintsService
{
    public function index(Request $request, $api = 0)
    {
        try {
            $lang = app()->getLocale();
            if ($api === 1) {
                $employee = auth('employee')->user();
                if (!$employee) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Authentication failed',
                        'code' => 401,
                        'data' => null
                    ], 401);
                }
            } else {
                $admin = auth('admin')->user();
                if (!$admin) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Authentication failed',
                        'code' => 401,
                        'data' => null
                    ], 401);
                }
            }

            App::setLocale($lang);

            // $query = DeliveryComplaints::with(['order'])->where('manage', 'admin');
            $query = DeliveryComplaints::with(['order']);

            if ($api === 1) {
                if ($employee->hasRole('Branch_Manager')) {
                    $branch_id = $employee->branch_id;
                    if ($branch_id) {
                        $query->whereHas('order', function ($q) use ($branch_id) {
                            $q->where('branch_id', $branch_id);
                        });
                    }
                }
            }

            // if ($api == 1) {
            //     $result = paginateOrGetAll($query, $request, null);
            //     $complaints = $result['data'] ?? $result;
            // } else {
            //     $complaints = $query->get();
            // }

            if ($api == 1) {
                $result = paginateOrGetAll($query, $request);
                $complaints = collect($result['data']);
            } else {
                $complaints = $query->get();
            }

            $transformedComplaints = $complaints->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'delivery_name' => ($complaint->employee->first_name . ' ' . $complaint->employee->last_name) ?? null,
                    'delivery_phone' => ($complaint->employee->phone_number)  ?? null,
                    'client_name' => ($complaint->order->Client->name) ?? null,
                    'client_phone' => ($complaint->order->Client->phone)  ?? null,
                    'branch_ar' => $complaint->order->branch->name_ar ?? null,
                    'branch_en' => $complaint->order->branch->name_en ?? null,
                    'branch_name' => $complaint->order->branch->name ?? null,
                    'order_num' => $complaint->order->order_number ?? null,
                    'client_id'  => $complaint->client_id ?? null,
                    'order_id'   => $complaint->order_id ?? null,
                    'employee_id' => $complaint->employee_id,
                    'message' => $complaint->message,
                    'status' => __('einvoice.' . strtolower($complaint->status)), //$complaint->status,
                    'created_by' => $complaint->created_by,
                    'modified_by' => $complaint->modified_by,
                    'reason_id' => $complaint->reason_id,
                    'employee' => [
                        'id' => $complaint->employee->id,
                        'employee_status_id' => $complaint->employee->employee_status_id,
                        'ethnic_background_id' => $complaint->employee->ethnic_background_id,
                        'area_id' => $complaint->employee->area_id,
                        'country_id' => $complaint->employee->country_id,
                        'city_id' => $complaint->employee->city_id,
                        'vehicle_id' => $complaint->employee->vehicle_id,
                        'supervisor_id' => $complaint->employee->supervisor_id,
                        'branch_id' => $complaint->employee->branch_id,
                        'is_biometric' => $complaint->employee->is_biometric,
                        'biometric_id' => $complaint->employee->biometric_id,
                        'flag' => $complaint->employee->flag,
                        'image' => $complaint->employee->image,
                        'first_name_en' => $complaint->employee->first_name_en,
                        'last_name_en' => $complaint->employee->last_name_en,
                        'device_token' => $complaint->employee->device_token,
                        'payment_type_id' => $complaint->employee->payment_type_id,
                        'payment_frequency_id' => $complaint->employee->payment_frequency_id,
                        'is_first_login' => $complaint->employee->is_first_login,
                        'kitchen_info' => $complaint->employee->kitchen_info ?? []
                    ]
                ];
            });

            // Direct response without helper
            return response()->json([
                'status' => true,
                'message' => 'Request successful',
                'code' => 200,
                'data' => $transformedComplaints
            ], 200);
        } catch (\Exception $e) {
            Log::error('Complaints error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'code' => 500,
                'data' => null
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id, $api = 0)
    {
        try {
            $lang = app()->getLocale();
            if ($api === 1) {
                $employee = auth('employee')->user();
                if ((!$employee)) {
                    return RespondWithBadRequest($lang, 4);
                }
            } else {
                $admin = auth('admin')->user();

                if ((!$admin)) {
                    return RespondWithBadRequest($lang, 4);
                }
            }

            App::setLocale($lang);

            $complaints = DeliveryComplaints::with(['employee', 'order.branch'])->find($id);
            if (!$complaints) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }            
            $data = [
                'employee_first_name' => $complaints->employee?->first_name,
                'employee_last_name' => $complaints->employee?->last_name,
                'employee_phone' => $complaints->employee?->phone_number,
                'customer_name' => $complaints->order?->client_name,
                'customer_phone' => $complaints->order?->client_phone,
                'order_number' => $complaints->order?->order_number,
                'branch_name' => $complaints->order?->Branch?->name,
                'message' => $complaints->message,
                'status' => $complaints->status,
            ];
            
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Throwable $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, $api = 0)
    {
        try {
            $lang = app()->getLocale();

            // Authentication check
            if ($api === 1) {
                $employee = auth('employee')->user();
                if (!$employee) {
                    return RespondWithBadRequest($lang, 4);
                }
                $deletedBy = $employee->id;
            } else {
                $admin = auth('admin')->user();
                if (!$admin) {
                    return RespondWithBadRequest($lang, 4);
                }
                $deletedBy = $admin->id;
            }

            App::setLocale($lang);

            // Find the record (including soft-deleted ones if needed)
            $complaint = DeliveryComplaints::find($id);

            if (!$complaint) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // Update deleted_by and soft delete
            $complaint->update(['deleted_by' => $deletedBy]);
            $complaint->delete(); // This sets deleted_at (soft delete)

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function changeStatus(Request $request, $id, $api = 0)
    {
        try {
            $lang = app()->getLocale();
            if ($api === 1) {
                $employee = auth('employee')->user();
                if ((!$employee)) {
                    return RespondWithBadRequest($lang, 4);
                }
            } else {
                $admin = auth('admin')->user();

                if ((!$admin)) {
                    return RespondWithBadRequest($lang, 4);
                }
            }

            App::setLocale($lang);
            $complaint = DeliveryComplaints::find($id);
            if (!$complaint) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $validator = Validator::make($request->all(), [
                "status" => "required|in:hold,done",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }



            $complaint->status = $request->status;
            $complaint->save();

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
}

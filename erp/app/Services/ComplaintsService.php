<?php


namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ComplaintsService
{
    public function index(Request $request, $api = 0)
    {
        try {
            $lang = app()->getLocale();

            // Authentication check
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

            // Base query with eager loading
            $query = Complaint::with([
                'client',
                'order.branch'
            ])->where('manage', 'admin');

            // Branch filtering
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

            // Get data
            if ($api == 1) {
                $result = paginateOrGetAll($query, $request, null);
                $complaints = $result['data'] ?? $result;
            } else {
                $complaints = $query->get();
            }

            // Transform data
            $transformedComplaints = $complaints->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'name' => $complaint->client->name ?? null,
                    'phone' => $complaint->client->phone ?? null,
                    'branch' => $complaint->order->branch->name ?? null,
                    'order_num' => $complaint->order->order_number ?? null,
                    'rate' => $complaint->rate ?? null,
                    'complain'   => $complaint->complain ?? null,
                    'comment'    => $complaint->comment ?? null,
                    'status'     => __('complaints.'.$complaint->status) ?? null,
                    'manage'     => $complaint->manage ?? null,
                    'client_id'  => $complaint->client_id ?? null,
                    'order_id'   => $complaint->order_id ?? null,


                ];
            });

            if ($api == 1) {
                $responseData['data'] = $transformedComplaints->toArray();
                $responseData['meta'] = $result['meta'];
            }
            else {
                $responseData = $transformedComplaints;
            }

            // Direct response without helper
            return response()->json([
                'status' => true,
                'message' => 'Request successful',
                'code' => 200,
                'data' => $responseData
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
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "client_id" => "required|exists:users,id",
                "order_id" => "required|exists:orders,id",
                "rate" => "nullable|in:1,2,3,4,5",
                "status" => "required|in:pending,inprogress,solved",
                "complain" => "required|string",
                "comment" => "nullable|string",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            //            dd(Employee::find(auth('employee')->user()->id)->user_id);
            $complaint = Complaint::create(
                $validator->validated()
                    + [
                        'created_by' => Employee::find(auth('employee')->user()->id)->user_id,
                    ]
            );

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id, $api = 0)
    {
        try {
            $lang = app()->getLocale();

            // Authentication
            if ($api === 1) {
                $employee = auth('employee')->user();
                if (!$employee) {
                    return RespondWithBadRequest($lang, 4);
                }
            } else {
                $admin = auth('admin')->user();
                if (!$admin) {
                    return RespondWithBadRequest($lang, 4);
                }
            }

            App::setLocale($lang);

            // Load complaint with relations
            $complaint = Complaint::with(['client', 'order.branch'])->find($id);

            if (!$complaint) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // Transform data
            $transformedComplaint = [
                'id'        => $complaint->id,
                'name'      => $complaint->client->name ?? null,
                'phone'     => $complaint->client->phone ?? null,
                'branch'    => $complaint->order->branch->name ?? null,
                'order_num' => $complaint->order->order_number ?? null,
                'rate'      => $complaint->rate ?? null,
                'complain'  => $complaint->complain ?? null,
                'comment'   => $complaint->comment ?? null,
                'status'    => __('complaints.'.$complaint->status) ?? null,
                'manage'    => $complaint->manage ?? null,
                'client_id' => $complaint->client_id ?? null,
                'order_id'  => $complaint->order_id ?? null,
                'order' => $complaint->order ?? null,
                'client' => $complaint->client ?? null,
            ];

            // Response
            return response()->json([
                'status'  => true,
                'message' => 'Request successful',
                'code'    => 200,
                'data'    => $transformedComplaint
            ], 200);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 500); // Server error
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
            } else {
                $admin = auth('admin')->user();
                if (!$admin) {
                    return RespondWithBadRequest($lang, 4);
                }
            }

            App::setLocale($lang);

            $complaint = Complaint::find($id);

            // Check if complaint exists
            if (!$complaint) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // Soft delete the complaint
            $complaint->deleted_by = $admin->id ?? null;
            $complaint->save();
            $complaint->delete();

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 500); // Use 500 for server errors
        }
    }
    public function addComment(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "complaint_id" => "required|exists:complaints,id",
                "comment" => "required|string",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $complaint = Complaint::find($request->complaint_id);

            if ($complaint->comment != null) {
                return respondErrorData($lang == 'en' ? 'Duplicated comment.' : 'تعليق مكرر.', 400, $lang == 'en' ? ['Comment sent before.'] : ['تم ارسال تعليق من قبل.']);
            }

            $complaint->comment = $request->comment;
            $complaint->save();

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function changeStatus(Request $request, $id,  $api = 0)
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
            $complaint = Complaint::find($id);

            if (!$complaint) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $validator = Validator::make($request->all(), [
                "status" => "required|in:pending,inprogress,solved",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $complaint->status = $request->status;
            $complaint->save();
            $complaint->status = __('complaints.'.$complaint->status) ?? null;
            

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
}

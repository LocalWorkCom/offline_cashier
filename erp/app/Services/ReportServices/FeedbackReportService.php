<?php


namespace App\Services\ReportServices;

use App\Models\Branch;
use App\Models\Complaint;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class FeedbackReportService
{
    public function index(Request $request)
    {
        try {
            $lang = app()->getLocale();

            // Get all filter parameters
            $status = $request->input('status', 'all');
            $branch = $request->input('branch', 'all');
            $dateFrom = $request->input('date_from', null);
            $dateTo = $request->input('date_to', null);
            $orderNum = $request->input('order_num', null);
            $phone = $request->input('phone', null);
            $rating = $request->input('rating', 'all');
            $type = $request->input('type', 'all');
            $manage = $request->input('manage', 'all');
            $invoiceNum = $request->input('invoice_num', null);

            // Build the query
            $complaints = Complaint::with(['client', 'order.branch'])->orderBy('created_at', 'desc');

            // Status filter
            if ($status !== 'all') {
                $complaints = $complaints->where('status', $status);
            }

            // Branch filter
            if ($branch !== 'all') {
                $complaints = $complaints->whereHas('order', function ($query) use ($branch) {
                    $query->where('branch_id', $branch);
                });
            }

            // Date range filter
            if ($dateFrom) {
                $complaints = $complaints->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $complaints = $complaints->whereDate('created_at', '<=', $dateTo);
            }

            // Order number filter

            if ($orderNum) {
                $complaints = $complaints->whereHas('order', function ($query) use ($orderNum) {
                    $query->where('order_number', 'like', '%' . $orderNum . '%');
                });
            }
            // Phone filter
            if ($phone) {
                $complaints = $complaints->whereHas('client', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                });
            }

            // Rating filter
            if ($rating !== 'all') {
                $complaints = $complaints->where('rate', $rating);
            }

            // Order type filter
            if ($type !== 'all') {
                $complaints = $complaints->whereHas('order', function ($query) use ($type) {
                    $query->where('type', $type);
                });
            }

            // Managed by filter
            if ($manage !== 'all') {
                $complaints = $complaints->where('manage', $manage);
            }

            // Invoice number filter
            if ($invoiceNum) {
                $complaints = $complaints->whereHas('order', function ($query) use ($invoiceNum) {
                    $query->where('invoice_number', 'like', '%' . $invoiceNum . '%');
                });
            }

            // Get status counts for totals
            $pendingCount = Complaint::where('status', 'pending')->count();
            $inprogressCount = Complaint::where('status', 'inprogress')->count();
            $solvedCount = Complaint::where('status', 'solved')->count();

            // Apply pagination
            $paginatedResult = paginateOrGetAll($complaints, $request);

            // Format the response data
            if (isset($paginatedResult['data'])) {
                $paginatedResult['data'] = collect($paginatedResult['data'])->map(function ($complaint) use ($lang) {
                    return [
                        'id' => $complaint->id,
                        'name' => $complaint->client->name ?? '',
                        'phone' => $complaint->client->phone ?? '',
                        'branch' => [
                            'id' => $complaint->order->branch->id ?? null,
                            'name_ar' => $complaint->order->branch->name_ar ?? '',
                            'name_en' => $complaint->order->branch->name_en ?? ''
                        ],
                        'rate' => $complaint->rate ?? null,
                        'order_num' => $complaint->order->order_number ?? '',
                        'invoice_number' => $complaint->order->invoice_number ?? '',
                        'status' => $complaint->status,
                        'manage' => $complaint->manage,
                        'order_type' => $complaint->order->type ?? '',
                        'created_at' => $complaint->created_at,
                        'complain' => $complaint->complain,
                        'comment' => $complaint->comment,
                        'client_id' => $complaint->client_id,
                        'order_id' => $complaint->order_id,
                    ];
                })->all();
            }

            // Add status-based totals to the response
            $paginatedResult['totals'] = [
                'pending' => $pendingCount,
                'inprogress' => $inprogressCount,
                'solved' => $solvedCount,
            ];

            return ResponseWithSuccessDataPaginated($lang, $paginatedResult, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
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

    public function show(Request $request, string $id)
    {
        try {
            $lang = app()->getLocale();

            $complaint = Complaint::with(['client', 'order.branch'])->find($id);

            // if (!$complaint) {
            //     return respondError(__('complaint.not_found'), 404);
            // }

            // Prepare the response data
            $complaints = [
                'id' => $complaint->id,
                'client_name' => $complaint->client->name ?? '',
                'client_phone' => $complaint->client->phone ?? '',
                'rate' => $complaint->rate ?? null,
                'order_number' => $complaint->order->order_number ?? '',
                'invoice_number' => $complaint->order->invoice_number ?? '',
                'branch' => [
                    'name_ar' => $complaint->order && $complaint->order->branch
                        ? $complaint->order->branch->name_ar
                        : '',
                    'name_en' => $complaint->order && $complaint->order->branch
                        ? $complaint->order->branch->name_en
                        : ''
                ],
                'branch_lang' => $complaint->order && $complaint->order->branch
                    ? $complaint->order->branch->name
                    : '',
                'status' => $complaint->status,
                'status_lang' => __('complaints.' . $complaint->status) ?? '',
                'complain' => $complaint->complain ?? '',
                'comment' => $complaint->comment ?? '',
                'created_at' => $complaint->created_at?->format('Y-m-d'),
                'updated_at' => $complaint->updated_at?->format('Y-m-d')
            ];

            return ResponseWithSuccessData($lang, $complaints, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $complaints = Complaint::find($id);
            $complaints->delete();
            $complaints->deleted_by = $admin->id ?? null;
            $complaints->save();
            return ResponseWithSuccessData($lang, $complaints, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
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

    public function changeStatus(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "status" => "required|in:pending,inprogress,solved",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $complaint = Complaint::find($id);

            $complaint->status = $request->status;
            $complaint->save();

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
}

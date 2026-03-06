<?php

namespace App\Services\HR_Services;

use App\Models\Employee;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Models\PerformanceReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PerformanceReviewService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $user = auth()->user();

        // HR Managers get all reviews with filtering options
        if ($user->hasRole('HR_Manager')) {
            $reviews = PerformanceReview::query()
                ->with(['employee'])
                ->when($request->has('employee_id'), function ($q) use ($request) {
                    $q->where('employee_id', $request->employee_id);
                })
                ->when($request->has('rating'), function ($q) use ($request) {
                    $q->where('rating', $request->rating);
                })
                ->get();

            return [
                'my' => $reviews->where('employee_id', $user->id)->values(),
                'employees' => $reviews->where('employee_id', '!=', $user->id)->values()
            ];
        }

        // Supervisors get reviews for their supervised employees + their own
        $supervisedEmployees = getSupervisedEmployees($user->id);
        if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
            $employeeIds = $supervisedEmployees->pluck('id')->toArray();

            $reviews = PerformanceReview::query()
                ->with(['employee'])
                ->where(function ($query) use ($user, $employeeIds, $request) {
                    $query->where('employee_id', $user->id)
                        ->orWhereIn('employee_id', $employeeIds);

                    // 🔹 Apply filters inside this group
                    if ($request->has('employee_id')) {
                        $query->where('employee_id', $request->employee_id);
                    }
                    if ($request->has('rating')) {
                        $query->where('rating', $request->rating);
                    }
                })
                ->get();

            return [
                'my' => $reviews->where('employee_id', $user->id)->values(),
                'employees' => $reviews->where('employee_id', '!=', $user->id)->values()
            ];
        }

        // Regular users only get their own reviews
        $reviews = PerformanceReview::query()
            ->with(['employee'])
            ->where('employee_id', $user->id)
            ->when($request->has('rating'), function ($q) use ($request) {
                $q->where('rating', $request->rating);
            })
            ->get();

        return [
            'my' => $reviews,
            'employees' => collect([])
        ];
    }


    public function store(Request $request)
    {
        $user = auth()->user();

        $validateData = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
            'rating' => 'required|integer|min:1',
            'strengths' => 'required',
            'weaknesses' => 'required',
            'additional_comments' => 'required'
        ]);

        if ($validateData->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validateData->errors(),
                'validation_type' => true
            ], 400);
        }

        // 🔹 Restrict supervisors to their supervised employees
        $supervisedEmployees = getSupervisedEmployees($user->id);
        if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
            if (!$supervisedEmployees || !$supervisedEmployees->pluck('id')->contains($request->employee_id)) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'message' => 'You are not authorized to review this employee.',
                    'data' => null,
                    'errorData' => null
                ], 403);
            }
        }

        // 🔹 HR Managers or Admins can review anyone
        // (no restriction needed)

        $performanceReview = new PerformanceReview();
        $performanceReview->employee_id = $request->employee_id;
        $performanceReview->rating = $request->rating;
        $performanceReview->strengths = $request->strengths;
        $performanceReview->weaknesses = $request->weaknesses;
        $performanceReview->additional_comments = $request->additional_comments;
        $performanceReview->created_by = authActionSave()['by'];
        $performanceReview->created_by_type = authActionSave()['type'];
        $performanceReview->save();

        // 🔹 Get the employee
        $employee = Employee::find($request->employee_id);

        // 🔹 Prepare notification results
        $notificationResult = null;
        $fullUrl = url()->current();
        $apiBaseUrl = Str::before($fullUrl, '/api');
        $url = $apiBaseUrl . '/performance-reviews/' . $performanceReview->id;

        if ($employee) {
            $notificationSent = false;
            $requestId = $performanceReview->id;

            // Send push notification (if device token exists)
            if (!empty($employee->device_token)) {
                $notification = send_push_notification(
                    $employee->device_token,
                    "تمت إضافة تقييم أداء جديد لك مع تقييم {$performanceReview->rating}",
                    "A new performance review has been added for you with rating {$performanceReview->rating}",
                    'تقييم أداء جديد',
                    'New Performance Review',
                    'employee',
                    $employee->id,
                    authActionSave()['by'],
                    $requestId,
                    app()->getLocale(),
                    7,
                    $url
                );

                $notificationSent = $notification !== false;
            }

            $notificationResult = [
                'user_id' => $employee->id,
                'notification_sent' => $notificationSent,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Performance review created successfully.',
            'code' => 200,
            'data' => [
                'performance_review' => $performanceReview,
                'notification' => $notificationResult
            ]
        ], 200);
    }


    public function report(Request $request)
    {
        $user = auth()->user();

        $employeeId = $request->input('employee_id');
        $rating = $request->input('rating');
        $employeeName = $request->input('employee_name');
        $departmentId = $request->input('department_id');

        $from = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))->startOfDay()
            : null;
        $to = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))->endOfDay()
            : null;

        $query = PerformanceReview::with('employee');

        // ✅ HR Manager logic
        if ($user->hasRole('HR_Manager')) {
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            // 🔍 Filter by employee name
            if ($employeeName) {
                $query->whereHas('employee', function ($q) use ($employeeName) {
                    $nameParts = explode(' ', trim($employeeName));
                    if (count($nameParts) === 2) {
                        $q->where(function ($subQuery) use ($nameParts) {
                            $subQuery->where('first_name', 'LIKE', "%{$nameParts[0]}%")
                                ->where('last_name', 'LIKE', "%{$nameParts[1]}%");
                        })->orWhere(function ($subQuery) use ($nameParts) {
                            $subQuery->where('first_name', 'LIKE', "%{$nameParts[1]}%")
                                ->where('last_name', 'LIKE', "%{$nameParts[0]}%");
                        });
                    } else {
                        $q->where(function ($subQuery) use ($employeeName) {
                            $subQuery->where('first_name', 'LIKE', "%{$employeeName}%")
                                ->orWhere('last_name', 'LIKE', "%{$employeeName}%");
                        });
                    }
                });
            }

            // 🏢 Department filter
            if ($departmentId) {
                $query->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }
        }

        // ✅ Supervisor logic (based on whether they actually supervise employees)
        else {
            $supervisedEmployees = getSupervisedEmployees($user->id);

            if ($supervisedEmployees && $supervisedEmployees->count() > 0) {
                $employeeIds = $supervisedEmployees->pluck('id')->toArray();

                $query->where(function ($q) use ($user, $employeeIds, $employeeId) {
                    $q->where('employee_id', $user->id)
                        ->orWhereIn('employee_id', $employeeIds);

                    // Apply employee_id filter inside this group
                    if ($employeeId) {
                        $q->where('employee_id', $employeeId);
                    }
                });

                // 🔍 Employee name filter
                if ($employeeName) {
                    $query->whereHas('employee', function ($q) use ($employeeName) {
                        $nameParts = explode(' ', trim($employeeName));
                        if (count($nameParts) === 2) {
                            $q->where(function ($subQuery) use ($nameParts) {
                                $subQuery->where('first_name', 'LIKE', "%{$nameParts[0]}%")
                                    ->where('last_name', 'LIKE', "%{$nameParts[1]}%");
                            })->orWhere(function ($subQuery) use ($nameParts) {
                                $subQuery->where('first_name', 'LIKE', "%{$nameParts[1]}%")
                                    ->where('last_name', 'LIKE', "%{$nameParts[0]}%");
                            });
                        } else {
                            $q->where(function ($subQuery) use ($employeeName) {
                                $subQuery->where('first_name', 'LIKE', "%{$employeeName}%")
                                    ->orWhere('last_name', 'LIKE', "%{$employeeName}%");
                            });
                        }
                    });
                }

                // 🏢 Department filter
                if ($departmentId) {
                    $query->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                }
            } else {
                // Regular employee — only see their own reviews
                $query->where('employee_id', $user->id);
            }
        }

        // ⭐ Common filters
        if ($rating) {
            $query->where('rating', $rating);
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $query->where('created_at', '>=', $from);
        } elseif ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query->orderByDesc('created_at');
    }
}

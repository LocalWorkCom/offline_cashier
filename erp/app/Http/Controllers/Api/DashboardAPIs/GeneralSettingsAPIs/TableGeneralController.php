<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Order;
use App\Models\Table;
use App\Events\TableStatus;
use Illuminate\Http\Request;
use App\Models\FloorPartition;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\SettingsServices\TableService;

class TableGeneralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }
    public function index(Request $request)
    {
        // try {
        $lang = $request->header('lang', 'en');

        // Get pagination parameters from headers
        $perPage = $request->header('per-page');
        $page = $request->header('page', 1);

        // Check for employee authentication
        $employee = auth('employee')->user();


        $query = Table::query();

        // Apply status filter if provided
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $flags = ['waiter', 'cashier', 'customer_service', 'branch manager'];
        // Add branch manager filter
        if (in_array($employee->flag, $flags)) {
            $branch_id = $employee->branch_id;

            if ($branch_id) {

                $query->with(['floors', 'floorPartitions', 'branches'])->where('branch_id', $branch_id)->whereHas('floors.branches', function ($query) use ($branch_id) {
                    $query->where('branch_id', $branch_id);
                });
            }
        } else {
            if ($request->filled('branch_id')) {
                $branch_id = $request->branch_id;

                $query->with('floors','branches', 'floorPartitions')
                    ->whereHas('floors', function ($q) use ($branch_id) {
                        $q->where('branch_id', $branch_id);
                    });
            }
            else
            {
                $query->with( 'floors','branches', 'floorPartitions');
            }
        }
        $response = paginateOrGetAll($query, $request, null);

        $response['data']->transform(function ($item) use ($lang) {
            $item->status_name = $item->status == 1
                ? ($lang == 'en' ? 'active' : 'نشط')
                : ($lang == 'en' ? 'inactive' : 'غير نشط');
            $item->type_name = $item->type == 1
                ? ($lang == 'en' ? 'in door' : 'داخلية')
                : ($lang == 'en' ? 'out door' : 'خارجية');
            $item->smoking_name = $item->smoking == 1
                ? ($lang == 'en' ? 'smoking' : 'مدخنة')
                : ($lang == 'en' ? 'no smoking' : 'غير مدخنة');
            $item->available_status_name = $item->Available_Status == true
                ? ($lang == 'en' ? 'Available' : 'متاحة')
                : ($lang == 'en' ? 'Occupied' : 'مشغول');
            return $item;
        });

         // return  $response;
        foreach ($response['data'] as $table) {
            if($table->Available_Status == false){
                $orderId = Order::where('table_id', $table->id)
                ->orderBy('id', 'desc')
                ->first()?->id;
                $table->order_id = $orderId; 
            }
            else{
                $table->order_id = null;
            }
        }


        return ResponseWithSuccessDataPaginated($lang, $response, 1);

        // } catch (\Exception $e) {
        //     return RespondWithBadRequestData($lang, 2); // Server error
        // }
    }

    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'floor_id' => 'required|integer|exists:floors,id',
                'floor_partition_id' => 'required|integer|exists:floor_partitions,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1',
                'smoking' => 'required|integer|min:1',
                'table_number' => 'required|integer',
                'capacity' => 'required|integer|min:1',
                'online' => 'required|boolean'
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


            $check_tabel_number = Table::where('table_number', $request->table_number)->first();
            if ($check_tabel_number) {
                return RespondWithBadRequestData($lang, 9);
            }

            $check_floor_partition = FloorPartition::where('id', $request->floor_partition_id)->first();
            if ($check_floor_partition->capacity <= $check_floor_partition->exist_table) {
                return RespondWithBadRequestNotAdd($lang, 9);
            }
            // $user_id = Auth::guard('api')->user()->id;
            $table = new Table();
            $table->floor_id = $request->floor_id;
            $table->floor_partition_id = $request->floor_partition_id;
            $table->name_ar = $request->name_ar;
            $table->name_en = $request->name_en; // Fixed this line
            $table->table_number = $request->table_number;
            $table->type = $request->type;
            $table->smoking = $request->smoking;
            $table->capacity = $request->capacity;
            $table->status = $request->status;

            $table->created_by = authActionSave()['by'];
            $table->created_by_type = authActionSave()['type'];
            $table->save();

            return ResponseWithSuccessData($lang, $table, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Table::where('id', $request->id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:tables,id',
                'floor_partition_id' => 'required|integer|exists:floor_partitions,id',
                'floor_id' => 'required|integer|exists:floors,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1',
                'smoking' => 'required|integer|min:1',
                'table_number' => 'required|integer|min:1',
                'capacity' => 'required|integer|min:1'
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


            $check_tabel_number = Table::where('table_number', $request->table_number)->where('id', '!=', $request->id)->first();
            if ($check_tabel_number) {
                return RespondWithBadRequestData($lang, 9);
            }

            $table = Table::findOrFail($request->id);
            $table->floor_id = $request->floor_id;
            $table->floor_partition_id = $request->floor_partition_id;
            $table->name_ar = $request->name_ar;
            $table->name_en = $request->name_en; // Fixed this line
            $table->table_number = $request->table_number;
            $table->type = $request->type;
            $table->smoking = $request->smoking;
            $table->capacity = $request->capacity;
            $table->status = $request->status;

            $table->modified_by = authActionSave()['by'];
            $table->modified_by_type = authActionSave()['type'];
            $table->save();

            return ResponseWithSuccessData($lang, $table, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Table::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $table = Table::find($request->id);
            if (!$table) {
                return  RespondWithBadRequestNotExist();
            }
            // Check only orders created today
            $hasOrdersToday = $table->orders()
                ->whereDate('created_at', today())
                ->where('status', '!=', 'cancelled') // Exclude cancelled orders
                ->exists();

            if ($hasOrdersToday) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => __('validation.table_has_orders'),
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ], 400);
            }
            $table->deleted_by = authActionSave()['by'];
            $table->deleted_by_type = authActionSave()['type'];
            $table->save();

            $table->delete();

            $data =
                [
                    'id' => $table->id,
                    'name' => $table->name,
                    'name_ar' => $table->name_ar,
                    'name_en' => $table->name_en,
                    'table_number' => $table->table_number,
                    'status' => $table->status,
                    'smoking' => $table->smoking,
                    'floors' => ['id' => $table->floors->id, 'name' => $table->floors->name, 'name_ar' => $table->floors->name_ar, 'name_en' => $table->floors->name_en],
                    'floor_partitions' => ['id' => $table->floorPartitions->id, 'name' => $table->floorPartitions->name, 'name_ar' => $table->floorPartitions->name_ar, 'name_en' => $table->floorPartitions->name_en]
                ];
            $notifyData =
                [
                    'notification_type' => 'table',
                    'description_ar' => 'تم حذف علي طاولة بالفرع',
                    'description_en' => ' table deleted in your branch',
                    'title_ar' => 'تم حذف طاولة',
                    'title_en' => 'Table deleted',
                    'created_by' => $table->deleted_by,
                    'order_id' => $table->id
                ];
            runNotificationToEmployees($table->branch_id, $notifyData, $table->deleted_by, $table->id, 'ar');
            broadcast(new TableStatus($data, $table->branch_id, 'delete'));
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Table::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $table = Table::with([
                'floors:id,name_ar,name_en',
                'floorPartitions:id,name_ar,name_en',
                'branches:id,name_ar,name_en'
            ])->findOrFail($id);

            return ResponseWithSuccessData($lang, $table, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}

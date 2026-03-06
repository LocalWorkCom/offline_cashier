<?php


namespace App\Services\SettingsServices;

use App\Events\TableAdded;
use App\Events\TableStatus;
use App\Models\FloorPartition;
use App\Models\Floor;
use App\Models\Table;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TableService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        try {
            if (isset($request->lang)) {
                $langs = $request->lang;
            } else {
                $langs = $this->lang;
            }
            //$tables = Table::with(['floors','floorPartitions'])->get();
            $tables = Table::query();
            if ($request->status) {
                $tables = $tables->where('status', $request->status);
            }
            if ($request->branch_id) {
                $tables = $tables->where('branch_id', $request->branch_id);
            }
            $tables = $tables->get();
            return ResponseWithSuccessData($langs, $tables, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function show($id)
    {
        try {
            $table = Table::with(['floors', 'floorPartitions'])->findOrFail($id);
            $table->makeHidden(['name'])->makeVisible(['name_ar', 'name_en']);
            return ResponseWithSuccessData($this->lang, $table, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function show_all($floor_id, $type)
    {
        try {
            if ($type == "floor") {
                $tables = Table::where('floor_id', $floor_id)->with(['floors', 'floorPartitions'])->get();
            } else {
                $tables = Table::where('floor_partition_id', $floor_id)->with(['floors', 'floorPartitions'])->get();
            }
            return ResponseWithSuccessData($this->lang, $tables, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching branches: ' . $e->getMessage());
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'floor_id' => 'required|integer|exists:floors,id',
                'floor_partition_id' => 'required|integer|exists:floor_partitions,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1',
                'smoking' => 'required|integer|min:1',
                'table_number' => 'required|integer',
                'capacity' => 'required|integer|min:1',
                'online' => 'required|boolean' // Added validation for online
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            // First get the branch_id from the floor
            $check_floor = Floor::where('id', $request->floor_id)->first();
            if (!$check_floor->branches) {
                return RespondWithBadRequestNotAdd($this->lang, 9);
            }
            $branch_id = $check_floor->branches->id;

            // Now check table number with the known branch_id
            $check_tabel_number = Table::where('table_number', $request->table_number)
                ->where('branch_id', $branch_id)
                ->first();
            if ($check_tabel_number) {
                return RespondWithBadRequestData($this->lang, 9);
            }

            $check_floor_partition = FloorPartition::where('id', $request->floor_partition_id)->first();
            if ($check_floor_partition->capacity <= $check_floor_partition->exist_table) {
                return RespondWithBadRequestNotAdd($this->lang, 9);
            }

            $user_id = Auth::guard('admin')->user()->id;
            $table = new Table();
            $table->branch_id = $branch_id;
            $table->floor_id = $request->floor_id;
            $table->floor_partition_id = $request->floor_partition_id;
            $table->name_ar = $request->name_ar;
            $table->name_en = $request->name_en;
            $table->table_number = $request->table_number;
            $table->type = $request->type;
            $table->smoking = $request->smoking;
            $table->capacity = $request->capacity;
            $table->status = $request->status;
            $table->online = $request->online; // Added online column
            $table->created_by = $user_id;
            $table->save();
            $table->load(['floors:id,name_ar,name_en', 'floorPartitions:id,name_ar,name_en']);

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
                    'description_ar' => 'تم إضافة طاولة جديدة للفرع',
                    'description_en' => 'new table added to your branch',
                    'title_ar' => 'تمت إضافة طاولة جديدة',
                    'title_en' => 'New table added',
                    'created_by' => $user_id,
                    'order_id' => $table->id
                ];
            runNotificationToEmployees($branch_id, $notifyData, $user_id, $table->id, 'ar');
            broadcast(new TableAdded($data, $branch_id));
            return ResponseWithSuccessData($this->lang, $table, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'floor_partition_id' => 'required|integer|exists:floor_partitions,id',
                'floor_id' => 'required|integer|exists:floors,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1',
                'smoking' => 'required|integer|min:1',
                'table_number' => 'required|integer|min:1',
                'capacity' => 'required|integer|min:1',
                'online' => 'required|boolean' // Added validation for online
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }
            // First get the branch_id from the floor
            $check_floor = Floor::where('id', $request->floor_id)->first();
            if ($check_floor->branches) {
                $branch_id = $check_floor->branches->id;
            } else {
                return RespondWithBadRequestNotAdd($this->lang, 9);
            }
            $branch_id = $check_floor->branches->id;

            $check_tabel_number = Table::where('table_number', $request->table_number)
                ->where('branch_id', $branch_id)
                ->where('id', '!=', $id)
                ->first();
            if ($check_tabel_number) {
                return RespondWithBadRequestData($this->lang, 9);
            }


            $user_id = Auth::guard('admin')->user()->id;
            $table = Table::findOrFail($id);
            $table->branch_id = $branch_id;
            $table->floor_id = $request->floor_id;
            $table->floor_partition_id = $request->floor_partition_id;
            $table->name_ar = $request->name_ar;
            $table->name_en = $request->name_en;
            $table->table_number = $request->table_number;
            $table->type = $request->type;
            $table->smoking = $request->smoking;
            $table->capacity = $request->capacity;
            $table->status = $request->status;
            $table->online = $request->online; // Added online column
            $table->modified_by = $user_id;
            $table->save();
            $table->load(['floors:id,name_ar,name_en', 'floorPartitions:id,name_ar,name_en']);

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
                    'description_ar' => 'تم تعديل علي طاولة بالفرع',
                    'description_en' => ' table updated in your branch',
                    'title_ar' => 'تم تعديل طاولة',
                    'title_en' => 'Table updated',
                    'created_by' => $user_id,
                    'order_id' => $table->id
                ];
            runNotificationToEmployees($branch_id, $notifyData, $user_id, $table->id, 'ar');
            broadcast(new TableStatus($data, $branch_id, 'updated'));

            return ResponseWithSuccessData($this->lang, $table, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }


    public function delete(Request $request, $id)
    {
        try {
            $user_id =  Auth::guard('admin')->user()->id;

            $table = Table::find($request->id);
            if (!$table) {
                return  RespondWithBadRequestNotExist();
            }

            if ($table->orders()->exists()) {
                return RespondWithBadRequestNotHavePermeation();
            }
          
            $table->deleted_by = $user_id;
            $table->save();

            $table->delete();
            $table->load(['floors:id,name_ar,name_en', 'floorPartitions:id,name_ar,name_en']);

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
                    'created_by' => $user_id,
                    'order_id' => $table->id
                ];
            runNotificationToEmployees($table->branch_id, $notifyData, $user_id, $table->id, 'ar');
            broadcast(new TableStatus($data, $table->branch_id, 'delete'));

            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }
}

<?php


namespace App\Services\SettingsServices;

use App\Models\Branch;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FloorService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        $lang = session()->get('locale');

        try {
            $withTrashed = $request->query('withTrashed', false);
            $query = Floor::with(['floorPartitions', 'tables', 'floorPartitions.tables']);


            if ($request->has('branch_id') && $request->branch_id) {
                $query->where('branch_id', $request->branch_id);
            }
            $branches = Branch::all();
            // If user is Branch Manager, restrict to their branch
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->where('branch_id', $branch_id);
                    $branches = Branch::where('id', $branch_id)->get();
                }
            }
            $Floors = $query->get();
            $data = [
                'floors' => $Floors,
                'branches'=>$branches
            ];
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching floors: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show($id)
    {
        try {
            $floor = Floor::with(['branches', 'floorPartitions', 'tables', 'floorPartitions.tables'])->findOrFail($id);
            $floor->makeHidden(['name'])->makeVisible(['name_ar', 'name_en']);
            return ResponseWithSuccessData($this->lang, $floor, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'branch_id' => 'required|integer|exists:branches,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1',
                'smoking' => 'required|integer|min:1'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            // $user_id =  Auth::guard('admin')->user()->id;
            $floor = new Floor();
            $floor->branch_id = $request->branch_id;
            $floor->name_ar = $request->name_ar;
            $floor->name_en = $request->name_en;
            $floor->type = $request->type;
            $floor->smoking = $request->smoking;
            $floor->created_by = authActionSave()['by'];
            $floor->created_by_type = authActionSave()['type'];
            $floor->save();

            return ResponseWithSuccessData($this->lang, $floor, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'branch_id' => 'required|integer|exists:branches,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1',
                'smoking' => 'required|integer|min:1'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            // $user_id =  Auth::guard('admin')->user()->id;
            $floor = Floor::findOrFail($id);
            $floor->branch_id = $request->branch_id;
            $floor->name_ar = $request->name_ar;
            $floor->name_en = $request->name_en;
            $floor->type = $request->type;
            $floor->smoking = $request->smoking;
            $floor->modified_by = authActionSave()['by'];
            $floor->modified_by_type = authActionSave()['type'];
            $floor->save();

            return ResponseWithSuccessData($this->lang, $floor, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $user_id =  Auth::guard('admin')->user()->id;

            $floor = Floor::find($id);
            if (!$floor) {
                return  RespondWithBadRequestNotExist();
            }

            $floorPartitions = $floor->floorPartitions()->exists();
            if ($floorPartitions) {
                return  RespondWithBadRequestNoEmpty();
            }

            $floor->deleted_by = $user_id;
            $floor->save();

            $floor->delete();

            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function branch($branch_id)
    {
        try {
            $floors = Floor::with(['floorPartitions', 'tables', 'floorPartitions.tables'])->where('branch_id', $branch_id)->get();
            return ResponseWithSuccessData($this->lang, $floors, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }
}

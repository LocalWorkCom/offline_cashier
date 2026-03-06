<?php


namespace App\Services\HR_Services;

use App\Models\Position;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function getAllPositions()
    {

        return Position::with('parent', 'department', 'employees')->withCount('employees')->orderBy('employees_count', 'desc');

        // return Position::with('parent', 'department')->withCount('employees')->orderBy('employees_count', 'desc');

    }

    public function getPosition($id)
    {
        return Position::with('parent', 'department')->findOrFail($id);
    }

    public function createPosition($data)
    {
        $position = new Position();

        $position->name_ar = $data['name_ar'];
        $position->name_en = $data['name_en'];
        $position->description_ar = $data['description_ar'] ?? null;
        $position->description_en = $data['description_en'] ?? null;

        $position->department_id = $data['department_id'];
        $position->parent_id = $data['parent_id'] ?? null;
        $position->created_by = authActionSave()['by'];
        $position->created_at = now();
        $position->save();
        $position->load('parent');
        if ($position->parent) {
            $position->parent->makeHidden(['name', 'name_site']);
        }
        return $position;
    }

    public function updatePosition($data, $id)
    {

        try {
            return DB::transaction(function () use ($id, $data) {
                $position = Position::findOrFail($id);
                $position->name_ar = $data['name_ar'];
                $position->name_en = $data['name_en'];
                $position->description_ar = $data['description_ar'] ?? null;
                $position->description_en = $data['description_en'] ?? null;

                $position->department_id = $data['department_id'];
                $position->parent_id = $data['parent_id'];
                $position->modified_by = authActionSave()['by'];
                $position->updated_at = now();
                $position->save();
                $position->load('parent');
                if ($position->parent) {
                    $position->parent->makeHidden(['name', 'name_site']);
                }
                return $position;
            });
        } catch (\Exception $e) {
            Log::error('position update failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deletePosition($id)
    {
        $position = Position::findOrFail($id);     // throws if not found
        $position->deleted_by = authActionSave()['by'];
        $position->save();
        $position->delete();                       // soft delete
    }
}

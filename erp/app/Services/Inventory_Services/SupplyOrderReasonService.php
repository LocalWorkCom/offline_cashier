<?php

namespace App\Services\Inventory_Services;

use App\Models\SupplyOrderReason;
use Illuminate\Support\Facades\Auth;

class SupplyOrderReasonService
{
    public function index()
    {
        return SupplyOrderReason::query();
    }

    public function store(array $data)
    {
        $data['created_by'] = authActionSave()['by'];
        $data['created_by_type'] =  authActionSave()['type'];

        return SupplyOrderReason::create($data);
    }

    public function show($id)
    {
        return SupplyOrderReason::find($id);
    }

    public function update($reason, array $data)
    {
        // $reason = SupplyOrderReason::findOrFail($id);

        $data['updated_by'] = authActionSave()['by'];
        $data['updated_by_type'] = authActionSave()['type'];

        $reason->update($data);
        return $reason;
    }

    public function destroy($id)
    {
        $reason = SupplyOrderReason::findOrFail($id);

        $reason->deleted_by = authActionSave()['by'];
        $reason->deleted_by_type = authActionSave()['type'];
        $reason->save();
        $reason->delete();

        return true;
    }
}

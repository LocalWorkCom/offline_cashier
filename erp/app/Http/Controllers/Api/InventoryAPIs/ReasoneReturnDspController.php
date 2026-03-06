<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Services\Inventory_Services\ReasoneReturnDspervice;
use Illuminate\Http\Request;

class ReasoneReturnDspController extends Controller
{
    protected $ReasoneReturnDspervice;
    protected $lang;
    public function __construct(ReasoneReturnDspervice $ReasoneReturnDspervice)
    {
        $this->ReasoneReturnDspervice = $ReasoneReturnDspervice;
        // $this->lang = app()->getLocale();
    }

    public function index(Request $request)
    {
        return $this->ReasoneReturnDspervice->index($request);
    }
    public function show(Request $request , $id)
    {
        return $this->ReasoneReturnDspervice->show($request ,$id);
    }
    public function store(Request $request)
    {
        return $this->ReasoneReturnDspervice->store($request);
    }
    public function update(Request $request, $id)
    {
        return $this->ReasoneReturnDspervice->update($request, $id);
    }
    public function delete(Request $request, $id)
    {
        return $this->ReasoneReturnDspervice->delete($request, $id);
    }
}

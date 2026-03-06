<?php

namespace App\Services\Inventory_Services;

use App\Models\DirectSupplyPermission;
use App\Models\DirectSupplyPermissionItem;
use App\Models\DirectSupplyPermissionLog;
use App\Models\DspItemIssue;
use App\Models\PurchaseOrderHistory;
use App\Models\PurchaseRequest;
use App\Models\ReturnDsp;
use App\Models\SupplyOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DirectSupplyPermissionService
{

    protected $ReturnDspService;

    // Inject NotificationService into this service
    public function __construct(ReturnDspService $ReturnDspService)
    {
        $this->ReturnDspService = $ReturnDspService;
    }
    public function createDSP(array $data): DirectSupplyPermission
    {
        return DB::transaction(function () use ($data) {
            $pr_id =  $data['linked_pr_id'] ? PurchaseRequest::where('pr_number', $data['linked_pr_id'])->first()->id : null;
            $so_id =  $data['linked_so_id'] ? SupplyOrder::where('order_number', $data['linked_so_id'])->first()->id : null;

            $dsp = DirectSupplyPermission::create([
                'date' => $data['date'] ?? now(),
                'dsp_no' => $data['dsp_number'],
                'from_store_id' => $data['from_store_id'],
                'to_store_id' => $data['to_store_id'],
                'department' => 'Inventory',
                'linked_pr_id' => $pr_id,
                'linked_so_id' => $so_id,
                'dsp_status_id' =>  $data['status'] ?? 1,
                'qa_tester_id' => $data['qa_tester_id'] ?? authActionSave()['by'],
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
            ]);

            foreach ($data['items'] as $item) {
                $dsp->items()->create([
                    'category_id' => $item['category_id'],
                    'product_brand_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]);
            }
            // if ($data['status'] == 2) {
            //     $dsp->refresh();
            //     $dspNumber = 'DSP-' . now()->format('Ymd') . '-' . str_pad(DirectSupplyPermission::count() + 1, 4, '0', STR_PAD_LEFT);

            //     $dsp = DirectSupplyPermission::create([
            //         'date' => $data['date'] ?? now(),
            //         'dsp_no' => $dspNumber,
            //         'from_store_id' => $data['from_store_id'],
            //         'to_store_id' => $data['to_store_id'],
            //         'department' => 'Inventory',
            //         'linked_pr_id' => $pr_id,
            //         'linked_so_id' => $so_id,
            //         'dsp_status_id' => 1,
            //         'qa_tester_id' => $data['qa_tester_id'] ?? authActionSave()['by'],
            //         'created_by' => authActionSave()['by'],
            //         'created_by_type' => authActionSave()['type'],
            //     ]);

            //     foreach ($data['items'] as $item) {
            //         $dsp->items()->create([
            //             'category_id' => $item['category_id'],
            //             'product_brand_id' => $item['item_id'],
            //             'unit_id' => $item['unit_id'],
            //             'quantity' => $item['quantity'],
            //             'notes' => $item['notes'] ?? null,
            //             'created_by' => authActionSave()['by'],
            //             'created_by_type' => authActionSave()['type'],
            //         ]);
            //     }
            //     // Log creation in DirectSupplyPermissionLog
            //     DirectSupplyPermissionLog::create([
            //         'dsp_id' => $dsp->id,
            //         'action' => $data['status'],
            //         'details' => json_encode([
            //             'message' => 'Purchase order ' . ($data['status']) . ' by employee #' . authActionSave()['by'],
            //             'po_number' =>  $data['dsp_number'],
            //             'qa' =>  $data['qa_tester_id'] ??  authActionSave()['by'],
            //             'status' => $data['status'],
            //         ]),
            //         'employee_id' => authActionSave()['by'],
            //     ]);
            // }
            // Log creation in DirectSupplyPermissionLog
            DirectSupplyPermissionLog::create([
                'dsp_id' => $dsp->id,
                'action' => $data['status'],
                'details' => json_encode([
                    'message' => 'Purchase order ' . ($data['status']) . ' by employee #' . authActionSave()['by'],
                    'po_number' =>  $data['dsp_number'],
                    'qa' =>  $data['qa_tester_id'] ??  authActionSave()['by'],
                    'status' => $data['status'],
                ]),
                'employee_id' => authActionSave()['by'],
            ]);

            return $dsp;
        });
    }

    /**
     * Update DSP status.
     */
    public function updateStatus(DirectSupplyPermission $dsp, int $status): DirectSupplyPermission
    {
        // $oldStatus = $dsp->status;
        $dsp->dsp_status_id = $status;
        $dsp->modified_by = authActionSave()['by'];
        $dsp->modified_by_type = authActionSave()['type'];
        $dsp->save();
        DirectSupplyPermissionLog::create([
            'dsp_id' => $dsp->id,
            'action' => $status,
            'details' => json_encode([
                'message' => 'Purchase order ' . ($status) . ' by employee #' . authActionSave()['by'],
                'status' => $status,
            ]),
            'employee_id' => authActionSave()['by'],
        ]);
        //case of recived and added call function update inventory quantity

        return $dsp;
    }
    public function updateDSP(DirectSupplyPermission $dsp, array $data): DirectSupplyPermission
    {
        return DB::transaction(function () use ($dsp, $data) {
            // Update main DSP record
            $dsp->update([
                'dsp_status_id' => $data['status'],
                'modified_by' => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ]);

            // Update or create items
            foreach ($data['items'] as $item) {
                if (!empty($item['id'])) {
                    // Update existing item
                    $existingItem = $dsp->items()->where('id', $item['id'])->first();
                    if ($existingItem) {
                        $existingItem->update([
                            'received_unit_id' => $item['category_id'] ?? $existingItem->category_id,
                            'received_quantity' => $item['received_quantity'],
                            'has_added' => $item['unit_id'] ?? $existingItem->unit_id,
                            'notes' => $item['notes'] ?? $existingItem->notes,
                            'modified_by' => authActionSave()['by'],
                            'modified_by_type' => authActionSave()['type'],
                        ]);
                        if ($item['has_added'] != 1 && $item['has_issued'] == 1) {
                            foreach ($item['issues'] as $dspItemissue) {
                                $dspItemissue = DspItemIssue::create([
                                    'dsp_item_id' => $existingItem->id,
                                    'unit_id' => $dspItemissue['unit_id'],
                                    'issue_type_id' => $dspItemissue['issue_type_id'],
                                    'quantity' => $dspItemissue['quantity'],
                                    'created_by' => authActionSave()['by'],
                                    'created_by_type' => authActionSave()['type'],
                                ]);
                                DirectSupplyPermissionLog::create([
                                    'dsp_id' => $dsp->id,
                                    'action' => 'update item issues',
                                    'details' => json_encode([
                                        'message' => 'Direct Supply updated by employee #' . authActionSave()['by'],
                                        'dsp_no' => $dsp->dsp_no,
                                        'item_id' => $existingItem->product_brand_id,
                                        'issue_type_id' => $existingItem->issue_type_id,
                                        'quantity' => $existingItem->quantity,
                                    ]),
                                    'employee_id' => authActionSave()['by'],
                                ]);
                            }
                            $dspRENumber = 'DSP-Re-' . now()->format('Ymd') . '-' . str_pad(ReturnDsp::count() + 1, 4, '0', STR_PAD_LEFT);

                            $ReturnDsp = new ReturnDsp();
                            $ReturnDsp->dsp_id = $dsp->id;
                            $ReturnDsp->return_code = $dspRENumber;
                            $ReturnDsp->status = 'draft';
                            $ReturnDsp->returned_quantity = collect($item['issues'])->sum('quantity');
                            $ReturnDsp->created_by = authActionSave()['by'];
                            $ReturnDsp->created_by_type = authActionSave()['type'];
                            $ReturnDsp->save();
                            // $this->ReturnDspService->send_notification_to_warehouse_contact($data['lang'], $ReturnDsp);
                        }
                        DirectSupplyPermissionLog::create([
                            'dsp_id' => $dsp->id,
                            'action' => 'update quantities',
                            'details' => json_encode([
                                'message' => 'Direct Supply updated by employee #' . authActionSave()['by'],
                                'dsp_no' => $dsp->dsp_no,
                                'product_brand_id' => $existingItem->product_brand_id,
                                'received_quantity' => $existingItem->received_quantity,
                                'has_added' => $existingItem->has_added,

                            ]),
                            'employee_id' => authActionSave()['by'],
                        ]);
                    }
                }
            }

            // Log update
            DirectSupplyPermissionLog::create([
                'dsp_id' => $dsp->id,
                'action' =>  $data['status'] ?? $dsp->dsp_status_id,
                'details' => json_encode([
                    'message' => 'Direct Supply updated by employee #' . authActionSave()['by'],
                    'dsp_no' => $dsp->dsp_no,
                    'status' => $data['status'] ?? $dsp->dsp_status_id,
                ]),
                'employee_id' => authActionSave()['by'],
            ]);

            return $dsp->fresh(['items']);
        });
    }
}

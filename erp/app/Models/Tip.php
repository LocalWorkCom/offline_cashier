<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'order_id',
        'payment_method',
        'payment_amount',
        'change_amount',
        'tips_aption',
        'tip_amount',
        'bill_amount',
        'tip_specific_amount',
        'returned_amount',
        'total_with_tip',
        'menus_integration_id',
        'payment_status_menu_integration',
        'payment_method_menu_integration',
        'date',
        'created_by'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function menu_Integration()
    {
        return $this->belongsTo(MenusIntegration::class, 'menus_integration_id');
    }
}

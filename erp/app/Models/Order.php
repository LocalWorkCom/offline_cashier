<?php

namespace App\Models;

use App\Models\Tip;
use App\Models\Einvoice;
use App\Models\Employee;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'orders';

    const PRINT_STATUS_DONE = 'done';
    const PRINT_STATUS_URGENT = 'urgent';
    const PRINT_STATUS_HOLD = 'hold';
    const pending = 1;
    const inProgress = 2;
    const onHold = 3;
    const packing = 4;
    const completed = 5;
    const cancelled = 6;

    public static $statusMap = [
        self::pending => 'pending',
        self::inProgress => 'inprogress',
        self::onHold => 'onHold',
        self::packing => 'packing',
        self::cancelled => 'cancelled',
        self::completed => 'completed',
    ];

    protected $fillable = [
        'status',
        'print_count_cashier',
        'type',
        'note',
        'order_number',
        'invoice_number',
        'tax_value',
        'fees',
        'delivery_fees',
        'total_price_befor_tax',
        'total_price_after_tax',
        'client_id',
        'table_id',
        'coupon_id',
        'discount_id',
        'branch_id',
        'delivery_id',
        'cashier_id',
        'cashier_machine_id',
        'print_status',
        'modify_by',
        'coupon_value',
        'date',
        'time',
        'client_address_id',
        'created_by',
                'total_price_before_coupon',

        'make_type',
        'tax_application',
        'takeaway_pickup_time',
        'service_fees',
        'preparation_appointment',
        'waiter_id',
        'parent_id',
        'tax_percentage',
        'service_percentage'
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at',
    ];

    protected $casts = [
        'print_status' => 'string',
        'service_fees' => 'float',
    ];

    public function latestTracking()
    {
        return $this->hasOne(OrderTracking::class)->latest(); // orders by created_at DESC and limits 1
    }

    public static function getPrintStatuses()
    {
        return [
            self::PRINT_STATUS_HOLD,
            self::PRINT_STATUS_DONE,
            self::PRINT_STATUS_URGENT,
        ];
    }
    // Define relationships
    // In Order.php model
    public function Client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    public function Branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function Table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function orderDetailsWithoutCancel()
    {
        return $this->hasMany(OrderDetail::class, 'order_id')
            ->where('status', '!=', 'cancel')
            ->where('in_request_return', 0);
    }

    public function waiter()
    {
        return $this->belongsTo(Employee::class, 'waiter_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Employee::class, 'delivery_id');
    }

    public function make_type()
    {
        return $this->belongsTo(order::class, 'waiter');
    }


    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

    public function tracking()
    {
        return $this->hasMany(OrderTracking::class);
    }
    public function orderTransactions()
    {
        return $this->hasMany(OrderTransaction::class);
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
    public function orderAddons()
    {
        return $this->hasMany(OrderAddon::class, 'order_id', 'id');
    }
    public function address()
    {
        return $this->belongsTo(ClientAddress::class, 'client_address_id')->withTrashed();
    }
    public function einvoice()
    {
        return $this->hasOne(Einvoice::class, 'invoice_id', 'id');
    }

    public function addons()
    {
        return $this->hasMany(OrderAddon::class, 'order_id', 'id');
    }
    public function transaction()
    {
        return $this->hasOne(OrderTransaction::class, 'order_id', 'id');
    }
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }
    public function customerService()
    {
        return $this->belongsTo(Employee::class, 'customer_service_id');
    }

    public function cashierMachine()
    {
        return $this->belongsTo(CashierMachine::class, 'cashier_machine_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    // app/Models/Order.php
    public function cancellationReasons()
    {
        return $this->hasMany(CancellationReason::class, 'order_id');
    }

    // Relationship with order_details


    // Relationship with parent order (for split/merge)
    public function parentOrder()
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    // Relationship with child orders (for split/merge)
    public function childOrders()
    {
        return $this->hasMany(Order::class, 'parent_id');
    }
    public function einvoices()
    {
        return $this->hasMany(Einvoice::class, 'order_id'); // ✅ Correct foreign key
    }

    public function deliveryComplaints()
    {
        return $this->hasOne(DeliveryComplaints::class, 'order_id');
    }

    public function feedback()
    {
        return $this->hasOne(Complaint::class, 'order_id');
    }
    public function reservation()
    {
        return $this->belongsTo(TableReservation::class);
    }
    public function waiterRequests()
    {
        return $this->hasMany(WaiterRequest::class, 'order_ids')
            ->whereRaw('JSON_CONTAINS(order_ids, CAST(id AS JSON))');
    }
    public function getResponsiblePersonAttribute()
    {
        return match ($this->make_type) {
            'waiter' => ($this->waiter->first_name ?? '') . ' ' . ($this->waiter->last_name ?? '') ?: __('employee.client_deleted'),
            'cashier' => ($this->cashier->first_name ?? '') . ' ' . ($this->cashier->last_name ?? '') ?: __('employee.client_deleted'),
            'customer_service' => ($this->customerService->first_name ?? '') . ' ' . ($this->customerService->last_name ?? '') ?: __('employee.client_deleted'),
            'app', 'site' => $this->client->name ?? __('order.client_deleted'),
            default => __('order.unknown_type'),
        };
    }
    public function getResponsiblePersonPhoneAttribute()
    {
        return match ($this->make_type) {
            'waiter' => $this->waiter->phone_number  ?? __('employee.phone_deleted'),
            'cashier' => $this->cashier->phone_number  ?? __('employee.phone_deleted'),
            'customer_service' => $this->customerService->phone_number  ?? __('employee.phone_deleted'),
            'app', 'site' => $this->client->phone  ?? __('order.phone_deleted'),
            default => __('order.unknown_type'),
        };
    }

    public function getResponsiblePersonEmailAttribute()
    {
        return match ($this->make_type) {
            'waiter' => $this->waiter->email ?? __('employee.email_deleted'),
            'cashier' => $this->cashier->email ?? __('employee.email_deleted'),
            'customer_service' => $this->customerService->email ?? __('employee.email_deleted'),
            'app', 'site' => $this->client->email ?? __('order.email_deleted'),
            default => __('order.unknown_type'),
        };
    }
    public function getResponsiblePersonFlagAttribute()
    {
        return match ($this->make_type) {
            'waiter' => $this->waiter->flag ?? __('employee.flag_deleted'),
            'cashier' => $this->cashier->flag ?? __('employee.flag_deleted'),
            'customer_service' => $this->customerService->flag ?? __('employee.flag_deleted'),
            'app', 'site' => $this->client->flag ?? __('order.flag_deleted'),
            default => __('order.unknown_type'),
        };
    }

    public function tips()
    {
        return $this->hasOne(Tip::class, 'order_id', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}

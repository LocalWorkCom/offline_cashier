<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPolicyInvoiceCount extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_policy_invoice_counts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_policy_id',
        'invoice_count',
        'created_by',
        'updated_by',
        'created_by_type',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type'

    ];

    protected $hidden = [   'created_by',
        'updated_by',
        'created_by_type',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type',
        'created_at',
        'updated_at'
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the payment policy associated with this invoice count.
     */
    public function paymentPolicy()
    {
        return $this->belongsTo(PaymentPolicies::class, 'payment_policy_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Boot the model with some event handling.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}

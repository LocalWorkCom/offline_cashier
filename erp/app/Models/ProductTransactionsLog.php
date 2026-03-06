<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransactionsLog extends Model
{
    use HasFactory;

    protected $table = 'product_transaction_logs';

    protected $fillable = [
        'product_transaction_id',
        'model_id',
        'model_name',
        'created_by',
        'modified_by',
    ];

    /**
     * Relations
     */

    // The related product transaction
    public function productTransaction()
    {
        return $this->belongsTo(ProductTransaction::class, 'product_transaction_id');
    }

    // Created by employee
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    // Modified by employee
    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'modified_by');
    }

    /**
     * Optionally: you can define a dynamic relation for model_name/model_id
     * (for example, to load related model based on model_name)
     */
    public function relatedModel()
    {
        return $this->morphTo(__FUNCTION__, 'model_name', 'model_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableReservationLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['table_reservation_id', 'client_id', 'cashier_id', 'waiter_id', 'date', 'from', 'to', 'canceled', 'canceled_reason', 'canceled_by', 'created_by', 'modified_by', 'deleted_by', 'deleted_at', 'created_at', 'updated_at'];

    public function tableReservations()
    {
        return $this->belongsTo(TableReservation::class, 'table_reservation_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    public function waiter()
    {
        return $this->belongsTo(Employee::class, 'waiter_id');
    }
}

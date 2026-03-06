<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchDiscount extends Model
{
    use HasFactory;
    protected $table = 'branch_discount';

    public function orders()
    {
        return $this->hasMany(Order::class, 'discount_id');
    }



}

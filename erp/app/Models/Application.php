<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application  extends Model
{
    protected $fillable = ['position_id', 'file_path','status'];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}

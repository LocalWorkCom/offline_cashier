<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['seen', 'chat_id', 'receiver', 'sender', 'message']; // include 'seen'

    public function channel()
    {
        return $this->belongsTo(ChatChannel::class);
    }
    public function sender()
{
    return $this->morphTo(null, 'guard_type', 'sender');
}

public function receiver()
{
    return $this->morphTo(null, 'guard_type', 'receiver');
}
}

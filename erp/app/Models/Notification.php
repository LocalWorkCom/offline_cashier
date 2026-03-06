<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;
    protected $appends = ['description', 'title'];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',

    ];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'title_ar',
        'title_en',
        'type',
        'notify_type',
        'description_ar',
        'description_en',
        'status',
        'user_id',
        'created_by',
        'product_id',
        'date_time',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'date_time' => 'datetime',
    ];

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        return $locale == 'ar' ? $this->description_ar : $this->description_en;
    }

    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        return $locale == 'ar' ? $this->title_ar : $this->title_en;
    }

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the creator of the notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the product associated with the notification.
     */
    // public function product(): BelongsTo
    // {
    //     return $this->belongsTo(Product::class);
    // }
     public function category()
    {
        return $this->belongsTo(NotificationCategory::class, 'notify_type');
    }
}

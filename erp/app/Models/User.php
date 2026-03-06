<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes, LogsActivity, HasApiTokens, Notifiable, HasRoles;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('user');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'facebook_id',
        'country_id',
        'phone',
        'country_code',
        'is_active',
        'birth_date',
        'flag'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    // public function country()
    // {
    //     return $this->belongsTo(Country::class);
    // }

    public function employees()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class, 'id');
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function addresses()
    {
        return $this->hasMany(ClientAddress::class, 'user_id');
    }
public function activeAddresses()
{
    return $this->hasMany(ClientAddress::class, 'user_id')
                ->where('is_default', 1); // assuming default=1 means active
}

    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }
    public function rolehasAssign()
    {
        return $this->roles()->exists() ? $this->roles : 'No roles assigned';
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }
    public function createdCities()
    {
        return $this->hasMany(City::class, 'created_by');
    }

    /**
     * Get the cities updated by this user.
     */
    public function updatedCities()
    {
        return $this->hasMany(City::class, 'updated_by');
    }

    /**
     * Get the regions created by this user.
     */
    public function createdRegions()
    {
        return $this->hasMany(Area::class, 'created_by');
    }

    /**
     * Get the regions updated by this user.
     */
    public function updatedRegions()
    {
        return $this->hasMany(Area::class, 'updated_by');
    }

    public function scopeClients($query)
    {
        return $query->where('flag', 'client');
    }

    public function initiatedChats(): MorphMany
    {
        return $this->morphMany(ChatChannel::class, 'initiator');
    }

    /**
     * Get all chats where this user is the participant
     */
    public function participantChats(): MorphMany
    {
        return $this->morphMany(ChatChannel::class, 'participant');
    }

    /**
     * Get all chat messages sent by this user
     */
    public function chatMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}

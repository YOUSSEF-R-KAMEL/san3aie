<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'password',
        'role',
        'category_id',
        'area_id',
        'bio',
        'experience_years',
        'is_available',
        'avatar',
        'is_blocked',
        'is_verified',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_available' => 'boolean',
            'is_blocked' => 'boolean',
            'is_verified' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function customerRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    public function workerRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'worker_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'worker_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function verification()
    {
        return $this->hasOne(WorkerVerification::class, 'worker_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'worker_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'worker_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(WorkerFeature::class, 'worker_id');
    }
}

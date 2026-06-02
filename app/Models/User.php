<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'owner_id', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function activeCarts()
    {
        return $this->hasMany(ActiveCart::class, 'cashier_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'user_id');
    }

    public function canAccessStorageObject(int $storageObjectId): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        $storageObject = StorageObject::find($storageObjectId);
        return $storageObject && $storageObject->owner_id === $this->owner_id;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }
}
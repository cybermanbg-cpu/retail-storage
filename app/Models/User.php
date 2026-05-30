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

#[Fillable(['name', 'email', 'password', 'owner_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    // Релация към собственика
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
    
    // Релация към активните колички (като касиер)
    public function activeCarts()
    {
        return $this->hasMany(ActiveCart::class, 'cashier_id');
    }
    
    // Релация към разписките (като касиер)
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'user_id');
    }
    
    // Проверка дали може да работи в даден обект
    public function canAccessStorageObject(int $storageObjectId): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        $storageObject = StorageObject::find($storageObjectId);
        return $storageObject && $storageObject->owner_id === $this->owner_id;
    }
    
    // Проверка дали е супер администратор
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
    
    // Проверка дали е собственик
    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }
    
    // Проверка дали е управител на обект
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }
    
    // Проверка дали е касиер
    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }
}
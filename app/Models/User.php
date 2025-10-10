<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    // الحقول المسموح تعبئتها
    protected $fillable = [
        'email',
        'phone',
        'password',
        'full_name',
        'is_verified',
        'two_fa_enabled',
        'role',
        'balance',
    ];

    // الحقول المخفية عند الإرجاع في JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // تحويل الحقول تلقائيًا لأنواع محددة
    protected $casts = [
        'is_verified' => 'boolean',
        'two_fa_enabled' => 'boolean',
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
     public function deals()
    {
        return $this->hasMany(Deal::class);
    }

     public function walle()
    {
        return $this->hasMany(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}

<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (blank($customer->referral_code)) {
                $customer->referral_code = static::generateReferralCode();
            }
        });
    }

    public static function generateReferralCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    protected $fillable = [
        'name', 'email', 'phone', 'birth_date', 'password', 'address', 'customer_group_id',
        'membership_tier_id', 'points_expire_at', 'total_points', 'total_spent', 'active',
        'deposit_balance', 'credit_limit', 'outstanding_balance',
        'referral_code', 'referrer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'active' => 'boolean',
        ];
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function membershipTier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referrer_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'referrer_id');
    }
}

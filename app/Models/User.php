<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'commission_percent',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'active' => 'boolean',
        ];
    }

    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'user_outlet')->withTimestamps();
    }

    /**
     * Auto-sync kolom `role` (string lawas) ke pivot `role_user`
     * agar tidak divergen: role=owner tanpa pivot '*' = kehilangan
     * semua akses outlet. Hook ini menjamin pivot selalu terisi.
     */
    protected static function booted(): void
    {
        $syncRoleString = function (User $user): void {
            if (filled($user->role)) {
                $roleId = Role::where('slug', $user->role)->value('id');
                if ($roleId && ! $user->roles()->where('roles.id', $roleId)->exists()) {
                    $user->roles()->syncWithoutDetaching([$roleId]);
                }
            }
        };

        static::created($syncRoleString);
        static::updated($syncRoleString);

        // Pengaman terakhir (model-level, berlaku untuk semua jalur hapus
        // termasuk bulk action & force delete): tolak hapus diri sendiri
        // dan owner aktif terakhir. policy + UI menyembunyikan tombolnya;
        // ini jaring pengaman bila request dibuat manual.
        static::deleting(function (User $user): bool {
            if (! auth()->check()) {
                return true;
            }
            if ((int) $user->id === (int) auth()->id()) {
                return false;
            }
            if ($user->isSuperAdmin() && static::otherActiveSuperAdmins($user)->doesntExist()) {
                return false;
            }

            return true;
        });
    }

    /**
     * Get outlet IDs this user can access.
     * Owner with '*' permission (atau kolom role=owner sebagai fallback
     * bila pivot role belum tersinkron) sees all outlets.
     * Others only see their assigned outlets.
     */
    public function getAccessibleOutletIds(): array
    {
        if (($this->role ?? null) === 'owner' || $this->hasPermission('*')) {
            return Outlet::pluck('id')->toArray();
        }

        return $this->outlets()->pluck('outlets.id')->toArray();
    }

    /**
     * Get accessible outlets as a query builder.
     */
    public function accessibleOutlets()
    {
        if (($this->role ?? null) === 'owner' || $this->hasPermission('*')) {
            return Outlet::where('active', true)->orderBy('name');
        }

        return $this->outlets()->where('active', true)->orderBy('name');
    }

    /**
     * Superadmin untuk keperluan proteksi: kolom role lawas ATAU permission '*'.
     */
    public function isSuperAdmin(): bool
    {
        return ($this->getAttribute('role') ?? null) === 'owner' || $this->hasPermission('*');
    }

    /**
     * Owner aktif lain selain user yang diberikan (untuk proteksi owner terakhir).
     */
    public static function otherActiveSuperAdmins(User $except): \Illuminate\Database\Eloquent\Builder
    {
        $ownerRoleId = \App\Models\Role::where('slug', 'owner')->value('id');

        return static::query()
            ->where('active', true)
            ->where('id', '!=', $except->id)
            ->where(function ($q) use ($ownerRoleId) {
                $q->where('role', 'owner');
                if ($ownerRoleId) {
                    $q->orWhereHas('roles', fn ($r) => $r->where('roles.id', $ownerRoleId));
                }
            });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function stockOpnames(): HasMany
    {
        return $this->hasMany(StockOpname::class);
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function employeeOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'employee_id');
    }
}

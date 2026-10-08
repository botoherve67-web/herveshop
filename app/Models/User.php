<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $hidden = ['password', 'remember_token'];

    protected $fillable = ['name', 'email', 'firebase_uid', 'whatsapp', 'password', 'address', 'zone', 'birth_date', 'gender', 'delivery_notes', 'is_active', 'account_type', 'affiliate_code'];

    public function hasAdminRole(?string $role = null): bool
    {
        if (! $this->is_admin) {
            return false;
        }

        $currentRole = $this->admin_role ?: 'super_admin';

        return $role === null || $currentRole === 'super_admin' || $currentRole === $role;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'partner_id');
    }

    public function affiliateWithdrawalRequests()
    {
        return $this->hasMany(AffiliateWithdrawalRequest::class, 'partner_id');
    }

    public function courierOrders()
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

}

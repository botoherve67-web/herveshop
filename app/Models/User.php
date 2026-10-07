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

    protected $hidden = ['password', 'remember_token', 'email_verification_otp'];

    protected $fillable = ['name', 'email', 'whatsapp', 'password', 'address', 'zone', 'birth_date', 'gender', 'delivery_notes', 'is_active'];

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
            'email_verification_otp_expires_at' => 'datetime',
            'email_verification_otp_sent_at' => 'datetime',
            'email_verification_otp_locked_until' => 'datetime',
            'email_verification_otp_attempts' => 'integer',
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

    public function passwordResetRequests()
    {
        return $this->hasMany(PasswordResetRequest::class);
    }
}

<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, \Illuminate\Contracts\Auth\MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'is_blocked',
        'blocked_reason',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    /**
     * Control Filament Admin Panel access.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin && ! (bool) $this->is_blocked;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function customerNotifications()
    {
        return $this->hasMany(CustomerNotification::class);
    }

    public function recentlyViewed()
    {
        return $this->hasMany(RecentlyViewedProduct::class);
    }

    public function recentlyViewedProducts()
    {
        return $this->hasMany(RecentlyViewedProduct::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function customRequests()
    {
        return $this->hasMany(CustomRequest::class, 'user_id');
    }

    public function customOrders()
    {
        return $this->hasMany(CustomOrder::class);
    }

}

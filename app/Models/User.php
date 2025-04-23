<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'customer';
    
    /**
     * Valid user roles
     */
    public const VALID_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CUSTOMER,
    ];

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
        'phone',
        'is_active',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Get orders for this user
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Get user's full address as a string
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }
    
    /**
     * Scope query to only include active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope query to only include admin users
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }
    
    /**
     * Scope query to only include customer users
     */
    public function scopeCustomers($query)
    {
        return $query->where('role', self::ROLE_CUSTOMER);
    }
}

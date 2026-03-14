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

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'specialization',
        'role',
    ];

    /**
     * Get the referrals sent by this GP.
     */
    public function sentReferrals()
    {
        return $this->hasMany(Referral::class, 'sender_id');
    }

    /**
     * Get the referrals received by this Specialist.
     */
    public function receivedReferrals()
    {
        return $this->hasMany(Referral::class, 'receiver_id');
    }

    /**
     * Check if user is an Admin
     */
    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    /**
     * Check if user is a GP
     */
    public function isGP()
    {
        return $this->role === 'GP';
    }

    /**
     * Check if user is a Specialist
     */
    public function isSpecialist()
    {
        return $this->role === 'Specialist';
    }

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
        ];
    }
}

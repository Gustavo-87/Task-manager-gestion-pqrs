<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password', 'tower', 'unit', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
   public function pqrs()
{
    return $this->hasMany(Pqr::class);
}
   public function assignedPqrs() { return $this->hasMany(Pqr::class, 'assigned_to_id'); }
   public function canViewAllPqrs(): bool { return in_array($this->role,['admin','gestor','auditor','apoyo'],true); }
   public function canManagePqrs(): bool { return in_array($this->role,['admin','gestor','apoyo'],true); }
   public function isAdmin(): bool { return $this->role === 'admin'; }
}

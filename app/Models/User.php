<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable([
    'name',
    'email',
    'password',
    'rol',
    'activo',
    'otp_code',
    'otp_expires_at',
])]
#[Hidden([
    'password',
    'remember_token',
    'otp_code',
])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'rol' => 'residente',
        'activo' => true,
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
            'activo' => 'boolean',
            'otp_expires_at' => 'datetime',
        ];
    }

    public function generateOtp(): string
    {
        $otp = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $this->otp_code = $otp;
        $this->otp_expires_at = now()->addMinutes(5);
        $this->save();

        return $otp;
    }

    public function verifyOtp(string $code): bool
    {
        if (!$this->otp_code || !$this->otp_expires_at) {
            return false;
        }

        if (now()->greaterThan($this->otp_expires_at)) {
            return false;
        }

        return hash_equals((string) $this->otp_code, $code);
    }


    /**
     * Obtiene el identificador que se almacenará en el token JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Define atributos personalizados para el token JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function pqrs()
    {
        return $this->hasMany(Pqr::class);
    }
}

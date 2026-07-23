<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
        'penginapan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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

    /**
     * Get the user's initials
     */
    public function isTransport(): bool
    {
        return $this->role === 'transport';
    }

    public function isPanitia(): bool
    {
        return $this->role === 'panitia';
    }

    public function isScan(): bool
    {
        return $this->role === 'scan';
    }

    public function isPenginapan(): bool
    {
        return $this->role === 'penginapan';
    }

    public function isPanitiapenginapan(): bool
    {
        return $this->role === 'panitia_penginapan';
    }

    public function canMarkHadir(): bool
    {
        return in_array($this->role, ['panitia', 'scan', 'penginapan'], true);
    }

    public function penginapan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Penginapan::class);
    }

    public function sopir(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Sopir::class);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}

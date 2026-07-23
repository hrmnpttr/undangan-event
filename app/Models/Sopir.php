<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sopir extends Model
{
    use HasFactory;

    protected $table = 'sopir';

    protected $fillable = [
        'nama',
        'no_hp',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function konfirmasis(): HasMany
    {
        return $this->hasMany(Konfirmasi::class);
    }
}

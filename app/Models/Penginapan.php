<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penginapan extends Model
{
    use HasFactory;

    protected $table = 'penginapan';

    protected $fillable = [
        'nama',
        'kode',
        'kategori',
        'alamat',
        'no_telp',
        'koordinat_maps',
    ];

    public function konfirmasis(): HasMany
    {
        return $this->hasMany(Konfirmasi::class);
    }

    public function tamus(): HasMany
    {
        return $this->hasMany(\App\Models\Tamu::class, 'penginapan_id');
    }
}

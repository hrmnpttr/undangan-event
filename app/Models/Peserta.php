<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Peserta extends Model
{
    use HasFactory;

    protected $table = 'peserta';

    protected $fillable = [
        'konfirmasi_id',
        'nama',
        'jenis_kelamin',
        'nama_pasangan',
    ];

    public function konfirmasi(): BelongsTo
    {
        return $this->belongsTo(Konfirmasi::class);
    }

    public function tamu(): HasOneThrough
    {
        return $this->hasOneThrough(
            Tamu::class,
            Konfirmasi::class,
            'id',          // konfirmasi.id
            'id',          // tamus.id
            'konfirmasi_id', // peserta.konfirmasi_id
            'tamu_id',     // konfirmasi.tamu_id
        );
    }
}

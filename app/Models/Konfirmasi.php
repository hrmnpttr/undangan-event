<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Konfirmasi extends Model
{
    use HasFactory;

    protected $table = 'konfirmasi';

    protected $fillable = [
        'tamu_id',
        'jumlah_hadir',
        'butuh_antar_jemput',
        'tanggal_datang',
        'pesawat_datang',
        'jam_tiba',
        'tanggal_pulang',
        'pesawat_pulang',
        'jam_berangkat',
        'butuh_penginapan',
        'jumlah_menginap',
        'sopir_id',
        'penginapan_id',
        'nomor_kamar',
        'acknowledged',
        'catatan_perubahan',
        'confirmed_at',
        'status_penjemputan',
        'dijemput_at',
        'dijemput_koordinat',
        'selesai_at',
        'selesai_koordinat',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'tanggal_datang' => 'date',
        'tanggal_pulang' => 'date',
        'butuh_antar_jemput' => 'boolean',
        'butuh_penginapan' => 'boolean',
        'jumlah_menginap' => 'integer',
        'acknowledged' => 'boolean',
        'catatan_perubahan' => 'array',
        'dijemput_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    public function tamu(): BelongsTo
    {
        return $this->belongsTo(Tamu::class);
    }

    public function sopir(): BelongsTo
    {
        return $this->belongsTo(Sopir::class);
    }

    public function penginapan(): BelongsTo
    {
        return $this->belongsTo(Penginapan::class);
    }

    public function pesertas(): HasMany
    {
        return $this->hasMany(Peserta::class);
    }

    /**
     * Effective accommodation: the room admin specifically assigned on this
     * confirmation, otherwise the one assigned to the tamu (source of truth).
     */
    public function getPenginapanEfektifAttribute(): ?Penginapan
    {
        return $this->penginapan ?? $this->tamu?->penginapanRecord;
    }

    /**
     * Count unacknowledged change entries.
     */
    public function getJumlahPerubahanBaruAttribute(): int
    {
        if (!$this->catatan_perubahan) {
            return 0;
        }

        return collect($this->catatan_perubahan)
            ->where('acknowledged', false)
            ->count();
    }
}

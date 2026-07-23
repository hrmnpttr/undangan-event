<?php

namespace App\Models;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Tamu extends Model
{
    use HasFactory;

    protected $table = 'tamu';

    protected $fillable = [
        'nama',
        'deskripsi',
        'nomor_wa',
        'wa_terkirim_at',
        'jumlah_orang',
        'bahasa',
        'luar_kota',
        'penginapan_id',
        'dapat_transport',
        'tipe_kamar',
        'jenis_kelamin',
        'tipe',
        'jenis',
        'kode_unik',
    ];

    protected $casts = [
        'luar_kota' => 'boolean',
        'dapat_transport' => 'boolean',
        'tipe' => 'boolean',
        'jumlah_orang' => 'integer',
        'wa_terkirim_at' => 'datetime',
    ];

    /**
     * Accessor: $tamu->penginapan returns true if penginapan_id is set.
     * Backward-compatible with code that used the old boolean column.
     */
    public function getPenginapanAttribute(): bool
    {
        return $this->penginapan_id !== null;
    }

    protected static function booted(): void
    {
        static::creating(function (Tamu $tamu) {
            if (empty($tamu->kode_unik)) {
                $tamu->kode_unik = static::generateKodeUnik();
            }
            // Auto-detect gender from name prefix if not set
            if (empty($tamu->jenis_kelamin) && !empty($tamu->nama)) {
                $tamu->jenis_kelamin = static::detectGenderFromNama($tamu->nama);
            }
        });

        static::updating(function (Tamu $tamu) {
            // Re-detect gender when nama changes and jenis_kelamin was auto-set (or still null)
            if ($tamu->isDirty('nama') && !$tamu->isDirty('jenis_kelamin')) {
                $detected = static::detectGenderFromNama($tamu->nama);
                if ($detected) {
                    $tamu->jenis_kelamin = $detected;
                }
            }
        });
    }

    /**
     * Detect gender from name prefix/title.
     *
     * Returns 'L' for male, 'P' for female, or null if undetectable.
     *
     * Male indicators:  Romo, RP., RD., Pastor, Fr., Br., Mgr., Kardinal, Bapak, Pak, Bp.
     * Female indicators: Sr., Suster, Ibu, Bu
     */
    public static function detectGenderFromNama(?string $nama): ?string
    {
        if (empty($nama)) {
            return null;
        }

        // Male patterns (order matters — check most specific first)
        if (preg_match('/\b(Romo|RP\.|RD\.|Pastor|Fr\.|Frater|Br\.|Bruder|Mgr\.|Kardinal|Bapak|Pak|Bp\.)\b/i', $nama)) {
            return 'L';
        }

        // Female patterns
        if (preg_match('/\b(Sr\.|Suster|Ibu|Bu)\b/i', $nama)) {
            return 'P';
        }

        return null;
    }

    public static function generateKodeUnik(): string
    {
        do {
            $kode = strtoupper(Str::random(8));
        } while (static::where('kode_unik', $kode)->exists());

        return $kode;
    }

    public function konfirmasi(): HasOne
    {
        return $this->hasOne(Konfirmasi::class);
    }

    public function penginapanRecord(): BelongsTo
    {
        return $this->belongsTo(Penginapan::class, 'penginapan_id');
    }

    public function kehadirans(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }

    public function getQrUrl(): string
    {
        return config('app.url') . '/i/' . $this->kode_unik;
    }

    public function getQrCodeSvg(int $scale = 5): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'scale' => $scale,
            'outputBase64' => false,
            'addQuietzone' => true,
            'svgUseFillAttributes' => true,
        ]);

        return (new QRCode($options))->render($this->getQrUrl());
    }

    public function getQrCodeBase64Png(int $scale = 10): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'scale' => $scale,
            'outputBase64' => true,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($this->getQrUrl());
    }

    public function getQrCodeForPdf(int $scale = 5): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'scale' => $scale,
            'outputBase64' => true,
            'addQuietzone' => true,
        ]);

        // Library already returns full data URI when outputBase64=true
        return (new QRCode($options))->render($this->getQrUrl());
    }

    public function getQrCodeSvgForPdf(int $scale = 5): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'scale' => $scale,
            'outputBase64' => false,
            'addQuietzone' => true,
            'svgUseFillAttributes' => true,
        ]);

        $svg = (new QRCode($options))->render($this->getQrUrl());
        // Remove width/height attributes and set viewBox for proper scaling
        $svg = preg_replace('/width="\d+" height="\d+"/', '', $svg);
        $svg = str_replace('<svg ', '<svg width="100" height="100" viewBox="0 0 210 210" ', $svg);
        return $svg;
    }

    public function getQrCodeSvgForPdfWithSize(int $scale = 5): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'scale' => $scale,
            'outputBase64' => false,
            'addQuietzone' => true,
            'svgUseFillAttributes' => true,
        ]);

        $svg = (new QRCode($options))->render($this->getQrUrl());
        // Set specific width and height for PDF rendering
        $svg = str_replace('<svg ', '<svg width="100" height="100" ', $svg);
        return $svg;
    }

    public function getQrCodeSvgForPdfWithSizeAndViewbox(int $scale = 5): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'scale' => $scale,
            'outputBase64' => false,
            'addQuietzone' => true,
            'svgUseFillAttributes' => true,
        ]);

        $svg = (new QRCode($options))->render($this->getQrUrl());
        // Set specific width and height for PDF rendering with viewBox
        $svg = str_replace('<svg ', '<svg width="100" height="100" viewBox="0 0 210 210" ', $svg);
        return $svg;
    }

    public function getJumlahScanAttribute(): int
    {
        return $this->kehadirans()->count();
    }

    public function getSudahHadirAttribute(): bool
    {
        return $this->kehadirans()->exists();
    }

    public function getSudahKonfirmasiAttribute(): bool
    {
        return $this->konfirmasi()->exists();
    }
}

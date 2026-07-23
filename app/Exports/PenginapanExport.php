<?php

namespace App\Exports;

use App\Models\Konfirmasi;
use App\Models\Penginapan;
use App\Models\Tamu;
use App\Services\RoomCalculatorService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PenginapanExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private readonly ?int $penginapanId = null) {}

    public function sheets(): array
    {
        $service     = app(RoomCalculatorService::class);
        $totalData   = $service->buildPenginapanSummary();
        $perHariData = $service->buildPerHariSummary();

        if ($this->penginapanId) {
            $totalData = $totalData->filter(fn ($r) => $r['id'] === $this->penginapanId)->values();
            $penginapanNama = Penginapan::find($this->penginapanId)?->nama;
            if ($penginapanNama) {
                $perHariData = $perHariData->map(function ($day) use ($penginapanNama) {
                    $filtered = collect($day['per_penginapan'])
                        ->filter(fn ($p) => $p['penginapan'] === $penginapanNama)
                        ->values()
                        ->toArray();
                    if (empty($filtered)) {
                        return null;
                    }
                    $p = $filtered[0];
                    return array_merge($day, [
                        'per_penginapan' => $filtered,
                        'total_tamu'     => $p['tamu'],
                        'total_orang'    => $p['orang'],
                        'double_total'   => $p['double_rooms'],
                        'twin_l_total'   => $p['twin_l'],
                        'twin_p_total'   => $p['twin_p'],
                        'total_kamar'    => $p['total_kamar'],
                    ]);
                })->filter()->values();
            }
        }

        return [
            new PenginapanTotalSheet($totalData),
            new PenginapanPerHariSheet($perHariData),
            new PenginapanDetailSheet($this->penginapanId),
            new PenginapanEstimasiSheet($this->penginapanId),
        ];
    }
}

// ─── Sheet 1: Total per penginapan ─────────────────────────────────────────

class PenginapanTotalSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    public function __construct(private readonly \Illuminate\Support\Collection $data) {}

    public function title(): string { return 'Total per Penginapan'; }

    public function headings(): array
    {
        return [
            'Penginapan',
            '(Estimasi) Tamu', '(Estimasi) Orang',
            '(Estimasi) Kamar Double', '(Estimasi) Orang Twin',
            '(Konfirmasi) Tamu Menginap', '(Konfirmasi) Orang Menginap',
            'Kamar Double', 'Twin L (kamar)', 'Twin L (org)', 'Twin P (kamar)', 'Twin P (org)',
            'Belum Jelas', 'Total Kamar',
        ];
    }

    public function array(): array
    {
        $rows = $this->data->map(fn ($r) => [
            $r['nama'],
            $r['estimasi_tamu'],
            $r['estimasi_orang'],
            $r['estimasi_double'],
            $r['estimasi_twin_orang'],
            $r['konfirmasi_tamu'],
            $r['konfirmasi_orang'],
            $r['double_rooms'],
            $r['twin_l_rooms'],
            $r['twin_l_orang'],
            $r['twin_p_rooms'],
            $r['twin_p_orang'],
            $r['undetermined'],
            $r['total_kamar'],
        ])->toArray();

        // Grand total row
        $rows[] = [
            'GRAND TOTAL',
            $this->data->sum('estimasi_tamu'),
            $this->data->sum('estimasi_orang'),
            $this->data->sum('estimasi_double'),
            $this->data->sum('estimasi_twin_orang'),
            $this->data->sum('konfirmasi_tamu'),
            $this->data->sum('konfirmasi_orang'),
            $this->data->sum('double_rooms'),
            $this->data->sum('twin_l_rooms'),
            $this->data->sum('twin_l_orang'),
            $this->data->sum('twin_p_rooms'),
            $this->data->sum('twin_p_orang'),
            $this->data->sum('undetermined'),
            $this->data->sum('total_kamar'),
        ];

        return $rows;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $lastRow = $this->data->count() + 2; // +1 header +1 total
        return [
            1           => ['font' => ['bold' => true]],
            $lastRow    => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3CD']]],
        ];
    }
}

// ─── Sheet 2: Per malam ────────────────────────────────────────────────────

class PenginapanPerHariSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    public function __construct(private readonly \Illuminate\Support\Collection $data) {}

    public function title(): string { return 'Per Malam'; }

    public function headings(): array
    {
        return [
            'Tanggal (Malam)', 'Penginapan',
            'Tamu', 'Orang Menginap',
            'Kamar Double', 'Twin L (kamar)', 'Twin L (org)', 'Twin P (kamar)', 'Twin P (org)',
            'Belum Jelas', 'Total Kamar',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $day) {
            foreach ($day['per_penginapan'] as $p) {
                $rows[] = [
                    $day['label'],
                    $p['penginapan'],
                    $p['tamu'],
                    $p['orang'],
                    $p['double_rooms'],
                    $p['twin_l'],
                    $p['twin_l_orang'],
                    $p['twin_p'],
                    $p['twin_p_orang'],
                    $p['undetermined'],
                    $p['total_kamar'],
                ];
            }
            // Subtotal row per day
            $rows[] = [
                $day['label'] . ' — SUBTOTAL',
                '',
                $day['total_tamu'],
                $day['total_orang'],
                $day['double_total'],
                $day['twin_l_total'],
                '',
                $day['twin_p_total'],
                '',
                '',
                $day['total_kamar'],
            ];
        }

        return $rows;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

// ─── Sheet 3: Detail Tamu Menginap ─────────────────────────────────────────

class PenginapanDetailSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    public function __construct(private readonly ?int $penginapanId = null) {}

    public function title(): string { return 'Detail Tamu Menginap'; }

    public function headings(): array
    {
        return [
            'No',
            'Penginapan',
            'Nama Tamu',
            'Jenis Tamu',
            'Nomor Kamar',
            'Tgl Datang',
            'Tgl Pulang',
            'Jml Menginap',
            'Peserta ke-',
            'Nama Peserta',
            'Jenis Kelamin',
            'Nama Pasangan',
        ];
    }

    public function array(): array
    {
        $query = Konfirmasi::with(['tamu.penginapanRecord', 'pesertas'])
            ->where('butuh_penginapan', true)
            ->whereHas('tamu', fn ($q) => $q->whereNotNull('penginapan_id'));

        if ($this->penginapanId) {
            $query->whereHas('tamu', fn ($q) => $q->where('penginapan_id', $this->penginapanId));
        }

        $konfirmasis = $query->get()->sortBy([
            fn ($a, $b) => strcmp(
                $a->tamu?->penginapanRecord?->nama . $a->tamu?->nama,
                $b->tamu?->penginapanRecord?->nama . $b->tamu?->nama,
            ),
        ]);

        $rows = [];
        $no   = 1;

        foreach ($konfirmasis as $k) {
            $penginapanNama = $k->tamu?->penginapanRecord?->nama ?? '-';
            $tamuNama       = $k->tamu?->nama ?? '-';
            $jenisTamu      = $k->tamu?->jenis ?? '-';
            $nomorKamar     = $k->nomor_kamar ?? '-';
            $tglDatang      = $k->tanggal_datang?->format('d/m/Y') ?? '-';
            $tglPulang      = $k->tanggal_pulang?->format('d/m/Y') ?? '-';
            $jmlMenginap    = $k->jumlah_menginap ?? $k->jumlah_hadir;

            if ($k->pesertas->isEmpty()) {
                $rows[] = [
                    $no++, $penginapanNama, $tamuNama, $jenisTamu,
                    $nomorKamar, $tglDatang, $tglPulang, $jmlMenginap,
                    '-', '-', '-', '-',
                ];
                continue;
            }

            foreach ($k->pesertas as $idx => $peserta) {
                $jk = match ($peserta->jenis_kelamin) {
                    'L' => 'Laki-laki',
                    'P' => 'Perempuan',
                    default => '-',
                };
                $rows[] = [
                    $idx === 0 ? $no++ : '',
                    $penginapanNama,
                    $tamuNama,
                    $jenisTamu,
                    $nomorKamar,
                    $tglDatang,
                    $tglPulang,
                    $jmlMenginap,
                    $idx + 1,
                    $peserta->nama ?: '-',
                    $jk,
                    $peserta->nama_pasangan ?: '-',
                ];
            }
        }

        return $rows;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

// ─── Sheet 4: Estimasi Tamu Belum Konfirmasi ──────────────────────────────

class PenginapanEstimasiSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    private ?\Illuminate\Support\Collection $cachedTamus = null;
    private array $subtotalRows = [];

    public function __construct(private readonly ?int $penginapanId = null) {}

    public function title(): string { return 'Estimasi Belum Konfirmasi'; }

    public function headings(): array
    {
        return [
            'No',
            'Penginapan',
            'Nama Tamu',
            'Jenis Tamu',
            'L/P',
            'Jumlah Orang',
            'Tipe Kamar',
            'Status Konfirmasi',
            'Estimasi Kamar',
        ];
    }

    private function getTamus(): \Illuminate\Support\Collection
    {
        if ($this->cachedTamus === null) {
            $this->cachedTamus = app(RoomCalculatorService::class)
                ->buildEstimasiDetail($this->penginapanId);
        }
        return $this->cachedTamus;
    }

    /**
     * Calculate pooled room estimation per penginapan.
     * - Double: 1 undangan = 1 kamar (regardless of 1 or 2 orang)
     * - Twin L/P: orang pooled by gender per penginapan, then ceil(total / 2) each
     * - Undetermined: no gender set, pooled separately
     */
    private static function calculatePooledRooms(\Illuminate\Support\Collection $tamuGroup): array
    {
        $doubleKamar   = 0;
        $doubleOrang   = 0;
        $twinLOrang    = 0;
        $twinPOrang    = 0;
        $undetermined  = 0;

        foreach ($tamuGroup as $tamu) {
            $jumlah = $tamu->jumlah_orang ?? 1;
            if ($tamu->tipe_kamar === 'double') {
                $doubleKamar++;
                $doubleOrang += $jumlah;
            } elseif ($tamu->jenis_kelamin === 'L') {
                $twinLOrang += $jumlah;
            } elseif ($tamu->jenis_kelamin === 'P') {
                $twinPOrang += $jumlah;
            } else {
                $undetermined += $jumlah;
            }
        }

        $twinLKamar = (int) ceil($twinLOrang / 2);
        $twinPKamar = (int) ceil($twinPOrang / 2);
        $undeterminedKamar = (int) ceil($undetermined / 2);
        $totalKamar = $doubleKamar + $twinLKamar + $twinPKamar + $undeterminedKamar;

        return [
            'double_kamar'       => $doubleKamar,
            'double_orang'       => $doubleOrang,
            'twin_l_orang'       => $twinLOrang,
            'twin_l_kamar'       => $twinLKamar,
            'twin_p_orang'       => $twinPOrang,
            'twin_p_kamar'       => $twinPKamar,
            'undetermined'       => $undetermined,
            'undetermined_kamar' => $undeterminedKamar,
            'total_orang'        => $doubleOrang + $twinLOrang + $twinPOrang + $undetermined,
            'total_kamar'        => $totalKamar,
        ];
    }

    public function array(): array
    {
        $tamus   = $this->getTamus();
        $grouped = $tamus->groupBy(fn ($t) => $t->penginapanRecord?->nama ?? '-');

        $rows              = [];
        $no                = 1;
        $grandTotalOrang   = 0;
        $grandTotalKamar   = 0;
        $this->subtotalRows = [];

        foreach ($grouped as $penginapanNama => $tamuList) {
            $pooled = self::calculatePooledRooms($tamuList);

            foreach ($tamuList as $tamu) {
                $jumlahOrang = $tamu->jumlah_orang ?? 1;
                $tipeKamar   = match ($tamu->tipe_kamar) {
                    'double' => 'Double',
                    'twin'   => 'Twin',
                    default  => 'Auto',
                };
                $jk = match ($tamu->jenis_kelamin) {
                    'L' => 'Laki-laki',
                    'P' => 'Perempuan',
                    default => '-',
                };

                $konfirmasi = $tamu->konfirmasi;
                $status     = $konfirmasi ? 'Belum konfirmasi penginapan' : 'Belum konfirmasi';

                $rows[] = [
                    $no++,
                    $penginapanNama,
                    $tamu->nama ?? '-',
                    $tamu->jenis ?? '-',
                    $jk,
                    $jumlahOrang,
                    $tipeKamar,
                    $status,
                    '',
                ];
            }

            // Subtotal row per penginapan
            $subtotalLabel = $penginapanNama . ' — SUBTOTAL';
            $parts = [];
            if ($pooled['double_kamar']) {
                $parts[] = 'Double: ' . $pooled['double_kamar'] . ' kmr';
            }
            if ($pooled['twin_l_orang']) {
                $parts[] = 'Twin L: ' . $pooled['twin_l_orang'] . ' org → ' . $pooled['twin_l_kamar'] . ' kmr';
            }
            if ($pooled['twin_p_orang']) {
                $parts[] = 'Twin P: ' . $pooled['twin_p_orang'] . ' org → ' . $pooled['twin_p_kamar'] . ' kmr';
            }
            if ($pooled['undetermined']) {
                $parts[] = '?: ' . $pooled['undetermined'] . ' org → ' . $pooled['undetermined_kamar'] . ' kmr';
            }
            $detail = implode(' | ', $parts) ?: '-';

            $rows[] = [
                '',
                $subtotalLabel,
                $tamuList->count() . ' tamu',
                $detail,
                '',
                $pooled['total_orang'],
                '',
                '',
                $pooled['total_kamar'],
            ];

            $this->subtotalRows[] = count($rows) + 1; // +1 for header row

            $grandTotalOrang += $pooled['total_orang'];
            $grandTotalKamar += $pooled['total_kamar'];
        }

        // Grand total row
        $rows[] = [
            '',
            'GRAND TOTAL',
            $tamus->count() . ' tamu',
            '',
            '',
            $grandTotalOrang,
            '',
            '',
            $grandTotalKamar,
        ];

        return $rows;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        // Force array() to run first so subtotalRows is populated
        $this->array();

        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        // Style subtotal rows
        foreach ($this->subtotalRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'italic' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F0FE']],
            ];
        }

        // Style grand total row (last row)
        $lastRow = $sheet->getHighestRow();
        $styles[$lastRow] = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3CD']],
        ];

        return $styles;
    }
}

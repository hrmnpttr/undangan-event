<?php

namespace App\Exports;

use App\Services\RoomCalculatorService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PenjemputanExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $service = app(RoomCalculatorService::class);

        return [
            new PenjemputanTotalSheet($service->buildPenjemputanTotal()),
            new PenjemputanPerHariSheet($service->buildPenjemputanPerHari()),
        ];
    }
}

// ─── Sheet 1: Total Overview ───────────────────────────────────────────────

class PenjemputanTotalSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    public function __construct(private readonly array $data) {}

    public function title(): string { return 'Total Penjemputan'; }

    public function array(): array
    {
        $d = $this->data;
        return [
            ['Kategori', 'Tamu', 'Orang'],
            ['ESTIMASI (semua eligible)', $d['estimasi_tamu'], $d['estimasi_total_orang']],
            ['  → VVIP', '', $d['estimasi_vvip']],
            ['  → VIP (dapat transport)', '', $d['estimasi_vip']],
            [],
            ['KONFIRMASI (butuh antar jemput = Ya)', $d['konfirmasi_tamu'], $d['konfirmasi_total_orang']],
            ['  → VVIP', '', $d['konfirmasi_vvip']],
            ['  → VIP', '', $d['konfirmasi_vip']],
            [],
            ['Belum Konfirmasi (estimasi - konfirmasi)', $d['belum_konfirmasi'], ''],
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
        ];
    }
}

// ─── Sheet 2: Per Hari ────────────────────────────────────────────────────

class PenjemputanPerHariSheet implements
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    public function __construct(private readonly \Illuminate\Support\Collection $data) {}

    public function title(): string { return 'Per Hari'; }

    public function headings(): array
    {
        return [
            'Tanggal', 'Arah',
            'Tamu', 'Orang', 'VVIP', 'VIP', 'Belum Ada Sopir',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $day) {
            if ($day['datang']['tamu'] > 0) {
                $rows[] = [
                    $day['label'], 'DATANG',
                    $day['datang']['tamu'],
                    $day['datang']['orang'],
                    $day['datang']['vvip'],
                    $day['datang']['vip'],
                    $day['datang']['belum_sopir'],
                ];
            }
            if ($day['pulang']['tamu'] > 0) {
                $rows[] = [
                    $day['label'], 'PULANG',
                    $day['pulang']['tamu'],
                    $day['pulang']['orang'],
                    $day['pulang']['vvip'],
                    $day['pulang']['vip'],
                    $day['pulang']['belum_sopir'],
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

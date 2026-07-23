<?php

namespace App\Filament\Resources\TamuResource\Pages;

use App\Filament\Resources\TamuResource;
use App\Filament\Widgets\TamuTableStatsWidget;
use App\Models\Tamu;
use App\Services\TamuXlsxImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ListTamu extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TamuResource::class;

    public function markWaSent(int $tamuId): void
    {
        Tamu::find($tamuId)?->update(['wa_terkirim_at' => now()]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TamuTableStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
            Action::make('download_template_xlsx')
                ->label('Template XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle('Template');

                    // Row 1: group headers (merged)
                    $sheet->setCellValue('B1', 'Kelas');
                    $sheet->mergeCells('B1:D1');
                    $sheet->setCellValue('J1', 'PENGINAPAN');
                    $sheet->mergeCells('J1:R1');
                    $sheet->setCellValue('T1', 'Undangan');
                    $sheet->mergeCells('T1:V1');

                    // Row 2: column headers matching actual Excel format
                    // A=KET, B=VVIP, C=VIP, D=UMUM, E=NAMA & GELAR, F=JABATAN,
                    // G=NO HP/WA, H=JML YG DIUNDANG, I=(blank),
                    // J=HOTEL, K=RRGN, L=IMMANUEL, M=SCJ, N=WISMA/ASRAMA,
                    // O=WISMA, P=YOSEPH, Q=STEFANUS, R=HK,
                    // S=KONTAK, T=ONLINE, U=OFFLINE, V=ED, W=DOMISILI
                    $headers = [
                        'A' => 'KET',
                        'B' => 'VVIP',
                        'C' => 'VIP',
                        'D' => 'UMUM',
                        'E' => 'NAMA & GELAR',
                        'F' => 'JABATAN',
                        'G' => 'NO HP/WA',
                        'H' => 'JML YG DIUNDANG',
                        'J' => 'HOTEL',
                        'K' => 'RRGN',
                        'L' => 'IMMANUEL',
                        'M' => 'SCJ',
                        'N' => 'WISMA/ASRAMA',
                        'O' => 'WISMA',
                        'P' => 'YOSEPH',
                        'Q' => 'STEFANUS',
                        'R' => 'HK',
                        'S' => 'KONTAK',
                        'T' => 'ONLINE',
                        'U' => 'OFFLINE',
                        'V' => 'ED',
                        'W' => 'DOMISILI',
                    ];

                    foreach ($headers as $col => $header) {
                        $sheet->setCellValue($col . '2', $header);
                    }

                    // Example row (row 3)
                    $sheet->setCellValue('B3', 1);                          // VVIP
                    $sheet->setCellValue('E3', "Fr Vien Nguyen SCJ");       // NAMA
                    $sheet->setCellValue('F3', 'Provinsial SCJ USA');       // JABATAN
                    $sheet->setCellValue('H3', 1);                          // JML DIUNDANG
                    $sheet->setCellValue('J3', 1);                          // HOTEL
                    $sheet->setCellValue('T3', 1);                          // ONLINE
                    $sheet->setCellValue('W3', 'Amerika');                  // DOMISILI

                    return response()->streamDownload(function () use ($spreadsheet) {
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, 'template-import-tamu.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),
            Action::make('import_xlsx')
                ->label('Import Template')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('File XLSX (Template / Export)')
                        ->helperText('Upload file template undangan atau file hasil Export XLSX')
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            '.xlsx',
                            '.xls',
                        ]),
                ])
                ->action(function (array $data) {
                    $path = $data['file'] ?? null;

                    if (!$path) {
                        Notification::make()
                            ->title('File import belum dipilih.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $imported = app(TamuXlsxImportService::class)->importFromStoragePath($path);

                        Notification::make()
                            ->title('Import XLSX selesai')
                            ->body("{$imported} data tamu berhasil diproses.")
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Import gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($path);
                    }
                }),
            Action::make('export_xlsx')
                ->label('Export XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->visible(fn () => Auth::user()?->isPanitia() ?? false)
                ->action(function () {
                    $tamuList = Tamu::with(['penginapanRecord', 'konfirmasi', 'kehadirans'])
                        ->orderBy('nama')
                        ->get();

                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle('Data Tamu');

                    $headers = [
                        'A' => 'ID',
                        'B' => 'Nama',
                        'C' => 'Deskripsi',
                        'D' => 'Nomor WA',
                        'E' => 'Jumlah Orang',
                        'F' => 'Bahasa',
                        'G' => 'Luar Kota',
                        'H' => 'Transport',
                        'I' => 'Penginapan',
                        'J' => 'Tipe Kamar',
                        'K' => 'Tipe',
                        'L' => 'Jenis',
                        'M' => 'Kode Unik',
                        'N' => 'Sudah Konfirmasi',
                        'O' => 'Sudah Hadir',
                    ];

                    foreach ($headers as $col => $header) {
                        $sheet->setCellValue($col . '1', $header);
                    }

                    $headerRange = 'A1:O1';
                    $sheet->getStyle($headerRange)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    foreach ($tamuList as $i => $tamu) {
                        $row = $i + 2;
                        $sheet->setCellValue('A' . $row, $tamu->id);
                        $sheet->setCellValue('B' . $row, $tamu->nama);
                        $sheet->setCellValue('C' . $row, $tamu->deskripsi);
                        $sheet->setCellValue('D' . $row, $tamu->nomor_wa ?? '');
                        $sheet->setCellValue('E' . $row, $tamu->jumlah_orang);
                        $sheet->setCellValue('F' . $row, $tamu->bahasa);
                        $sheet->setCellValue('G' . $row, $tamu->luar_kota ? 'Ya' : 'Tidak');
                        $sheet->setCellValue('H' . $row, $tamu->dapat_transport ? 'Ya' : 'Tidak');
                        $sheet->setCellValue('I' . $row, $tamu->penginapanRecord?->nama ?? '');
                        $sheet->setCellValue('J' . $row, $tamu->tipe_kamar ?? '');
                        $sheet->setCellValue('K' . $row, $tamu->tipe ? 'Online' : 'Offline');
                        $sheet->setCellValue('L' . $row, $tamu->jenis);
                        $sheet->setCellValue('M' . $row, $tamu->kode_unik);
                        $sheet->setCellValue('N' . $row, $tamu->konfirmasi ? 'Ya' : 'Tidak');
                        $sheet->setCellValue('O' . $row, $tamu->kehadirans->isNotEmpty() ? 'Ya' : 'Tidak');
                    }

                    foreach (range('A', 'O') as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    return response()->streamDownload(function () use ($spreadsheet) {
                        $writer = new Xlsx($spreadsheet);
                        $writer->save('php://output');
                    }, 'export-tamu-' . now()->format('Ymd-His') . '.xlsx', [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                }),
        ];
    }
}

<?php

use App\Livewire\Undangan;
use App\Models\Tamu;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

/*
Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});
*/

// Undangan page (QR code destination)
Route::get('/i/{kode}', Undangan::class)->name('undangan');

// Konfirmasi card PDF download
Route::get('/i/{kode}/pdf', function (string $kode) {
    $tamu = Tamu::where('kode_unik', $kode)->firstOrFail();
    $konfirmasi = $tamu->konfirmasi;

    if (!$konfirmasi) {
        abort(404);
    }

    app()->setLocale($tamu->bahasa === 'EN' ? 'en' : 'id');

    $pdf = Pdf::loadView('pdf.konfirmasi-card', [
        'tamu' => $tamu,
        'konfirmasi' => $konfirmasi,
    ])->setPaper('a4', 'portrait');

    return $pdf->download('konfirmasi-' . $tamu->kode_unik . '.pdf');
})->name('undangan.pdf');

require __DIR__.'/settings.php';

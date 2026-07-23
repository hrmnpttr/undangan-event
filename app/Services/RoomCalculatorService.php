<?php

namespace App\Services;

use App\Models\Konfirmasi;
use App\Models\Penginapan;
use App\Models\Tamu;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RoomCalculatorService
{
    /**
     * Build summary rows per penginapan (total overview).
     */
    public function buildPenginapanSummary(): Collection
    {
        $penginapans = Penginapan::withCount([
            'tamus as estimasi_tamu',
            'tamus as estimasi_orang' => fn ($q) => $q->selectRaw('sum(jumlah_orang)'),
            'tamus as estimasi_double' => fn ($q) => $q->where('tipe_kamar', 'double'),
        ])->get();

        return $penginapans->map(function (Penginapan $p) {
            // Estimated from tamu table (regardless of konfirmasi)
            $tamusAll = $p->tamus()->get();
            $estimasiTamu  = $tamusAll->count();
            $estimasiOrang = $tamusAll->sum('jumlah_orang');
            $estimasiDouble = $tamusAll->where('tipe_kamar', 'double')->count();
            // Remaining tamus: headcount for twin estimation
            $estimasiTwinOrang = $tamusAll->where('tipe_kamar', '!=', 'double')->sum('jumlah_orang');

            // Confirmed from konfirmasi table
            $konfirmasis = Konfirmasi::with(['tamu', 'pesertas'])
                ->where('butuh_penginapan', true)
                ->whereHas('tamu', fn ($q) => $q->where('penginapan_id', $p->id))
                ->get();

            $konfirmasiTamu  = $konfirmasis->count();
            $konfirmasiOrang = $konfirmasis->sum(fn ($k) => $k->jumlah_menginap ?? $k->jumlah_hadir);

            $rooms = $this->calculateRooms($konfirmasis);

            return [
                'id'                  => $p->id,
                'nama'                => $p->nama,
                'kode'                => $p->kode,
                'estimasi_tamu'       => $estimasiTamu,
                'estimasi_orang'      => $estimasiOrang,
                'estimasi_double'     => $estimasiDouble,
                'estimasi_twin_orang' => $estimasiTwinOrang,
                'konfirmasi_tamu'     => $konfirmasiTamu,
                'konfirmasi_orang'    => $konfirmasiOrang,
                'double_rooms'        => $rooms['double'],
                'twin_l_rooms'        => $rooms['twin_l'],
                'twin_p_rooms'        => $rooms['twin_p'],
                'twin_l_orang'        => $rooms['twin_l_orang'],
                'twin_p_orang'        => $rooms['twin_p_orang'],
                'undetermined'        => $rooms['undetermined'],
                'total_kamar'         => $rooms['total_kamar'],
            ];
        })->sortBy('nama')->values();
    }

    /**
     * Build per-night breakdown across all penginapans.
     * Returns rows keyed by date string, each containing per-penginapan breakdown.
     */
    public function buildPerHariSummary(): Collection
    {
        $konfirmasis = Konfirmasi::with(['tamu.penginapanRecord', 'pesertas'])
            ->where('butuh_penginapan', true)
            ->whereNotNull('tanggal_datang')
            ->whereNotNull('tanggal_pulang')
            ->get();

        // Maximum number of nights a single stay can span. Guards against
        // pathological/typo date ranges (e.g. a wrong year) that would otherwise
        // expand into thousands of phantom nights and make this report crawl.
        $maxNights = 60;

        // Expand date ranges → nightDate => [konfirmasi, ...]
        $byNight = [];
        foreach ($konfirmasis as $k) {
            $datang = $k->tanggal_datang->copy()->startOfDay();
            $pulang = $k->tanggal_pulang->copy()->startOfDay();

            // Skip invalid ranges (pulang on/before datang = no nights)
            if ($pulang->lte($datang)) {
                continue;
            }

            // Cap absurdly long ranges so one bad record can't blow up the loop
            $maxPulang = $datang->copy()->addDays($maxNights);
            if ($pulang->gt($maxPulang)) {
                $pulang = $maxPulang;
            }

            $night = $datang->copy();
            while ($night->lt($pulang)) {
                $byNight[$night->format('Y-m-d')][] = $k;
                $night->addDay();
            }
        }

        ksort($byNight);

        return collect($byNight)->map(function (array $ks, string $date) {
            $group = collect($ks);

            // Group by penginapan
            $byPenginapan = $group->groupBy(fn ($k) => $k->tamu?->penginapanRecord?->nama ?? 'Tidak diketahui');

            $penginapanRows = $byPenginapan->map(function ($kList, $pNama) {
                $orang = $kList->sum(fn ($k) => $k->jumlah_menginap ?? $k->jumlah_hadir);
                $rooms = $this->calculateRooms($kList);

                return [
                    'penginapan'   => $pNama,
                    'tamu'         => $kList->count(),
                    'orang'        => $orang,
                    'double_rooms' => $rooms['double'],
                    'twin_l'       => $rooms['twin_l'],
                    'twin_p'       => $rooms['twin_p'],
                    'twin_l_orang' => $rooms['twin_l_orang'],
                    'twin_p_orang' => $rooms['twin_p_orang'],
                    'undetermined' => $rooms['undetermined'],
                    'total_kamar'  => $rooms['total_kamar'],
                ];
            })->sortBy('penginapan')->values();

            $totalOrang = $group->sum(fn ($k) => $k->jumlah_menginap ?? $k->jumlah_hadir);
            $totalRooms = $this->calculateRooms($group);

            return [
                'tanggal'      => $date,
                'label'        => Carbon::parse($date)->translatedFormat('D, d M Y'),
                'total_tamu'   => $group->count(),
                'total_orang'  => $totalOrang,
                'total_kamar'  => $totalRooms['total_kamar'],
                'double_total' => $totalRooms['double'],
                'twin_l_total' => $totalRooms['twin_l'],
                'twin_p_total' => $totalRooms['twin_p'],
                'per_penginapan' => $penginapanRows,
            ];
        })->values();
    }

    /**
     * Calculate room allocation from a collection of Konfirmasi records.
     */
    public function calculateRooms(Collection $konfirmasis): array
    {
        $double      = 0;
        $malePool    = 0;
        $femalePool  = 0;
        $undetermined = 0;

        foreach ($konfirmasis as $k) {
            $tamu          = $k->tamu;
            $jumlahMenginap = $k->jumlah_menginap ?? $k->jumlah_hadir;

            // Forced double (even if alone)
            if ($tamu?->tipe_kamar === 'double') {
                $double++;
                continue;
            }

            $pesertas = $k->pesertas ?? collect();

            if ($pesertas->isEmpty()) {
                $undetermined += $jumlahMenginap;
                continue;
            }

            // Take only staying pesertas
            $staying = $pesertas->take($jumlahMenginap);

            foreach ($staying as $p) {
                // Male with pasangan = couple → double room (P partner is implicit)
                if ($p->jenis_kelamin === 'L' && !empty($p->nama_pasangan)) {
                    $double++;
                } elseif ($p->jenis_kelamin === 'P' && !empty($p->nama_pasangan)) {
                    // P already paired via their L's double slot → skip
                } elseif ($p->jenis_kelamin === 'L') {
                    $malePool++;
                } elseif ($p->jenis_kelamin === 'P') {
                    $femalePool++;
                } else {
                    $undetermined++;
                }
            }
        }

        $twinL = (int) ceil($malePool / 2);
        $twinP = (int) ceil($femalePool / 2);

        return [
            'double'       => $double,
            'twin_l'       => $twinL,
            'twin_p'       => $twinP,
            'twin_l_orang' => $malePool,
            'twin_p_orang' => $femalePool,
            'undetermined' => $undetermined,
            'total_kamar'  => $double + $twinL + $twinP,
        ];
    }

    /**
     * Build detail list of tamu assigned to penginapan but NOT yet confirmed.
     * Used for room-booking estimation.
     *
     * "Belum konfirmasi" = tamu has penginapan_id AND either:
     *   - no konfirmasi record, OR
     *   - konfirmasi exists but butuh_penginapan != true
     */
    public function buildEstimasiDetail(?int $penginapanId = null): Collection
    {
        $query = Tamu::with('penginapanRecord', 'konfirmasi')
            ->whereNotNull('penginapan_id')
            ->where(function ($q) {
                $q->whereDoesntHave('konfirmasi')
                  ->orWhereHas('konfirmasi', fn ($q2) => $q2->where('butuh_penginapan', '!=', true));
            });

        if ($penginapanId) {
            $query->where('penginapan_id', $penginapanId);
        }

        return $query->get()
            ->sortBy([
                fn ($a, $b) => strcmp(
                    ($a->penginapanRecord?->nama ?? '') . $a->nama,
                    ($b->penginapanRecord?->nama ?? '') . $b->nama,
                ),
            ])
            ->values();
    }

    /**
     * Build penjemputan total summary (estimated + confirmed, per jenis).
     */
    public function buildPenjemputanTotal(): array
    {
        // Estimated: all tamus eligible for transport (semua jenis, termasuk VVIP, harus dapat_transport=true)
        $eligible = Tamu::query()
            ->where('luar_kota', true)
            ->where('dapat_transport', true)
            ->with('konfirmasi')
            ->get();

        $estimasiTotal   = $eligible->sum('jumlah_orang');
        $estimasiVVIP    = $eligible->where('jenis', 'VVIP')->sum('jumlah_orang');
        $estimasiVIP     = $eligible->where('jenis', 'VIP')->sum('jumlah_orang');

        // Confirmed: those who confirmed butuh_antar_jemput=true
        $confirmed = Konfirmasi::with('tamu')
            ->where('butuh_antar_jemput', true)
            ->whereHas('tamu', fn ($q) => $q->where('luar_kota', true))
            ->get();

        $konfirmasiTotal = $confirmed->sum('jumlah_hadir');
        $konfirmasiVVIP  = $confirmed->filter(fn ($k) => $k->tamu?->jenis === 'VVIP')->sum('jumlah_hadir');
        $konfirmasiVIP   = $confirmed->filter(fn ($k) => $k->tamu?->jenis === 'VIP')->sum('jumlah_hadir');

        // Belum konfirmasi (eligible but no butuh_antar_jemput=true)
        $belumKonfirmasi = $eligible->filter(fn ($t) => !$t->konfirmasi || !$t->konfirmasi->butuh_antar_jemput)->count();

        return [
            'estimasi_tamu'       => $eligible->count(),
            'estimasi_total_orang'=> $estimasiTotal,
            'estimasi_vvip'       => $estimasiVVIP,
            'estimasi_vip'        => $estimasiVIP,
            'konfirmasi_tamu'     => $confirmed->count(),
            'konfirmasi_total_orang'=> $konfirmasiTotal,
            'konfirmasi_vvip'     => $konfirmasiVVIP,
            'konfirmasi_vip'      => $konfirmasiVIP,
            'belum_konfirmasi'    => $belumKonfirmasi,
        ];
    }

    /**
     * Build per-day penjemputan summary (arrival + departure separately).
     */
    public function buildPenjemputanPerHari(): Collection
    {
        $konfirmasis = Konfirmasi::with('tamu')
            ->where('butuh_antar_jemput', true)
            ->where(fn ($q) => $q->whereNotNull('tanggal_datang')->orWhereNotNull('tanggal_pulang'))
            ->get();

        $dates = [];

        foreach ($konfirmasis as $k) {
            if ($k->tanggal_datang) {
                $d = $k->tanggal_datang->format('Y-m-d');
                $dates[$d]['datang'][] = $k;
            }
            if ($k->tanggal_pulang) {
                $d = $k->tanggal_pulang->format('Y-m-d');
                $dates[$d]['pulang'][] = $k;
            }
        }

        ksort($dates);

        return collect($dates)->map(function (array $slots, string $date) {
            $datang = collect($slots['datang'] ?? []);
            $pulang = collect($slots['pulang'] ?? []);

            $buildGroup = fn (Collection $grp) => [
                'tamu'         => $grp->count(),
                'orang'        => $grp->sum('jumlah_hadir'),
                'vvip'         => $grp->filter(fn ($k) => $k->tamu?->jenis === 'VVIP')->count(),
                'vvip_orang'   => $grp->filter(fn ($k) => $k->tamu?->jenis === 'VVIP')->sum('jumlah_hadir'),
                'vip'          => $grp->filter(fn ($k) => $k->tamu?->jenis === 'VIP')->count(),
                'belum_sopir'  => $grp->filter(fn ($k) => !$k->sopir_id)->count(),
            ];

            return [
                'tanggal' => $date,
                'label'   => Carbon::parse($date)->translatedFormat('D, d M Y'),
                'datang'  => $buildGroup($datang),
                'pulang'  => $buildGroup($pulang),
            ];
        })->values();
    }
}

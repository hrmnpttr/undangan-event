<?php

namespace App\Services;

use App\Models\Penginapan;
use App\Models\Tamu;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TamuXlsxImportService
{
    public function importFromStoragePath(string $path): int
    {
        $absolutePath = Storage::disk('local')->path($path);

        $spreadsheet = IOFactory::load($absolutePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $headerRowIndex = $this->detectHeaderRow($rows);
        $headerMap = $headerRowIndex ? $this->buildHeaderMap($rows[$headerRowIndex]) : [];
        $penginapanColumns = $headerRowIndex
            ? $this->detectPenginapanColumns($rows[$headerRowIndex])
            : [];

        $startRow = $headerRowIndex ? $headerRowIndex + 1 : 1;
        $imported = 0;
        $penginapanCache = Penginapan::all();

        for ($i = $startRow; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? [];

            if ($this->isRowEmpty($row)) {
                continue;
            }

            // NAMA & GELAR (col E) — may be multi-line
            $namaGelar = $this->getValue($row, $headerMap, ['nama_dan_gelar', 'nama_gelar', 'nama'], ['E']);
            $lines     = $this->splitLines((string) $namaGelar);
            $nama      = $lines[0] ?? null;

            if (blank($nama)) {
                continue;
            }

            // JABATAN as deskripsi; fall back to extra lines of nama cell
            $jabatan          = trim((string) $this->getValue($row, $headerMap, ['jabatan', 'keterangan', 'deskripsi'], ['F']));
            $deskripsiFromNama = count($lines) > 1 ? implode(' ', array_slice($lines, 1)) : null;
            $deskripsi        = $jabatan ?: $deskripsiFromNama ?: null;

            $jenis = $this->resolveJenis($row, $headerMap);

            // Jumlah diundang — keep raw (0 is valid)
            // Try all common header variants first, then fall back to H/I
            $jmlDiundang = $this->rawInt(
                $this->getValue($row, $headerMap, [
                    'jml_yg_diundang',
                    'jumlah_yang_diundang',
                    'jumlah_yg_diundang',
                    'jml_yang_diundang',
                    'jml_diundang',
                    'jumlah_diundang',
                    'jumlah_orang',
                    'jumlah',
                ], ['H', 'I'])
            );

            // Fuzzy fallback: scan headerMap for any key containing 'diundang'
            if ($jmlDiundang === 0) {
                foreach ($headerMap as $normalizedKey => $col) {
                    if (str_contains($normalizedKey, 'diundang')) {
                        $candidate = $this->rawInt($row[$col] ?? 0);
                        if ($candidate > 0) {
                            $jmlDiundang = $candidate;
                            break;
                        }
                    }
                }
            }

            // Penginapan: scan all detected penginapan columns (template format)
            $penginapanId = null;
            $jmlMenginap  = 0;

            foreach ($penginapanColumns as $letter => $slug) {
                $val = $this->rawInt($row[$letter] ?? 0);
                if ($val > 0) {
                    $jmlMenginap += $val;
                    if ($penginapanId === null) {
                        $match = $penginapanCache->first(
                            fn ($p) => strtolower($p->kode ?? '') === $slug
                                || str_contains(strtolower($p->nama), $slug)
                                || str_contains($slug, strtolower($p->kode ?? ''))
                        );
                        $penginapanId = $match?->id;
                    }
                }
            }

            // Try penginapan by name (export format: column "Penginapan" contains the hotel name)
            if ($penginapanId === null) {
                $penginapanNama = trim((string) $this->getValue($row, $headerMap, ['penginapan'], []));
                if ($penginapanNama !== '') {
                    $match = $penginapanCache->first(
                        fn ($p) => strcasecmp($p->nama, $penginapanNama) === 0
                            || str_contains(strtolower($penginapanNama), strtolower($p->nama))
                            || str_contains(strtolower($p->nama), strtolower($penginapanNama))
                    );
                    $penginapanId = $match?->id;
                }
            }

            // If not found via detected columns, try old fixed I–M fallback
            if ($penginapanId === null && empty($penginapanColumns)) {
                $penginapanId = $this->resolvePenginapanIdLegacy($row, $headerMap, $rows[$headerRowIndex] ?? null);
            }

            // jumlah_orang = max(diundang, menginap), min 1
            $jumlahOrang = max($jmlDiundang, $jmlMenginap);
            if ($jumlahOrang <= 0) {
                $jumlahOrang = 1;
            }

            // Nomor WA (col G = NO HP/WA in template; col D = Nomor WA in export)
            $nomorWa = trim((string) $this->getValue($row, $headerMap, [
                'no_hp_wa', 'no_hp', 'no_wa', 'nomor_wa', 'hp', 'wa', 'phone',
            ], ['G']));

            // Online / Offline — support template (col T/N = 1/0) and export ("Online"/"Offline" string)
            $online = $this->toBool($this->getValue($row, $headerMap, ['online'], ['N', 'T']));
            if (!$online) {
                $tipeStr = strtolower(trim((string) $this->getValue($row, $headerMap, ['tipe'], [])));
                if ($tipeStr === 'online') {
                    $online = true;
                }
            }
            $tipeOnline = $online;

            // Luar Kota — support export format (Ya/Tidak string) and template inference
            $luarKotaStr = strtolower(trim((string) $this->getValue($row, $headerMap, ['luar_kota'], [])));
            if ($luarKotaStr !== '') {
                $luarKota = in_array($luarKotaStr, ['ya', '1', 'true', 'yes'], true);
            } else {
                // Domisili (support both old Q and new W)
                $domisili = trim((string) $this->getValue($row, $headerMap, ['domisili'], ['Q', 'W']));
                $luarKota = $this->inferLuarKota($domisili, $penginapanId !== null);
            }

            // Transport — export format (Ya/Tidak string)
            $dapatTransport = false;
            $transportStr = strtolower(trim((string) $this->getValue($row, $headerMap, ['transport', 'dapat_transport'], [])));
            if ($transportStr !== '') {
                $dapatTransport = in_array($transportStr, ['ya', '1', 'true', 'yes'], true);
            }

            // Tipe kamar — preserve from export
            $tipeKamar = trim((string) $this->getValue($row, $headerMap, ['tipe_kamar'], [])) ?: null;
            if ($tipeKamar !== null && !in_array($tipeKamar, ['double', 'twin'])) {
                $tipeKamar = null;
            }

            // Bahasa — preserve from export, default ID
            $bahasa = strtoupper(trim((string) $this->getValue($row, $headerMap, ['bahasa'], [])));
            if (!in_array($bahasa, ['ID', 'EN'])) {
                $bahasa = 'ID';
            }

            // Try to find by exported ID first (re-import signature), then fall back to nama + deskripsi
            $recordId = $this->rawInt($this->getValue($row, $headerMap, ['id'], ['A']));
            $existing = ($recordId > 0) ? Tamu::find($recordId) : null;

            if (!$existing) {
                $existing = Tamu::where('nama', $nama)->where('deskripsi', $deskripsi)->first();
            }

            if ($existing) {
                $existing->update([
                    'nama'           => $nama,
                    'deskripsi'      => $deskripsi,
                    'nomor_wa'       => $nomorWa ?: $existing->nomor_wa,
                    'jumlah_orang'   => max($existing->jumlah_orang, $jumlahOrang),
                    'bahasa'         => $bahasa,
                    'luar_kota'      => $luarKota,
                    'penginapan_id'  => $penginapanId,
                    'dapat_transport' => $dapatTransport,
                    'tipe_kamar'     => $tipeKamar,
                    'tipe'           => $tipeOnline,
                    'jenis'          => $jenis,
                ]);
            } else {
                Tamu::create([
                    'nama'           => $nama,
                    'deskripsi'      => $deskripsi,
                    'nomor_wa'       => $nomorWa ?: null,
                    'jumlah_orang'   => $jumlahOrang,
                    'bahasa'         => $bahasa,
                    'luar_kota'      => $luarKota,
                    'penginapan_id'  => $penginapanId,
                    'dapat_transport' => $dapatTransport,
                    'tipe_kamar'     => $tipeKamar,
                    'tipe'           => $tipeOnline,
                    'jenis'          => $jenis,
                ]);
            }

            $imported++;
        }

        return $imported;
    }

    // -----------------------------------------------------------------------
    // Header detection
    // -----------------------------------------------------------------------

    private function detectHeaderRow(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $joined = strtolower(implode(' ', array_map(fn ($v) => trim((string) $v), $row)));

            if (str_contains($joined, 'nama') &&
                (str_contains($joined, 'gelar') || str_contains($joined, 'jml') ||
                 str_contains($joined, 'jumlah') || str_contains($joined, 'deskripsi') ||
                 str_contains($joined, 'kode'))) {
                return $index;
            }
        }

        return null;
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $column => $value) {
            $normalized = $this->normalizeHeader((string) $value);

            if ($normalized !== '') {
                $map[$normalized] = $column;
            }
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace('&', ' dan ', $header);

        return preg_replace('/[^a-z0-9]+/', '_', $header) ?? '';
    }

    // -----------------------------------------------------------------------
    // Penginapan column detection (dynamic — works for any number of columns)
    // -----------------------------------------------------------------------

    /**
     * Scan the header row and return [column_letter => slug] for every column
     * whose header looks like a penginapan/hotel name.
     */
    private function detectPenginapanColumns(array $headerRow): array
    {
        $result = [];

        foreach ($headerRow as $letter => $value) {
            $slug = $this->headerToPenginapanSlug(strtolower(trim((string) $value)));

            if ($slug !== null) {
                $result[$letter] = $slug;
            }
        }

        return $result;
    }

    private function headerToPenginapanSlug(string $h): ?string
    {
        if ($h === '') {
            return null;
        }

        if (str_contains($h, 'hotel')) {
            return 'hotel';
        }

        if (str_contains($h, 'rrgn')) {
            return 'rrgn';
        }

        if (str_contains($h, 'imman')) {
            return 'immanuel';
        }

        if (str_contains($h, 'scj') && !str_contains($h, 'bad')) {
            // "scj 17 bad" → still scj
            return 'scj';
        }

        if (str_contains($h, 'scj')) {
            return 'scj';
        }

        if (str_contains($h, 'wisma') || str_contains($h, 'asrama')) {
            return 'wisma_asrama';
        }

        if (str_contains($h, 'yoseph') || str_contains($h, 'yosep')) {
            return 'yoseph';
        }

        if (str_contains($h, 'stefanus') || str_contains($h, 'stephanus')) {
            return 'stefanus';
        }

        // "hk" only when the header is exactly "hk" (avoid false matches like "check")
        if ($h === 'hk') {
            return 'hk';
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Legacy fixed-column penginapan (I–M) — used when header detection fails
    // -----------------------------------------------------------------------

    // Old template had hotel at I; new format has Jml yg Diundang at I, hotel at J
    // Legacy fallback tries J first (new format), then I (old format) as last resort
    private const LEGACY_PENGINAPAN_COLUMNS = ['J', 'K', 'L', 'M', 'N'];
    private const LEGACY_PENGINAPAN_SLUGS   = ['hotel', 'rrgn', 'immanuel', 'scj', 'wisma_asrama'];

    private function resolvePenginapanIdLegacy(array $row, array $headerMap, ?array $headerRow): ?int
    {
        foreach (self::LEGACY_PENGINAPAN_COLUMNS as $i => $letter) {
            if ($this->toBool($row[$letter] ?? null)) {
                $slug       = self::LEGACY_PENGINAPAN_SLUGS[$i];
                $penginapan = Penginapan::where('kode', $slug)->first();

                return $penginapan?->id;
            }
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function getValue(array $row, array $headerMap, array $headerCandidates, array $fallbackColumns = []): mixed
    {
        foreach ($headerCandidates as $candidate) {
            if (isset($headerMap[$candidate])) {
                return $row[$headerMap[$candidate]] ?? null;
            }
        }

        foreach ($fallbackColumns as $column) {
            if (array_key_exists($column, $row)) {
                return $row[$column];
            }
        }

        return null;
    }

    private function resolveJenis(array $row, array $headerMap): string
    {
        // Direct jenis column (export format: "umum", "VIP", "VVIP")
        if (isset($headerMap['jenis'])) {
            $jenisVal = strtolower(trim((string) ($row[$headerMap['jenis']] ?? '')));
            if ($jenisVal === 'vvip') return 'VVIP';
            if ($jenisVal === 'vip') return 'VIP';
            if ($jenisVal === 'umum') return 'umum';
        }

        if ($this->toBool($this->getValue($row, $headerMap, ['vvip'], ['B']))) {
            return 'VVIP';
        }

        if ($this->toBool($this->getValue($row, $headerMap, ['vip'], ['C']))) {
            return 'VIP';
        }

        $ket = strtolower(trim((string) $this->getValue($row, $headerMap, ['ket', 'kelas'], ['A'])));

        if (str_contains($ket, 'vvip')) {
            return 'VVIP';
        }

        if (str_contains($ket, 'vip')) {
            return 'VIP';
        }

        return 'umum';
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value > 0;
        }

        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return false;
        }

        return in_array($value, ['1', 'y', 'yes', 'ya', 'true', 'v', 'x', 'check'], true);
    }

    /** Returns the integer value, 0 if empty/non-numeric (no forced minimum). */
    private function rawInt(mixed $value): int
    {
        if (is_numeric($value)) {
            return max(0, (int) $value);
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $value);

        return ($clean !== null && $clean !== '') ? max(0, (int) $clean) : 0;
    }

    private function splitLines(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(fn ($line) => trim($line), preg_split('/\r\n|\r|\n/', $value) ?: [])
        ));
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function inferLuarKota(string $domisili, bool $hasPenginapan): bool
    {
        if ($domisili === '') {
            return $hasPenginapan;
        }

        return !str_contains(strtolower($domisili), 'palembang');
    }
}

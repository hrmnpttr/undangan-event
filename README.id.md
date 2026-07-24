# Undangan — Sistem Undangan & Manajemen Tamu Acara

> 🇬🇧 **Read in English: [README.md](README.md)**

Aplikasi Laravel + Filament untuk mengelola undangan acara secara menyeluruh:
undangan digital dengan konfirmasi kehadiran (RSVP), penyebaran via WhatsApp,
check-in dengan QR code, serta koordinasi transportasi dan penginapan tamu.
Awalnya dibuat untuk sebuah perayaan besar, namun kini sepenuhnya dapat
dikonfigurasi untuk acara apa pun.

- **Didukung oleh [PT SRIWIJAYA UTAMA KARYA](https://sriwijayahost.id)**
- **Lisensi:** gratis untuk penggunaan lokal / non-komersial, dan gratis untuk
  **satu acara publik** selama atribusi "Supported by PT SRIWIJAYA UTAMA KARYA"
  tetap terlihat. Penggunaan banyak acara atau komersial memerlukan izin — lihat
  [LICENSE](LICENSE). Kontak: [github.com/hrmnpttr](https://github.com/hrmnpttr).

---

## Fitur

- **Perancang undangan & template (tanpa koding):** pilih template siap pakai —
  pernikahan (Islam / Kristen / Katolik / Buddha), acara perusahaan, atau tech
  meetup — atau **Kustom** dengan gambar/HTML sendiri. Unggah **musik latar
  (khusus mp3)**, gambar sampul, foto utama, dan galeri, serta isi konten acara
  (rundown/agenda, kutipan, lokasi) dari menu **Desain Undangan** di panel admin.
  Lihat [panduan penggunaan](docs/PANDUAN-UNDANGAN.md).
- **Undangan digital per tamu** di `/i/{kode}` dengan kode unik, dwibahasa
  (Inggris / Indonesia), sampul pembuka beranimasi, dan musik latar.
- **RSVP / konfirmasi:** jumlah yang hadir, opsi "tidak dapat hadir", dan batas
  waktu konfirmasi yang dapat diatur.
- **Transportasi (antar-jemput):** tamu meminta antar-jemput dengan tanggal
  kedatangan dan kepulangan; panitia mengatur sopir dan jadwal penjemputan.
- **Penginapan:** tamu menyatakan kebutuhan penginapan serta data peserta yang
  menginap (nama, jenis kelamin, pasangan) untuk pengaturan kamar otomatis.
- **Penyebaran WhatsApp:** 25 variasi pesan bergilir, modal berbagi, dan
  pelacakan status "terkirim" per tamu.
- **QR code:** buat PDF QR satuan atau massal, dan check-in tamu dengan memindai
  QR di lokasi (log kehadiran).
- **PDF kartu konfirmasi** dibuat per tamu (DomPDF).
- **Impor/ekspor Excel:** impor daftar tamu; ekspor daftar penginapan dan
  penjemputan.
- **Dua panel admin (Filament):** panel panitia (`/panitia`) dan panel sopir
  (`/transport`), dengan akses berbasis peran.
- **Pengaturan runtime:** nama acara, tagline, tanggal acara, batas konfirmasi,
  dan tombol publikasi lokasi penginapan — dapat diubah dari panel admin.
- **Keamanan:** autentikasi dua faktor dan manajemen profil (Laravel Fortify).

## Teknologi

- PHP 8.2+ · Laravel 12
- Filament 5 (panel admin) · Livewire 4 · Flux UI
- Tailwind CSS 4 · Vite 7
- MySQL (atau SQLite untuk pengujian lokal)
- `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (Excel), `html5-qrcode`
  (pemindaian)

> **Catatan Flux UI:** proyek ini memakai `livewire/flux`. Beberapa komponen
> Flux memerlukan lisensi. Jika `composer install` meminta kredensial untuk
> `composer.fluxui.dev`, masukkan data akun Flux Anda atau sesuaikan dependensi.

## Prasyarat

- PHP 8.2 atau lebih baru dengan ekstensi Laravel yang umum
- Composer 2
- Node.js 20+ dan npm
- Database (disarankan MySQL/MariaDB; SQLite dapat dipakai untuk lokal)

## Instalasi (Lokal)

```bash
# 1. Pasang dependensi
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Atur .env — isi database dan pengaturan acara (lihat di bawah)

# 4. Skema database + data awal
php artisan migrate
php artisan db:seed          # opsional: mengisi setting default + user uji coba

# 5. Symlink storage (untuk gambar/aset undangan)
php artisan storage:link

# 6. Build aset front-end
npm run build                # atau: npm run dev  (untuk pengembangan langsung)

# 7. Jalankan
php artisan serve
```

Lalu buka `http://localhost:8000/panitia` untuk panel admin.

> ⚠️ Perintah di atas (`migrate`, `db:seed`, `storage:link`) mengubah database
> dan filesystem Anda. Jalankan sendiri pada database milik Anda — jangan
> mengarahkannya ke data produksi.

## Konfigurasi

Semua konten khusus acara berada di **`config/undangan.php`** dan dikendalikan
oleh `.env`. Tidak ada identitas acara tertentu yang di-hardcode di logika
aplikasi.

| Kunci `.env` | Kegunaan |
|---|---|
| `APP_NAME` | Nama aplikasi / penyelenggara (dipakai di meta tag) |
| `EVENT_NAME` / `EVENT_FULL_NAME` | Nama acara singkat & lengkap (teks undangan, deskripsi OG) |
| `EVENT_TAGLINE` | Tagline acara |
| `EVENT_DATE` | Tanggal acara (`Y-m-d`) |
| `EVENT_CONFIRMATION_UNTIL` | Batas konfirmasi (`Y-m-d`) |
| `EVENT_VENUE_NAME` / `EVENT_VENUE_MAPS` | Nama lokasi & URL Google Maps atau alamat untuk tombol "Navigasi ke Lokasi Acara" |
| `EVENT_INVITE_BASE_URL` | Base URL publik tautan tamu (fallback ke `APP_URL`) |
| `EVENT_DRIVER_EMAIL_DOMAIN` | Domain email untuk akun sopir yang dibuat otomatis |
| `EVENT_IMAGE_PREFIX`, `EVENT_IMAGE_LANG_EN/ID` | Pola nama berkas gambar utama undangan di `storage/app/public` |
| `EVENT_OG_IMAGE_EN/ID`, `EVENT_PDF_IMAGE_EN/ID` | Gambar berbagi sosial dan PDF |

Nilai yang tampil ke tamu (nama acara, tanggal, batas, tombol publikasi) juga
dapat diubah saat runtime dari menu **Settings** di panel admin; nilai ini
menimpa default di atas.

### Gambar undangan

Gambar utama, berbagi sosial, dan PDF **tidak** disertakan dalam repositori ini —
tambahkan milik Anda ke `storage/app/public` dan arahkan variabel
`EVENT_*_IMAGE*` ke gambar tersebut. Gambar utama mengikuti pola
`{PREFIX}{LANG}{1|2}{"" | "big"}.png` (mis. `AcaraSayaEn1.png`,
`AcaraSayaEn1big.png`). Kosongkan `EVENT_IMAGE_PREFIX` untuk menonaktifkan
tampilan gambar.

## Peran & Panel

Akses dikontrol oleh `role` pada tiap pengguna:

| Peran | Akses |
|---|---|
| `panitia` | Akses penuh panitia/admin |
| `panitia_penginapan` | Panitia penginapan |
| `penginapan` | Staf penginapan per hotel |
| `scan` | Pemindaian kehadiran QR |
| `transport` | Panel sopir (`/transport`) |

- **Panel panitia:** `/panitia`
- **Panel sopir:** `/transport`
- **Undangan publik:** `/i/{kode}` · **Kartu PDF:** `/i/{kode}/pdf`

## Rute Publik

| Rute | Deskripsi |
|---|---|
| `GET /i/{kode}` | Halaman undangan + RSVP tamu |
| `GET /i/{kode}/pdf` | Unduh PDF kartu konfirmasi |

## Catatan Keamanan

- Simpan rahasia hanya di `.env`; berkas ini di-ignore git dan tidak boleh
  di-commit.
- Akun login sopir dibuat dengan password acak yang aman (alfanumerik mudah
  dibaca), ditampilkan sekali ke admin saat pembuatan akun.
- Proyek ini terhubung ke database sungguhan; jalankan migrasi, seeder, dan tes
  hanya pada database milik Anda sendiri.

## Lisensi

Didukung oleh **[PT SRIWIJAYA UTAMA KARYA](https://sriwijayahost.id)**.

- **Gratis** untuk penggunaan lokal / non-komersial.
- **Gratis untuk satu (1) acara publik**, dengan syarat atribusi
  *"Supported by PT SRIWIJAYA UTAMA KARYA"* (tertaut ke https://sriwijayahost.id)
  tetap ada di situs yang di-deploy — sudah disertakan di footer undangan.
- **Penggunaan banyak acara, komersial, atau hosting/bisnis** memerlukan izin.

Lihat [LICENSE](LICENSE) untuk ketentuan lengkap dan hubungi
**[@hrmnpttr](https://github.com/hrmnpttr)** untuk mengatur lisensi komersial.

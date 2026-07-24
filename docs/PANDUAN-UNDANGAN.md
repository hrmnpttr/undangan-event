# Panduan Penggunaan — Sistem Undangan Umum

> 🇬🇧 English version: [INVITATION-GUIDE.md](INVITATION-GUIDE.md)

Sistem ini bisa dipakai untuk **acara apa pun** dan **oleh banyak orang**:
pernikahan (Islam, Kristen, Katolik, Buddha), acara perusahaan, pertemuan,
hingga tech meetup. Semua tampilan undangan diatur dari panel admin — tanpa
mengubah kode.

---

## Daftar Isi

1. [Persiapan awal (admin server)](#1-persiapan-awal-admin-server)
2. [Memilih & mendesain template](#2-memilih--mendesain-template)
3. [Mengunggah undangan & musik sendiri](#3-mengunggah-undangan--musik-sendiri)
4. [Template yang tersedia](#4-template-yang-tersedia)
5. [Mengelola tamu & berbagi undangan](#5-mengelola-tamu--berbagi-undangan)
6. [Hari-H: check-in QR](#6-hari-h-check-in-qr)
7. [Pertanyaan umum](#7-pertanyaan-umum)

---

## 1. Persiapan awal (admin server)

Langkah ini dijalankan **sekali** oleh admin/pengembang saat pertama menyiapkan
aplikasi. **Perintah database dijalankan sendiri oleh admin**, tidak dijalankan
otomatis oleh aplikasi.

```bash
# 1. Salin konfigurasi & buat APP_KEY
cp .env.example .env
php artisan key:generate

# 2. Atur koneksi database di .env, lalu buat tabel
php artisan migrate

# 3. Symlink storage → agar musik/gambar yang diunggah bisa diakses publik
php artisan storage:link

# 4. Build aset front-end
npm install && npm run build

# 5. Buat akun panitia pertama (silakan pakai seeder/registrasi sesuai proyek)
```

Login ke panel panitia di `/panitia`.

---

## 2. Memilih & mendesain template

1. Masuk ke menu **Desain Undangan** (ikon kuas).
2. Pada bagian **1. Pilih Template Undangan**, pilih template yang sesuai jenis
   acara. Form konten di bawahnya otomatis menyesuaikan.
3. Centang / hilangkan **"Tampilkan halaman sampul (amplop)"** sesuai selera.
   Jika dimatikan, undangan langsung terbuka tanpa layar sampul.
4. Isi bagian **3. Konten Undangan**:
   - **Judul, sub judul, tanggal, waktu, lokasi, Google Maps** — dipakai semua template.
   - **Kutipan / ayat pembuka** — mis. QS. Ar-Rum: 21 atau Kejadian 2:24.
   - Untuk **pernikahan**: nama panggilan & lengkap mempelai, nama orang tua,
     jadwal akad/pemberkatan dan resepsi.
   - Untuk **acara/perusahaan/tech**: penyelenggara, pembicara, dress code, dan
     **rundown/agenda** (klik "Tambah baris").
5. Klik **Simpan Desain**. Perubahan langsung tampil.

> Bagian yang dikosongkan otomatis disembunyikan di halaman undangan, jadi Anda
> hanya perlu mengisi yang relevan.

---

## 3. Mengunggah undangan & musik sendiri

Pada bagian **2. Musik & Gambar** di menu Desain Undangan:

- **Musik latar** — unggah berkas **`.mp3` saja** (maks 12&nbsp;MB). Berkas
  divalidasi; format selain mp3 akan ditolak. Anda bisa mematikan autoplay.
  Bila belum mengunggah, sistem memakai `storage/app/public/audio/background.mp3`
  bila tersedia.
- **Gambar sampul (amplop)** — gambar besar pada layar sampul sebelum masuk.
- **Gambar detail / isi** — gambar tambahan pada template klasik.
- **Foto utama** — foto mempelai/acara, tampil bulat di header.
- **Galeri foto** — beberapa gambar sekaligus, tampil sebagai grid.

Ingin mendesain sendiri sepenuhnya? Pilih template **Kustom**, unggah gambar
undangan Anda, atau tempel **HTML** pada kolom "HTML Kustom". Form RSVP tetap
muncul otomatis di bawahnya.

### Ukuran & orientasi gambar yang disarankan

Satu gambar dipakai untuk **HP dan desktop** sekaligus — sistem meng-crop
otomatis (`object-cover`) mengikuti layar, jadi **tidak ada upload versi mobile
terpisah**. Karena undangan bersifat *mobile-first*, utamakan gambar
**portrait**. Berkas maks **8 MB**; idealnya < 1 MB agar cepat dibuka.

| Bidang | Ukuran disarankan | Orientasi | Rasio | Catatan |
|--------|-------------------|-----------|-------|---------|
| **Gambar sampul (amplop)** | 1080 × 1440 px | Portrait | 3:4 | Tampil penuh di layar sampul. HP: dipotong 3:4; desktop: melebar (± 16:10) |
| **Gambar detail / isi** | 1080 × 1350 px | Portrait | 4:5 | Gambar tambahan (template klasik) |
| **Foto utama** | 800 × 800 px | Persegi | 1:1 | Ditampilkan bulat di header — pusatkan wajah/objek |
| **Galeri foto** | 1000 × 1000 px | Persegi | 1:1 | Grid; semua item dipotong ke persegi |
| **Gambar bagikan (OG)** *(opsional, via `.env`)* | 1200 × 630 px | Landscape | 1.91:1 | Pratinjau saat link dibagikan (WhatsApp/medsos) |

> **Tips crop otomatis:** bagian gambar yang paling penting sebaiknya di
> **tengah**, karena tepi bisa terpotong saat menyesuaikan layar. Jika ingin
> teks tidak terpotong, cukup ketikkan teksnya di kolom konten (Judul, Tanggal,
> dsb.) alih-alih menaruhnya di dalam gambar.

> **Butuh gambar berbeda untuk desktop (landscape) & HP (portrait)?** Fitur
> upload dua-versi belum tersedia di perancang; hubungi pengelola bila
> diperlukan.

---

## 4. Template yang tersedia

| Template | Cocok untuk | Ciri khas |
|----------|-------------|-----------|
| **Klasik (Gold)** | Umum / bawaan | Tema emas, dua gambar sampul (kompatibel versi lama) |
| **Pernikahan — Islam** | Akad & resepsi | Bismillah, "Walimatul 'Urs", warna hijau |
| **Pernikahan — Kristen** | Pemberkatan | Salib ✝, warna biru |
| **Pernikahan — Katolik** | Pemberkatan | Salib ✝, warna marun |
| **Pernikahan — Buddha** | Pemberkatan | Simbol Dharma ☸, warna keemasan hangat |
| **Acara Perusahaan** | Gala, RUPS, formal | Bersih, penyelenggara & rundown |
| **Tech Meetup** | Konferensi/meetup | Aksen ungu, gaya monospace, agenda |
| **Kustom** | Bebas | Gambar/HTML Anda sendiri |

Setiap template memakai palet warna sendiri yang otomatis diterapkan ke tombol,
kartu, dan aksen di seluruh halaman.

---

## 5. Mengelola tamu & berbagi undangan

1. Buka menu **Tamu**.
2. Tambahkan tamu (nama, jumlah orang, bahasa, dll) atau **impor dari Excel**.
3. Setiap tamu memperoleh **tautan unik** `/(domain)/i/<kode>` beserta **QR code**.
4. Gunakan tombol **bagikan WhatsApp** pada baris tamu — tersedia 25 variasi
   pesan agar tidak monoton.
5. Tamu membuka tautan, melihat undangan sesuai template, lalu mengisi
   **konfirmasi kehadiran (RSVP)**.

---

## 6. Hari-H: check-in QR

- Buka menu **Scan Kehadiran**.
- Pindai QR tamu memakai kamera atau alat scanner (mode hardware).
- Kehadiran tercatat otomatis, lengkap dengan jumlah yang hadir.

---

## 7. Pertanyaan umum

**Musik tidak berbunyi otomatis?**
Browser modern memblokir autoplay hingga ada interaksi. Musik akan mulai saat
tamu menyentuh/klik layar. Pastikan berkas `.mp3` valid.

**Gambar/musik tidak muncul setelah diunggah?**
Pastikan `php artisan storage:link` sudah dijalankan.

**Bagaimana mengganti acara/menggunakan untuk acara baru?**
Cukup buka **Desain Undangan**, ganti template & konten, lalu perbarui daftar
**Tamu**. Tidak perlu menyentuh kode.

**Apakah aman untuk banyak jenis acara?**
Ya. Semua konten disimpan di tabel `settings`, jadi mengganti tema tidak
memengaruhi data tamu maupun konfirmasi.

---

_Didukung oleh [PT SRIWIJAYA UTAMA KARYA](https://sriwijayahost.id). Lihat
[LICENSE](../LICENSE) untuk ketentuan penggunaan._

<x-filament-panels::page>
    <div class="prose dark:prose-invert max-w-none">

        <p class="lead">
            Sistem ini bisa dipakai untuk <strong>acara apa pun</strong> — pernikahan
            (Islam, Kristen, Katolik, Buddha), acara perusahaan, pertemuan, hingga
            tech meetup. Ikuti langkah berikut untuk menyiapkan undangan Anda.
        </p>

        <h2>Langkah 1 — Data acara</h2>
        <ol>
            <li>Buka menu <strong>Setting</strong> dan atur <code>tanggal_acara</code> serta
                <code>tgl_terakhir_konfirmasi</code> (format <code>YYYY-MM-DD</code>).</li>
            <li>Tambahkan <strong>Contact Person</strong> yang bisa dihubungi tamu.</li>
        </ol>

        <h2>Langkah 2 — Pilih & desain template</h2>
        <ol>
            <li>Buka menu <strong>Desain Undangan</strong>.</li>
            <li>Pilih salah satu template (Pernikahan Islam/Kristen/Katolik/Buddha,
                Acara Perusahaan, Tech Meetup, atau Kustom).</li>
            <li>Unggah <strong>musik latar</strong> (khusus <code>.mp3</code>, maks 12&nbsp;MB),
                gambar sampul, foto utama, dan galeri bila ada.</li>
            <li>Isi konten sesuai jenis acara:
                <ul>
                    <li><em>Pernikahan</em>: nama & orang tua mempelai, jadwal akad/pemberkatan & resepsi, ayat/kutipan.</li>
                    <li><em>Acara/Perusahaan/Tech</em>: penyelenggara, pembicara, dress code, dan <strong>rundown/agenda</strong>.</li>
                    <li><em>Kustom</em>: tempel HTML undangan Anda sendiri.</li>
                </ul>
            </li>
            <li>Klik <strong>Simpan Desain</strong>. Perubahan langsung tampil di halaman undangan.</li>
        </ol>

        <h2>Langkah 3 — Daftar tamu</h2>
        <ol>
            <li>Buka menu <strong>Tamu</strong>, tambahkan tamu satu per satu atau impor dari Excel.</li>
            <li>Setiap tamu mendapat <strong>tautan unik</strong> (<code>/i/&lt;kode&gt;</code>) dan QR code.</li>
            <li>Bagikan lewat tombol <strong>WhatsApp</strong> pada tabel tamu.</li>
        </ol>

        <h2>Langkah 4 — Hari-H</h2>
        <ol>
            <li>Gunakan menu <strong>Scan Kehadiran</strong> untuk memindai QR tamu saat datang.</li>
            <li>Pantau konfirmasi kehadiran di dashboard dan menu <strong>Konfirmasi</strong>.</li>
        </ol>

        <div class="not-prose mt-6 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-950/30 p-4 text-sm">
            <p class="font-semibold text-amber-800 dark:text-amber-300">Catatan teknis untuk admin server</p>
            <ul class="mt-2 list-disc pl-5 text-amber-800 dark:text-amber-300 space-y-1">
                <li>Jalankan <code>php artisan storage:link</code> sekali agar musik & gambar yang diunggah bisa diakses publik.</li>
                <li>Jalankan <code>php artisan migrate</code> saat pertama menyiapkan aplikasi (dijalankan oleh admin, bukan otomatis).</li>
                <li>Build aset front-end: <code>npm install &amp;&amp; npm run build</code>.</li>
            </ul>
        </div>

        <p class="text-sm text-gray-500 mt-4">
            Panduan lengkap juga tersedia di berkas <code>docs/PANDUAN-UNDANGAN.md</code>
            (Bahasa Indonesia) dan <code>docs/INVITATION-GUIDE.md</code> (English).
        </p>
    </div>
</x-filament-panels::page>

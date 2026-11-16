# SPK Screening TK — Laravel

Versi Laravel untuk pendaftaran dan screening calon siswa TK Pembina Hidayatul Mubtadiin. Fitur yang tersedia: pendaftaran daring dengan kuesioner 20 butir, akun orang tua, pemeriksaan antrean, klasifikasi Categorical Naïve Bayes, pengelolaan data latih, evaluasi, riwayat hasil, konfirmasi admin, cek status, tagihan, laporan pembayaran, verifikasi admin, dan pengaturan biaya/rekening.

Hasil klasifikasi hanya menjadi bahan pertimbangan awal pendampingan. Hasil bukan diagnosis dan tidak menentukan penerimaan siswa. Keputusan penerimaan tetap di pihak sekolah.

## Kebutuhan

- PHP 8.3 atau lebih baru, ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, dan `zip`.
- Composer 2.
- MySQL/MariaDB. Proyek dapat menggunakan database `spk_screening` yang sudah ada.

Laravel 13 memerlukan PHP 8.3 atau lebih baru. Script lokal `start-local.ps1` memakai runtime PHP 8.3 terpisah yang disiapkan di folder kerja Codex, sehingga PHP XAMPP lama tidak diganti.

## Menjalankan versi lokal

1. Jalankan MySQL melalui XAMPP.
2. Pastikan database `spk_screening` telah memiliki tabel dari aplikasi sebelumnya dan 20 pertanyaan screening aktif.
3. Salin `.env.example` menjadi `.env`, lalu sesuaikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` bila pengaturan lokal berbeda.
4. Pastikan dependensi Composer tersedia di folder `vendor`. Jika memasang dari GitHub, jalankan `composer install` menggunakan PHP 8.3 atau lebih baru.
5. Dari PowerShell, buka folder proyek Laravel lalu jalankan:

   ```powershell
   .\start-local.ps1
   ```

6. Buka `http://127.0.0.1:8010`.
7. Untuk masuk admin, gunakan akun petugas yang sudah ada pada tabel `users` melalui `http://127.0.0.1:8010/admin/login`.
8. Akun orang tua yang dibuat saat pendaftaran dapat digunakan melalui `http://127.0.0.1:8010/orang-tua/masuk`. Admin mengelola biaya dan pembayaran melalui `http://127.0.0.1:8010/admin/pembayaran`.

Jika PowerShell menolak menjalankan script, jalankan sesi ini:

```powershell
powershell -ExecutionPolicy Bypass -File .\start-local.ps1
```

## Database dan data

Konfigurasi default mengarah ke MySQL `spk_screening`. Aplikasi menggunakan tabel yang sudah ada, termasuk `pendaftar`, `indikator`, `kriteria`, `jawaban`, `data_latih_naive_bayes`, `screening`, dan `detail_screening_naive_bayes`.

Skrip skema lama disimpan sebagai bahan instalasi di `database/legacy/schema.sql`, dan instrumen master di `database/legacy/seed_instrument.sql`. Untuk pemasangan baru, impor skema ke database kosong lalu aktifkan instrumen sesuai persetujuan sekolah. Jangan mengimpor seed instrumen ke database berjalan tanpa pemeriksaan karena skrip seed memperbarui status butir kuesioner.

Tabel `screening_validations` menyimpan konfirmasi admin secara terpisah dari hasil screening dan status penerimaan. Untuk instalasi baru, tabel ini sudah ikut di `schema.sql`. Untuk database lama hasil impor yang belum memiliki tabel tersebut, pilih database `spk_screening` di phpMyAdmin, buka tab **Impor**, lalu impor `database/legacy/migration_screening_validations.sql`. Skrip memakai `CREATE TABLE IF NOT EXISTS` dan tidak mengubah data pendaftar atau riwayat yang sudah tersimpan. Jika database sudah memakai tabel `migrations` Laravel, migrasi aplikasi juga tersedia:

```powershell
.\migrate-local.ps1
```

Jangan menjalankan migrasi ini pada database produksi tanpa persetujuan dan cadangan yang dapat dipulihkan.

Untuk database lama yang kolom `detail_screening_naive_bayes.nilai_fitur` masih bertipe angka, impor `database/legacy/migration_detail_nb_feature_value.sql` setelah memilih database `spk_screening` di phpMyAdmin. Kolom tersebut perlu menyimpan kategori angka aspek dan kategori usia A/B/C.

## Uji otomatis

Dengan PHP dan dependensi Composer yang sesuai:

```powershell
& "..\.runtime\php83\php.exe" artisan test
```

Jika runtime PHP tersebut belum tersedia, gunakan `php artisan test` dari terminal yang sudah memakai PHP 8.3 atau lebih baru. Pengujian unit memeriksa batas kategori usia, pengabaian jawaban 0, keluaran kelas dan probabilitas Naïve Bayes, serta penolakan prediksi ketika salah satu kelas belum memiliki data latih.

## Catatan implementasi

- Kata sandi petugas diverifikasi terhadap hash yang telah tersimpan pada tabel `users`.
- Token CSRF, validasi input, pembatasan akses admin/guru, dan transaksi database digunakan pada alur yang mendukungnya.
- Dokumen pendaftaran disimpan pada disk privat Laravel; berkas tidak disajikan sebagai URL publik.
- File `.env` dan data pribadi pendaftar tidak dimasukkan ke repository.
- Pengelolaan profil sekolah, seluruh pengaturan SPMB, ekspor, manajemen akun petugas, dan administrasi berkas pendaftar belum seluruhnya dipindahkan. Aplikasi PHP sebelumnya tetap tersedia sebagai salinan acuan dan tidak diubah oleh proyek Laravel ini.

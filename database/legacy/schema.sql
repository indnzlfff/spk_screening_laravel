-- Database SPK Screening TK Pembina Hidayatul Mubtadiin
-- Skema aplikasi pendaftaran dan screening Naive Bayes.

CREATE DATABASE IF NOT EXISTS spk_screening
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE spk_screening;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'guru') NOT NULL DEFAULT 'guru',
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  KEY idx_users_role_aktif (role, aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS akun_orang_tua (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(150) NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  email VARCHAR(191) NULL,
  google_id VARCHAR(191) NULL,
  password_hash VARCHAR(255) NOT NULL,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_akun_orang_tua_no_hp (no_hp),
  UNIQUE KEY uq_akun_orang_tua_email (email),
  UNIQUE KEY uq_akun_orang_tua_google_id (google_id),
  KEY idx_akun_orang_tua_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pendaftar (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor_pendaftaran VARCHAR(30) NOT NULL,
  nama_anak VARCHAR(150) NOT NULL,
  nik CHAR(16) NULL,
  dokumen_akta VARCHAR(255) NULL,
  dokumen_kk VARCHAR(255) NULL,
  asal_paud VARCHAR(150) NULL COMMENT 'Opsional; dikosongkan jika anak tidak pernah mengikuti PAUD',
  tempat_lahir VARCHAR(100) NOT NULL,
  tanggal_lahir DATE NOT NULL,
  jenis_kelamin ENUM('L', 'P') NOT NULL,
  provinsi VARCHAR(100) NULL,
  kabupaten_kota VARCHAR(100) NULL,
  kecamatan VARCHAR(100) NULL,
  kelurahan_desa VARCHAR(100) NULL,
  kode_pos CHAR(5) NULL,
  alamat_anak TEXT NOT NULL,
  nama_ayah VARCHAR(150) NULL,
  pekerjaan_ayah VARCHAR(100) NULL,
  nama_ibu VARCHAR(150) NULL,
  pekerjaan_ibu VARCHAR(100) NULL,
  nama_wali VARCHAR(150) NULL,
  no_hp VARCHAR(20) NOT NULL,
  akun_orang_tua_id INT UNSIGNED NULL,
  alamat_wali TEXT NULL,
  status_pendaftaran ENUM('baru', 'diproses', 'diterima', 'ditolak') NOT NULL DEFAULT 'baru',
  kelas_masuk ENUM('TK A1', 'TK A2', 'TK A3', 'TK A4') NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pendaftar_nomor (nomor_pendaftaran),
  KEY idx_pendaftar_nama (nama_anak),
  KEY idx_pendaftar_akun_orang_tua (akun_orang_tua_id),
  KEY idx_pendaftar_nik (nik),
  KEY idx_pendaftar_status_tanggal (status_pendaftaran, created_at),
  CONSTRAINT fk_pendaftar_akun_orang_tua FOREIGN KEY (akun_orang_tua_id)
    REFERENCES akun_orang_tua (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kriteria (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode VARCHAR(10) NOT NULL,
  nama VARCHAR(120) NOT NULL,
  bobot DECIMAL(8,6) NULL COMMENT 'Kolom warisan untuk riwayat SAW lama; tidak dipakai oleh Naive Bayes',
  atribut ENUM('benefit', 'cost') NULL COMMENT 'Kolom warisan untuk riwayat SAW lama; tidak dipakai oleh Naive Bayes',
  aktif TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kriteria_kode (kode),
  KEY idx_kriteria_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS indikator (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kriteria_id INT UNSIGNED NOT NULL,
  kode VARCHAR(20) NOT NULL,
  pertanyaan TEXT NOT NULL,
  nilai_min DECIMAL(8,2) NOT NULL,
  nilai_max DECIMAL(8,2) NOT NULL,
  petunjuk_skala TEXT NULL,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_indikator_kode (kode),
  KEY idx_indikator_kriteria_aktif (kriteria_id, aktif),
  CONSTRAINT fk_indikator_kriteria FOREIGN KEY (kriteria_id)
    REFERENCES kriteria (id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_indikator_rentang CHECK (nilai_max >= nilai_min)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jawaban (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pendaftar_id INT UNSIGNED NOT NULL,
  indikator_id INT UNSIGNED NOT NULL,
  nilai DECIMAL(8,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_jawaban_pendaftar_indikator (pendaftar_id, indikator_id),
  KEY idx_jawaban_indikator (indikator_id),
  CONSTRAINT fk_jawaban_pendaftar FOREIGN KEY (pendaftar_id)
    REFERENCES pendaftar (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_jawaban_indikator FOREIGN KEY (indikator_id)
    REFERENCES indikator (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS screening (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor_screening VARCHAR(30) NOT NULL,
  pendaftar_id INT UNSIGNED NOT NULL,
  metode ENUM('SAW','Naive Bayes') NOT NULL DEFAULT 'SAW',
  kelas_prediksi ENUM('Sesuai harapan','Memerlukan perhatian lebih lanjut') NULL,
  probabilitas_sesuai_harapan DECIMAL(12,10) NULL,
  probabilitas_perhatian_lanjutan DECIMAL(12,10) NULL,
  nilai_akhir DECIMAL(12,8) NULL COMMENT 'Dipertahankan hanya untuk riwayat SAW lama',
  threshold DECIMAL(12,8) NULL,
  aturan_threshold ENUM('>=', '<=') NULL,
  rekomendasi VARCHAR(100) NOT NULL,
  catatan TEXT NULL,
  dihitung_oleh INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_screening_nomor (nomor_screening),
  KEY idx_screening_pendaftar_tanggal (pendaftar_id, created_at),
  KEY idx_screening_rekomendasi (rekomendasi),
  KEY idx_screening_method_class (metode,kelas_prediksi),
  CONSTRAINT fk_screening_pendaftar FOREIGN KEY (pendaftar_id)
    REFERENCES pendaftar (id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_screening_user FOREIGN KEY (dihitung_oleh)
    REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS screening_validations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  screening_id INT UNSIGNED NOT NULL,
  status ENUM('menunggu','dikonfirmasi') NOT NULL DEFAULT 'menunggu',
  catatan_admin TEXT NULL,
  validated_by INT UNSIGNED NULL,
  validated_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_screening_validations_screening (screening_id),
  KEY idx_screening_validations_status (status),
  CONSTRAINT fk_screening_validations_screening FOREIGN KEY (screening_id)
    REFERENCES screening (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_screening_validations_user FOREIGN KEY (validated_by)
    REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS data_latih_naive_bayes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_data VARCHAR(40) NOT NULL,
  usia_bulan SMALLINT UNSIGNED NULL COMMENT 'Kolom legacy; digunakan hanya untuk memetakan kategori usia pada migrasi.',
  usia_kategori ENUM('A','B','C') NOT NULL,
  nilai_agama_moral TINYINT UNSIGNED NOT NULL,
  motorik TINYINT UNSIGNED NOT NULL,
  kognitif TINYINT UNSIGNED NOT NULL,
  bahasa TINYINT UNSIGNED NOT NULL,
  sosial_emosional TINYINT UNSIGNED NOT NULL,
  kelas ENUM('Sesuai harapan','Memerlukan perhatian lebih lanjut') NOT NULL,
  sumber VARCHAR(160) NULL,
  dicatat_oleh INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_data_latih_kode (kode_data),
  KEY idx_data_latih_kelas (kelas),
  CONSTRAINT fk_data_latih_user FOREIGN KEY (dicatat_oleh)
    REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT chk_data_latih_skala CHECK (nilai_agama_moral BETWEEN 1 AND 4 AND motorik BETWEEN 1 AND 4 AND kognitif BETWEEN 1 AND 4 AND bahasa BETWEEN 1 AND 4 AND sosial_emosional BETWEEN 1 AND 4)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detail_screening_naive_bayes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  screening_id INT UNSIGNED NOT NULL,
  kode_fitur VARCHAR(30) NOT NULL,
  nama_fitur VARCHAR(100) NOT NULL,
  nilai_fitur VARCHAR(20) NULL,
  peluang_sesuai_harapan DECIMAL(16,12) NULL,
  peluang_perhatian_lanjutan DECIMAL(16,12) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_detail_nb_fitur (screening_id,kode_fitur),
  CONSTRAINT fk_detail_nb_screening FOREIGN KEY (screening_id)
    REFERENCES screening (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detail_screening (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  screening_id INT UNSIGNED NOT NULL,
  kriteria_id INT UNSIGNED NOT NULL,
  kode_kriteria_saat_hitung VARCHAR(10) NOT NULL,
  nama_kriteria_saat_hitung VARCHAR(120) NOT NULL,
  atribut_saat_hitung ENUM('benefit', 'cost') NOT NULL,
  nilai DECIMAL(12,6) NOT NULL,
  nilai_normalisasi DECIMAL(12,8) NOT NULL,
  bobot DECIMAL(8,6) NOT NULL,
  nilai_terbobot DECIMAL(12,8) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_detail_screening_kriteria (screening_id, kriteria_id),
  KEY idx_detail_screening_kriteria (kriteria_id),
  CONSTRAINT fk_detail_screening_header FOREIGN KEY (screening_id)
    REFERENCES screening (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_detail_screening_kriteria FOREIGN KEY (kriteria_id)
    REFERENCES kriteria (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_setting VARCHAR(80) NOT NULL,
  nilai VARCHAR(255) NULL,
  keterangan TEXT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_nama (nama_setting)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kode_petugas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_hash CHAR(64) NOT NULL,
  role ENUM('admin','guru') NOT NULL,
  dibuat_oleh INT UNSIGNED NOT NULL,
  berlaku_sampai DATETIME NOT NULL,
  digunakan_at DATETIME NULL,
  digunakan_oleh INT UNSIGNED NULL,
  dicabut_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kode_petugas_hash (kode_hash),
  KEY idx_kode_petugas_role_status (role,digunakan_at,dicabut_at,berlaku_sampai),
  CONSTRAINT fk_kode_petugas_pembuat FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_kode_petugas_pemakai FOREIGN KEY (digunakan_oleh) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tagihan (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pendaftar_id INT UNSIGNED NOT NULL,
  kode_tagihan VARCHAR(40) NOT NULL,
  jenis ENUM('pendaftaran','lainnya') NOT NULL DEFAULT 'pendaftaran',
  keterangan VARCHAR(200) NOT NULL,
  jumlah DECIMAL(12,2) NOT NULL,
  jatuh_tempo DATE NULL,
  dibuat_oleh INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tagihan_kode (kode_tagihan),
  UNIQUE KEY uq_tagihan_pendaftar_jenis (pendaftar_id, jenis),
  KEY idx_tagihan_pendaftar (pendaftar_id),
  CONSTRAINT fk_tagihan_pendaftar FOREIGN KEY (pendaftar_id)
    REFERENCES pendaftar (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_tagihan_user FOREIGN KEY (dibuat_oleh)
    REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pembayaran (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tagihan_id INT UNSIGNED NOT NULL,
  metode ENUM('transfer','tunai') NOT NULL,
  jumlah DECIMAL(12,2) NOT NULL,
  bukti_path VARCHAR(255) NULL,
  tanggal_rencana DATE NULL,
  status ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  catatan TEXT NULL,
  diverifikasi_oleh INT UNSIGNED NULL,
  diverifikasi_pada DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pembayaran_tagihan_status (tagihan_id, status),
  KEY idx_pembayaran_status_tanggal (status, created_at),
  CONSTRAINT fk_pembayaran_tagihan FOREIGN KEY (tagihan_id)
    REFERENCES tagihan (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_pembayaran_user FOREIGN KEY (diverifikasi_oleh)
    REFERENCES users (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lima aspek sesuai proposal. Bobot dan atribut tidak digunakan oleh Naive Bayes.
INSERT INTO kriteria (kode, nama, bobot, atribut, aktif) VALUES
  ('C1', 'Nilai agama dan moral', NULL, NULL, 0),
  ('C2', 'Fisik-motorik', NULL, NULL, 0),
  ('C3', 'Kognitif', NULL, NULL, 0),
  ('C4', 'Bahasa', NULL, NULL, 0),
  ('C5', 'Sosial-emosional', NULL, NULL, 0)
ON DUPLICATE KEY UPDATE nama = VALUES(nama);

INSERT INTO settings (nama_setting, nilai, keterangan) VALUES
  ('screening_method', 'naive_bayes', 'Metode screening penelitian sesuai PJS terbaru.'),
  ('screening_admission_independent', '1', 'Prediksi kesiapan tidak menentukan keputusan penerimaan.'),
  ('registration_fee_amount','1755000','Biaya pendaftaran yang berlaku saat ini; dapat diubah admin. Perubahan hanya berlaku untuk tagihan baru.'),
  ('registration_fee_due_date',NULL,'Jatuh tempo tanggal dapat diisi admin. Kebijakan saat ini: sebelum murid mulai masuk sekolah.'),
  ('payment_bank_name','BCA','Rekening transfer sementara sekolah.'),
  ('payment_account_name','Indana Zulfa','Nama pemilik rekening transfer sementara.'),
  ('payment_account_number','0892115018','Nomor rekening transfer sementara; konfirmasi kembali sebelum pembayaran.')
ON DUPLICATE KEY UPDATE keterangan = VALUES(keterangan);


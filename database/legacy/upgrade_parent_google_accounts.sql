-- Jalankan sekali pada database lama jika migrasi Laravel tidak digunakan.
ALTER TABLE akun_orang_tua
  ADD COLUMN email VARCHAR(191) NULL AFTER no_hp,
  ADD COLUMN google_id VARCHAR(191) NULL AFTER email,
  ADD UNIQUE KEY uq_akun_orang_tua_email (email),
  ADD UNIQUE KEY uq_akun_orang_tua_google_id (google_id);

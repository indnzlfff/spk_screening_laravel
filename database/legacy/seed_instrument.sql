-- Instrumen untuk instalasi database baru setelah development_demo.sql.
-- Butir nilai agama dan moral disusun dari indikator dalam proposal dan perlu ditelaah sekolah sebelum dipakai sebagai instrumen penelitian.
-- Tidak membuat data latih dan tidak mengubah data pendaftar.

UPDATE kriteria SET aktif=0;

INSERT INTO kriteria (kode,nama,bobot,atribut,aktif) VALUES
 ('C1','Nilai agama dan moral',NULL,NULL,1),
 ('C2','Fisik-motorik',NULL,NULL,1),
 ('C3','Kognitif',NULL,NULL,1),
 ('C4','Bahasa',NULL,NULL,1),
 ('C5','Sosial-emosional',NULL,NULL,1)
ON DUPLICATE KEY UPDATE nama=VALUES(nama),bobot=NULL,atribut=NULL,aktif=1;

-- Nonaktifkan pertanyaan lama tanpa menghapus jawaban atau riwayat yang tersimpan.
UPDATE indikator SET aktif=0;

INSERT INTO indikator (kriteria_id,kode,pertanyaan,nilai_min,nilai_max,petunjuk_skala,aktif)
SELECT k.id,q.kode,q.pertanyaan,0,4,
 '4=berkembang sangat baik/konsisten; 3=berkembang sesuai harapan; 2=mulai berkembang/perlu pengingat; 1=belum berkembang/perlu bantuan; 0=belum teramati dan diabaikan dari rata-rata.',1
FROM kriteria k
JOIN (
 SELECT 'C1' AS kriteria,'PROP26-C1-01' AS kode,'Apakah anak menunjukkan perilaku sopan kepada orang lain?' AS pertanyaan UNION ALL
 SELECT 'C1','PROP26-C1-02','Apakah anak dapat mengikuti aturan sederhana dalam kegiatan sehari-hari?' UNION ALL
 SELECT 'C1','PROP26-C1-03','Apakah anak menunjukkan sikap hormat kepada orang lain?' UNION ALL
 SELECT 'C1','PROP26-C1-04','Apakah anak bersedia mengikuti kegiatan peribadatan sesuai agama dan kebiasaan keluarganya?' UNION ALL
 SELECT 'C2','PROP26-C2-01','Apakah anak dapat berlari dan menjaga keseimbangan saat bermain?' UNION ALL
 SELECT 'C2','PROP26-C2-02','Apakah anak dapat melompat menggunakan kedua kaki?' UNION ALL
 SELECT 'C2','PROP26-C2-03','Apakah anak dapat memegang alat gambar untuk membuat coretan atau gambar?' UNION ALL
 SELECT 'C2','PROP26-C2-04','Apakah anak dapat menggunakan tangan untuk kegiatan sederhana seperti menyusun benda atau menggunting?' UNION ALL
 SELECT 'C3','PROP26-C3-01','Apakah anak dapat mengenali dan menyebutkan warna dasar?' UNION ALL
 SELECT 'C3','PROP26-C3-02','Apakah anak dapat menghitung benda secara berurutan?' UNION ALL
 SELECT 'C3','PROP26-C3-03','Apakah anak dapat membedakan ukuran benda, seperti besar-kecil atau panjang-pendek?' UNION ALL
 SELECT 'C3','PROP26-C3-04','Apakah anak dapat mencocokkan atau mengelompokkan benda berdasarkan bentuk atau kategorinya?' UNION ALL
 SELECT 'C4','PROP26-C4-01','Apakah anak dapat menyampaikan keinginan atau kebutuhan dengan kalimat yang dapat dipahami?' UNION ALL
 SELECT 'C4','PROP26-C4-02','Apakah anak dapat memahami dan mengikuti arahan sederhana?' UNION ALL
 SELECT 'C4','PROP26-C4-03','Apakah anak dapat menjawab pertanyaan sederhana tentang dirinya?' UNION ALL
 SELECT 'C4','PROP26-C4-04','Apakah anak dapat menceritakan pengalaman sederhana atau menyimak cerita singkat?' UNION ALL
 SELECT 'C5','PROP26-C5-01','Apakah anak mau bermain atau berinteraksi dengan teman sebaya?' UNION ALL
 SELECT 'C5','PROP26-C5-02','Apakah anak dapat berbagi atau menunggu giliran saat bermain?' UNION ALL
 SELECT 'C5','PROP26-C5-03','Apakah anak menunjukkan kepedulian ketika melihat orang lain mengalami kesulitan atau bersedih?' UNION ALL
 SELECT 'C5','PROP26-C5-04','Apakah anak dapat mengungkapkan perasaannya dan mulai ditenangkan dengan bantuan orang dewasa?'
) q ON q.kriteria=k.kode
ON DUPLICATE KEY UPDATE kriteria_id=VALUES(kriteria_id),pertanyaan=VALUES(pertanyaan),nilai_min=0,nilai_max=4,petunjuk_skala=VALUES(petunjuk_skala),aktif=1;

INSERT INTO settings (nama_setting,nilai,keterangan) VALUES
 ('screening_method','naive_bayes','Metode klasifikasi penelitian menggunakan Categorical Naive Bayes.'),
 ('screening_admission_independent','1','Hasil screening merupakan informasi pendukung dan tidak menentukan penerimaan.')
ON DUPLICATE KEY UPDATE nilai=VALUES(nilai),keterangan=VALUES(keterangan);

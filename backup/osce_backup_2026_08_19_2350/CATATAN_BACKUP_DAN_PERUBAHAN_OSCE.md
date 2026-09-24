# Catatan Backup dan Perubahan OSCE

Tanggal: 2026-08-19
Lokasi backup: `d:\laragon\www\cbt\backup\osce_backup_2026_08_19_2350`

## Isi folder backup

- `old/` = kondisi lama dari Git `HEAD` sebelum perubahan OSCE.
- `new/` = kondisi file saat ini setelah perubahan OSCE.

Catatan: file baru tidak ada di `old/`, hanya ada di `new/`:

- `app/Models/PesertaStationOsce.php`
- `app/Models/NilaiPesertaStationOsce.php`
- `database/migrations/2026_08_19_233645_create_peserta_station_osce_tables.php`

## Kondisi lama sebelum perubahan

### Struktur peserta

Peserta OSCE masih masuk lewat tabel:

```text
jadwal_has_mhs
```

Peserta belum melekat ke station/antrian. Di halaman ujian OSCE, peserta diambil dari mahasiswa rombel/blok, lalu di-left join ke `jadwal_has_mhs`.

### Import peserta

File:

```text
app/Imports/ImportPesertaOsce.php
```

Import hanya baca NIM, lalu insert ke:

```text
jadwal_has_mhs
```

Belum ada station awal dan urutan antrian.

### Penilaian

File:

```text
app/Http/Controllers/UjianOsceController.php
```

Nilai disimpan ke:

```text
komponen_has_mhs
```

Nilai melekat pada kombinasi:

```text
id_mhs_pt
id_jadwal_osce
id_jenis_osce
id_komponen_osce
```

Belum ada histori peserta per station/antrian.

### Tampilan

View lama:

```text
resources/views/data-master/jadwal-osce/tambahPeserta.blade.php
resources/views/data-master/jadwal-osce/importPeserta.blade.php
resources/views/data-master/ujian-osce/detail-mahasiswa.blade.php
resources/views/data-master/ujian-osce/beri-nilai.blade.php
```

Belum ada input station awal, urutan antrian, station berikutnya, atau tombol selesai dinilai.

## Kondisi baru setelah perubahan

### Struktur peserta baru

Ditambah tabel:

```text
peserta_station_osce
nilai_peserta_station_osce
```

Migration:

```text
database/migrations/2026_08_19_233645_create_peserta_station_osce_tables.php
```

Status migration saat dicek:

```text
Ran
```

### Peserta manual

Form tambah peserta sekarang menambah field:

```text
Station Awal
Urutan Antrian
```

Saat simpan, sistem tetap insert ke `jadwal_has_mhs`, lalu insert ke:

```text
peserta_station_osce
```

Status awal:

```text
menunggu
```

### Import peserta

Format import baru:

```text
Kolom B = NIM
Kolom C = ID/Nama Station
Kolom D = Urutan Antrian
```

Import sekarang insert ke:

```text
jadwal_has_mhs
peserta_station_osce
```

### Daftar peserta ujian OSCE

Halaman detail station sekarang mengambil peserta dari:

```text
peserta_station_osce
```

Kolom ditambah:

```text
Urutan
Status
```

### Form penilaian

Nilai sekarang disimpan ke tabel baru:

```text
nilai_peserta_station_osce
```

Dan tetap di-sync ke tabel lama:

```text
komponen_has_mhs
```

Tujuan sync: fitur nilai lama tetap jalan.

### Selesai dinilai dan pindah station

Form nilai sekarang punya:

```text
Station Berikutnya
Catatan Perpindahan
Tombol Selesai Dinilai
```

Saat `Selesai Dinilai`:

1. Semua komponen nilai wajib sudah diisi.
2. Peserta station sekarang di-update selesai.
3. Kalau pilih station berikutnya, row baru dibuat di `peserta_station_osce` dengan status `menunggu`.
4. Kalau pilih `Selesai Ujian`, status jadi `selesai_ujian`.

## Kenapa tampilan mungkin belum terlihat berubah

Kemungkinan penyebab:

1. View yang dibuka bukan halaman yang sudah diubah.
2. Cache view Laravel masih pakai versi lama.
3. Peserta lama belum punya row di `peserta_station_osce`, jadi daftar station terlihat kosong.
4. Browser cache.
5. Menu/route OSCE yang dipakai mungkin bukan `data-master/ujian-osce` atau bukan view Blade ini.

Perintah cek/bersihkan cache:

```bash
php artisan optimize:clear
```

Peserta lama perlu dimasukkan ulang lewat tambah peserta/import baru, atau dibuatkan fitur migrasi dari `jadwal_has_mhs` ke `peserta_station_osce`.

## File yang berubah

```text
app/Http/Controllers/JadwalOsceController.php
app/Http/Controllers/UjianOsceController.php
app/Imports/ImportPesertaOsce.php
resources/views/data-master/jadwal-osce/tambahPeserta.blade.php
resources/views/data-master/jadwal-osce/importPeserta.blade.php
resources/views/data-master/ujian-osce/detail-mahasiswa.blade.php
resources/views/data-master/ujian-osce/beri-nilai.blade.php
```

File baru:

```text
app/Models/PesertaStationOsce.php
app/Models/NilaiPesertaStationOsce.php
database/migrations/2026_08_19_233645_create_peserta_station_osce_tables.php
```

## Cara restore manual dari backup

Kalau mau balik ke kondisi lama, copy isi folder:

```text
d:\laragon\www\cbt\backup\osce_backup_2026_08_19_2350\old
```

ke lokasi file aslinya sesuai nama file yang dipisah dengan `__`.

Contoh:

```text
old/app__Http__Controllers__UjianOsceController.php
```

restore ke:

```text
app/Http/Controllers/UjianOsceController.php
```

Catatan: restore file saja belum menghapus tabel baru. Kalau mau rollback database, jalankan rollback migration OSCE dengan hati-hati.

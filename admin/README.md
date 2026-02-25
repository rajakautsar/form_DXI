# Export Feature

## Akses Export

Admin dapat mengakses halaman export di:
- **URL**: `http://localhost/form_DXI/admin/export`
- **Alternatif**: `http://localhost/form_DXI/admin/export.php`

Kedua URL akan bekerja dan menghasilkan file ZIP yang sama.

## Output

File export berupa ZIP yang berisi:
1. **data_submissions.csv** - Data semua pendaftar dalam format CSV (bisa dibuka dengan Excel)
2. **Folder photos/** - Semua foto karya yang di-upload oleh peserta

## Format CSV

CSV berisi kolom-kolom berikut:
- No. (Nomor urut)
- ID Pendaftaran
- Tanggal Daftar
- Nama Lengkap
- No. Telepon
- Instagram
- Alamat
- Kategori Lomba
- Judul Karya
- Jumlah Foto
- File Foto (Nama-nama file yang di-upload)
- File Proof Pembayaran
- File EXIF Data
- Setuju Pernyataan

## Data Source

Data export diambil dari file: `uploads/submissions.json`

Pastikan file ini ada dan terisi dengan data pendaftaran sebelum melakukan export.

## Troubleshooting

**Jika mendapat error 404 "Not Found":**
- Pastikan URL tidak ada typo
- Cek bahwa file `admin/export.php` ada di folder `form_DXI`
- Pastikan Apache memiliki akses ke folder

**Jika ZIP kosong atau tidak berisi foto:**
- Pastikan foto-foto berada di folder `uploads/macro/` atau `uploads/wide/`
- Verifikasi nama file foto di `submissions.json` sesuai dengan file yang ada
- Periksa permission folder agar Apache dapat membaca file

## Manual Download

Jika ingin download manual tanpa menggunakan web, admin dapat:
1. Database: Buka file `uploads/submissions.json` untuk melihat data
2. Foto: Buka folder `uploads/macro/` dan `uploads/wide/` untuk akses foto langsung

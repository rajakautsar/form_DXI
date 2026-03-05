# 🔐 HOSTINGER ADMIN ACCESS GUIDE

**Domain:** https://underwatershootout.deepextremeindonesia.com/

---

## 📱 AKSES ADMIN PANEL - Langkah Demi Langkah

### STEP 1: Buka URL Admin
Buka browser dan masuk ke:
```
https://underwatershootout.deepextremeindonesia.com/admin/admin.html
```

**Expected Result:** 
- Halaman admin dashboard akan terbuka
- Tampilan: Dashboard dengan grafik dan tombol-tombol

---

## 🔑 STEP 2: Masukkan Password

### Default Password
```
admin123
```

### Cara Masuk:
1. Halaman admin akan menampilkan form password
2. Input password: `admin123`
3. Klik tombol **"Masuk"** atau **"Login"**
4. **Done!** ✅ Anda sekarang di Admin Dashboard

**⚠️ PENTING:** Pastikan password sudah diubah di production (lihat bagian "Change Password" di bawah)

---

## 📊 FITUR-FITUR ADMIN DASHBOARD

Setelah berhasil login, Anda dapat mengakses:

### 1. **📈 Statistik Pendaftaran**
- Total pendaftar
- Pendaftar per kategori
- Data terbaru
- Grafik visual

### 2. **💾 Export Data**
Dua opsi export:

#### A. **Export CSV** 
- Menghasilkan file `.csv` 
- Bisa dibuka dengan Excel
- Berisi: nama, telepon, alamat, kategori, dll

#### B. **Export ZIP**
- Menghasilkan file `.zip`
- Berisi: CSV data + semua foto yang di-upload
- Ukuran file tergantung jumlah foto

#### Cara Export:
1. Klik tombol **"Export CSV"** atau **"Export ZIP"**
2. File akan otomatis ter-download
3. File ZIP bisa dibuka dengan WinRAR atau 7-Zip

### 3. **🗑️ Hapus Data**
- ⚠️ **Delete Submission:** Hapus satu pendaftar
- ⚠️ **Delete All:** Hapus SEMUA pendaftar (HATI-HATI!)
  
#### Cara Delete:
1. Klik tombol delete
2. Sistem akan minta password sekali lagi (untuk keamanan)
3. Klik **"Delete"** untuk konfirmasi
4. Data akan dihapus

---

## 🔐 CHANGE PASSWORD (WAJIB untuk Production)

### ⚠️ CRITICAL: Password Default Harus Diubah!

**File yang perlu diubah:** 
```
admin/admin.html
```

### Cara Mengubah Password:

#### Option 1: Via FTP (Recommended)
1. Buka FTP Client (contoh: FileZilla)
2. Connect ke Hostinger FTP
3. Navigate ke folder: `/public_html/form_DXI/admin/`
4. Download file: `admin.html`
5. Edit dengan text editor:
   - Cari baris: `const ADMIN_PASSWORD = 'admin123';`
   - Ganti dengan: `const ADMIN_PASSWORD = 'YOUR_NEW_SECURE_PASSWORD';`
6. Save dan upload kembali

#### Option 2: Via Hostinger File Manager
1. Login ke Hostinger control panel
2. Buka **File Manager**
3. Navigate ke: `/public_html/form_DXI/admin/`
4. Right-click `admin.html` → **Edit**
5. Cari: `const ADMIN_PASSWORD = 'admin123';`
6. Ganti dengan password baru Anda
7. Klik **Save**

#### Option 3: SSH Access (If Available)
```bash
# Connect via SSH
ssh username@underwatershootout.deepextremeindonesia.com

# Navigate to admin folder
cd public_html/form_DXI/admin/

# Edit file dengan nano/vim
nano admin.html

# Find and replace the password line
# Press Ctrl+X to exit, Y to save
```

### Password Requirements:
- ✅ Minimal 8 karakter
- ✅ Kombinasi huruf besar & kecil
- ✅ Tambahkan angka dan simbol (!@#$%)
- ✅ Hindari kata-kata umum (password, admin123, dll)

### Contoh Password Kuat:
```
DeepX@Admin2024!
UnderwaterShoot#2026
Form_DXI_Admin$Pass123
```

---

## 🧙 ADMIN TOOLS - Fitur Lengkap

### 1. **Lihat Statistik**
```
Dashboard utama menampilkan:
├── Total Submissions
├── Per Kategori (Makro, Wide, Bukti Pembayaran)
└── Recent Submissions (10 terbaru)
```

### 2. **Export Data CSV**
```
File berisi kolom:
├── No. (Nomor urut)
├── ID Pendaftaran
├── Tanggal Daftar
├── Nama Lengkap
├── Nomor Telepon
├── Instagram
├── Alamat
├── Kategori Lomba
├── Judul Karya
├── Jumlah Foto
├── File Foto (nama-nama file)
├── File Proof
├── File EXIF
└── Pernyataan
```

### 3. **Export Data ZIP**
```
Struktur file ZIP:
├── data_submissions.csv (CSV dari poin 2)
└── photos/
    ├── makro/ (semua foto makro)
    ├── wide/ (semua foto wide)
    ├── proof/ (semua bukti pembayaran)
    └── exif/ (semua file EXIF)
```

### 4. **Delete Submission**
- Click "Delete" pada setiap submission
- Confirm dengan password
- Deleted submission akan hilang

### 5. **Delete ALL (DANGER ZONE)**
⚠️ **HATI-HATI!** Ini akan menghapus SEMUA data!
- Klik tombol **"Delete All Submissions"**
- Confirm 2x dengan password
- Data tidak bisa di-recovery

---

## 🔗 ADMIN API ENDPOINTS

Jika Anda ingin mengintegrasikan dengan aplikasi lain, tersedia API:

### GET Statistics
```
GET https://underwatershootout.deepextremeindonesia.com/admin-api.php?action=stats

Response:
{
  "success": true,
  "stats": {
    "total": 45,
    "makro": 15,
    "wide": 30,
    "by_category": {...}
  }
}
```

### GET Recent Submissions
```
GET https://underwatershootout.deepextremeindonesia.com/admin-api.php?action=recent

Response:
{
  "success": true,
  "data": [... list of recent 10 submissions ...]
}
```

### DELETE Submission (Requires Password)
```
POST https://underwatershootout.deepextremeindonesia.com/admin-api.php

Body:
{
  "action": "delete",
  "id": "SUBMISSION_ID",
  "password": "admin123"
}
```

---

## ⚠️ TROUBLESHOOTING

### Problem 1: "404 Not Found"
**Penyebab:** URL tidak benar

**Solusi:**
1. Periksa URL: `https://underwatershootout.deepextremeindonesia.com/admin/admin.html`
2. Pastikan tidak ada typo
3. Jika masih error, hubungi Hostinger support

### Problem 2: "Password salah"
**Penyebab:** Password yang diinput tidak match dengan yang di-setting

**Solusi:**
1. Pastikan CAPS LOCK tidak aktif
2. Password default adalah: `admin123`
3. Jika sudah diubah, gunakan password yang baru
4. Cek file `admin.html` untuk verifikasi password

### Problem 3: "Data tidak muncul"
**Penyebab:** Database connection error atau data kosong

**Solusi:**
1. Pastikan form sudah menerima submissions
2. Cek file `uploads/submissions.json` 
3. Buka browser console (F12) dan lihat error message
4. Hubungi developer jika masih error

### Problem 4: "Export tidak berfungsi"
**Penyebab:** 
- Folder uploads tidak writable
- Missing PHP extensions
- Server tidak punya permission

**Solusi:**
1. Export CSV: Pastikan `uploads/submissions.json` readable
2. Export ZIP: Pastikan folder `uploads/` writable
3. Hubungi Hostinger support untuk set permissions

### Problem 5: "Foto tidak ada di ZIP"
**Penyebab:** Foto tidak tersimpan dengan benar atau nama file tidak match

**Solusi:**
1. Cek folder: `uploads/makro/`, `uploads/wide/`, `uploads/proof/`, `uploads/exif/`
2. Verifikasi nama file di `uploads/submissions.json`
3. Pastikan FTP user punya akses ke folder
4. Re-upload yang benar jika ada yang corrupt

---

## 🛡️ SECURITY CHECKLIST

Sebelum membuka admin ke publik, pastikan:

- [ ] Password sudah diubah dari `admin123`
- [ ] HTTPS aktif (seharusnya sudah default di Hostinger)
- [ ] File `.htaccess` ada di folder `/admin/`
- [ ] Database credentials aman di `config/database.php`
- [ ] Hanya share admin URL ke orang yang dipercaya
- [ ] Regular backup data (export CSV/ZIP secara berkala)

---

## 📞 QUICK REFERENCE

| Item | Value |
|------|-------|
| **Admin URL** | https://underwatershootout.deepextremeindonesia.com/admin/admin.html |
| **Default Password** | admin123 |
| **Server** | Hostinger |
| **File untuk ubah password** | admin/admin.html |
| **Database connection file** | config/database.php |
| **Data storage** | uploads/submissions.json |

---

## 🎓 TIPS & TRICKS

### Tip 1: Backup Regular
Export data secara berkala:
```bash
# Setiap minggu, buat download:
1. CSV untuk spreadsheet analysis
2. ZIP untuk backup lengkap dengan foto
```

### Tip 2: Monitor Form Activity
Check recent submissions di dashboard untuk:
- Monitor apakah form berfungsi
- Cek kualitas submission
- Respon cepat jika ada error submission

### Tip 3: Maintenance Schedule
Recommended maintenance:
- ✅ Daily: Check recent submissions
- ✅ Weekly: Export & backup data
- ✅ Monthly: Review & clean up if needed
- ✅ Before deadline: Freeze submissions (disable form)

### Tip 4: Share Admin Access
Jika perlu share access ke orang lain:
- ⚠️ Jangan share password di email/chat
- ✅ Ubah password setelah orang selesai bekerja
- ✅ Keep audit log siapa akses kapan

---

**Version:** 1.0  
**Last Updated:** March 4, 2026  
**Status:** Ready for Production ✅

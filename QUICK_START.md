# 🚀 QUICK START GUIDE

## 📍 Lokasi Project
```
c:\xampp\htdocs\form_DXI\
```

## ✅ Setup Cepat

### 1. Akses Form
Buka browser dan ketik:
```
http://localhost/form_DXI/
```

### 2. Selesai! 
Form sudah siap digunakan dengan semua fitur modern.

---

## 🎯 Fitur-Fitur Utama

### ✨ User Experience
- **Banner Modern**: Header dengan animasi gradient yang menarik
- **Responsive Design**: Bekerja sempurna di desktop, tablet, dan mobile
- **Drag & Drop Upload**: Bisa drag-drop file atau klik untuk browse
- **Real-time Validation**: Feedback langsung saat user input
- **Auto-save Draft**: Otomatis simpan draft setiap 30 detik
- **Smart Formatting**: Auto-format nomor HP dan username Instagram

### 📋 Validasi Form
- ✓ Semua field required ter-validasi
- ✓ Format nomor telepon & Instagram otomatis
- ✓ File type & size checking
- ✓ Agreement checkbox harus dicentang

### 📁 Struktur Data Form

#### A. Data Diri Peserta
- Nama Lengkap (required)
- No. HP WhatsApp (required)
- Instagram (required)
- Alamat (required)
- Bukti Follow/Repost (file, required)

#### B. Kategori Lomba
- **Macro Angle**: Detail bawah laut kecil
- **Wide Angle**: Lanskap bawah laut luas

#### C. Upload Karya
- Judul Foto (required)
- File Karya (JPG/PNG/TIFF, max 20MB)
- EXIF Data (opsional):
  - Kamera, Lensa
  - Shutter Speed, Aperture, ISO
  - Lokasi

#### D. Pernyataan Peserta
- 5 poin agreement yang harus disepakati (required)

---

## 🔧 File Structure

```
form_DXI/
├── index.html                      # ⭐ Main form (buka ini)
├── process_form.php                # Backend processing
├── admin_dashboard.php             # Admin panel (optional)
├── assets/
│   ├── css/
│   │   └── style.css               # Styling modern
│   └── js/
│       └── script.js               # Interaktivitas
├── uploads/                        # Folder untuk submit files (akan terbuat otomatis)
│   ├── macro/                      # File macro angle
│   ├── wide/                       # File wide angle
│   └── proof/                      # File bukti
├── README.md                       # Dokumentasi lengkap
├── SECURITY_DEPLOYMENT.md          # Panduan keamanan
├── submissions_example.json        # Contoh struktur data
└── .gitignore                      # Git configuration
```

---

## 🎨 Warna & Design

| Elemen | Warna | Kode |
|--------|-------|------|
| Primary (Biru) | #0066cc | Tombol utama, border |
| Secondary (Aqua) | #00a8cc | Hover, accent |
| Accent (Merah) | #ff6b6b | Error, required |
| Success (Hijau) | #51cf66 | Konfirmasi |
| Background | #f8f9fa | Light gray |

---

## 🔐 Security Notes

File `process_form.php` sudah ada dengan:
- ✓ Input validation & sanitization
- ✓ File type & size checking
- ✓ MIME type validation
- ✓ Secure filename generation

**Untuk Production:**
- Update `process_form.php` di bagian action form
- Setup CSRF protection
- Implement rate limiting
- Setup file upload directory dengan proper permissions
- Lihat `SECURITY_DEPLOYMENT.md` untuk checklist lengkap

---

## 📱 Testing Checklist

- [ ] Akses form di `http://localhost/form_DXI/`
- [ ] Isi nama lengkap
- [ ] Isi nomor HP (format otomatis ke 08... atau +62...)
- [ ] Isi Instagram (auto-add @ symbol)
- [ ] Isi alamat
- [ ] Upload bukti (drag & drop atau click)
- [ ] Pilih kategori (Macro atau Wide)
- [ ] Isi judul foto
- [ ] Upload file karya
- [ ] Isi EXIF data (opsional)
- [ ] Centang agreement
- [ ] Klik tombol "Daftarkan Karya"
- [ ] Lihat notifikasi success

---

## 🆘 Troubleshooting

### Form tidak muncul
- Pastikan mengakses `http://localhost/form_DXI/`
- Pastikan file `index.html` ada
- Check browser console (F12) untuk error

### File tidak bisa diupload
- Periksa ukuran file (max 20MB untuk foto, 5MB untuk bukti)
- Gunakan format JPG, PNG, atau TIFF
- Coba upload lagi dengan koneksi yang stabil

### Browser tidak support drag & drop
- Gunakan method click untuk upload
- Update browser ke versi terbaru
- Coba browser lain

---

## 📊 Admin Dashboard (Optional)

Akses di: `http://localhost/form_DXI/admin_dashboard.php`

**Default Password**: `UBAH_PASSWORD_INI` (UBAH untuk production!)

Fitur:
- 📊 Lihat statistik pendaftaran
- 📋 Lihat semua submissions
- 📥 Export data JSON
- 👁️ Lihat detail per peserta

---

## 🚀 Next Steps

1. **Test Form**: Verifikasi semua fitur berjalan dengan baik
2. **Customize**: Ubah warna, teks, atau fields sesuai kebutuhan
3. **Setup Backend**: Konfigurasi `process_form.php` dengan database/email
4. **Security**: Implementasikan checklist di `SECURITY_DEPLOYMENT.md`
5. **Deploy**: Pindahkan ke server production dengan HTTPS

---

## 📞 Support

Untuk pertanyaan atau issue:
1. Lihat `README.md` untuk dokumentasi lengkap
2. Lihat `SECURITY_DEPLOYMENT.md` untuk keamanan
3. Check browser console untuk error messages

---

**Dibuat dengan ❤️ untuk DXI**  
**Version**: 1.0.0  
**Last Updated**: February 2026

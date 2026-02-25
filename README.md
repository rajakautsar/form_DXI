# 📸 Kompetisi Fotografi Bawah Laut - Diver eXperience Indonesia

Sistem pendaftaran online untuk kompetisi fotografi bawah laut yang modern dan responsif.

## 📋 Fitur Utama

### A. Data Diri Peserta
- ✓ Input Nama Lengkap
- ✓ No. HP (WhatsApp)
- ✓ Username Instagram (dengan auto-formatting)
- ✓ Alamat Domisili
- ✓ Upload Bukti Follow dan Repost (Drag & Drop support)

### B. Kategori Lomba
- **Macro Angle**: Fokus detail kecil bawah laut
  - Nudibranch, Shrimp, Pygmy Seahorse, Tekstur karang
- **Wide Angle**: Fokus lanskap bawah laut
  - Reefscape, Diver Interaction, Schooling Fish, Big Marine Life

*File akan otomatis dipisahkan berdasarkan kategori untuk memudahkan penjurian*

### C. Upload Karya
- ✓ Input Judul Foto
- ✓ Upload File Karya (JPG, PNG, TIFF - Max 20MB)
- ✓ Input Informasi EXIF (Opsional):
  - Kamera, Lensa
  - Shutter Speed, Aperture, ISO
  - Lokasi Pengambilan

### D. Pernyataan Peserta
- ✓ Checkbox Agreement dengan 5 poin pernyataan
- ✓ Validasi otomatis
- ✓ Desain yang jelas dan mudah dipahami

## 🎨 Desain & UX

- **Modern & Responsive**: Desain contemporary dengan animasi halus
- **Mobile-Friendly**: Fully responsive untuk semua ukuran layar
- **Drag & Drop**: Kemudahan upload file dengan drag and drop
- **Real-time Validation**: Validasi input real-time dengan feedback yang jelas
- **Accessibility**: Keyboard navigation dan screen reader friendly
- **Local Storage**: Auto-save draft untuk mencegah data hilang

## 📁 Struktur Project

```
form_DXI/
├── index.html              # File utama form
├── assets/
│   ├── css/
│   │   └── style.css       # Styling modern
│   └── js/
│       └── script.js       # Interaktivitas & validasi
└── README.md               # Dokumentasi ini
```

## 🚀 Cara Menggunakan

### 1. Setup Lokal
```bash
# Copy folder ke xampp/htdocs
cp -r form_DXI /xampp/htdocs/

# Atau jika sudah di folder yang tepat
cd c:\xampp\htdocs\form_DXI
```

### 2. Jalankan di Browser
```
http://localhost/form_DXI/
atau
http://localhost/form_DXI/index.html
```

### 3. Testing
- Isi semua field yang diperlukan (ada tanda *)
- Upload file bukti dan karya menggunakan:
  - Click pada zone upload
  - Atau drag & drop file
- Pilih kategori (Macro Angle atau Wide Angle)
- Periksa semua pernyataan
- Submit form

## 🔧 Fitur Teknis

### Validasi
- ✓ Required field checking
- ✓ Format email validation
- ✓ Phone number auto-formatting
- ✓ Instagram username auto-@ formatting
- ✓ File type validation (JPG, PNG, TIFF)
- ✓ File size validation (20MB untuk foto, 5MB untuk bukti)

### Interaktivitas
- ✓ Auto-save draft setiap 30 detik ke localStorage
- ✓ Drag & drop file upload
- ✓ Visual feedback untuk kategori pilihan
- ✓ Notification system untuk status submission
- ✓ Smooth animations & transitions
- ✓ Category selection state management

### Responsiveness Breakpoints
- Desktop: 1024px+
- Tablet: 768px - 1023px
- Mobile: 480px - 767px
- Small Mobile: < 480px

## 🎯 Warna Brand
- **Primary**: #0066cc (Biru cerah)
- **Secondary**: #00a8cc (Biru aqua)
- **Accent**: #ff6b6b (Merah untuk error)
- **Success**: #51cf66 (Hijau untuk konfirmasi)

## 📝 Backend Integration

Untuk integrasi dengan backend, modify form action:

```html
<!-- Di index.html, ubah form tag menjadi: -->
<form class="competition-form" id="competitionForm" 
      enctype="multipart/form-data"
      action="process_form.php"
      method="POST">
```

### Data yang akan dikirim:
- `fullName`: string
- `phoneNumber`: string
- `instagram`: string
- `address`: string
- `proofFile`: file
- `category`: string (macro/wide)
- `photoTitle`: string
- `photoFile`: file
- `camera`: string
- `lens`: string
- `shutter`: string
- `aperture`: string
- `iso`: string
- `location`: string
- `agreement`: checkbox

## 📊 Browser Support

- ✓ Chrome/Edge 90+
- ✓ Firefox 88+
- ✓ Safari 14+
- ✓ Mobile browsers (iOS Safari, Chrome Mobile)

## 🔐 Security Notes

**Penting untuk production:**
1. Validasi file di server (MIME type check, virus scan)
2. Simpan uploaded files di folder aman, bukan public
3. Implement rate limiting untuk mencegah spam
4. Gunakan HTTPS
5. Implement CSRF tokens untuk form submission
6. Sanitize semua user inputs

## 🐛 Troubleshooting

### File tidak bisa diupload
- Pastikan ukuran file tidak melebihi batas (20MB foto, 5MB bukti)
- Gunakan format yang didukung: JPG, PNG, TIFF

### Form tidak submitting
- Periksa browser console untuk error messages
- Pastikan semua required fields sudah diisi
- Pastikan checkbox agreement sudah dicentang

### Drag & drop tidak bekerja
- Tergantung browser, pastikan browser sudah up-to-date
- Coba upload dengan click method

## 📞 Kontak & Support

Diver eXperience Indonesia
- Instagram: @DXI_Official
- Email: contact@dxiofficial.com

## 📄 Lisensi

Proprietary - Untuk Diver eXperience Indonesia

---

**Last Updated**: February 2026
**Version**: 1.0.0
# form_DXI  

# 📝 PANDUAN EDIT FILE DI HOSTINGER

## MASALAH
URL `/admin/export` tidak bisa diakses karena file `.htaccess` masih menggunakan path lama.

---

## SOLUSI: UPDATE `.htaccess` DI FOLDER `admin/`

### **STEP 1: Login ke Hostinger**

1. Buka browser
2. Pergi ke: https://hpanel.hostinger.com/
3. Login dengan email & password Anda
4. Pilih domain: **deepextremeindonesia.com**
5. Klik menu **Subdomains** atau cari subdomain **underwatershootout**

---

### **STEP 2: Buka File Manager**

1. Di hPanel, cari menu **File Manager**
2. Klik File Manager
3. Pastikan Anda di folder subdomain **underwatershootout**
4. Structure seharusnya:
   ```
   📁 public_html
      ├── 📄 index.html
      ├── 📄 process_form.php
      ├── 📁 assets/
      ├── 📁 admin/          ← Masuk sini
      ├── 📁 config/
      └── 📁 uploads/
   ```

---

### **STEP 3: Navigate ke Folder `admin/`**

1. Double-click folder **`admin/`**
2. Masuk folder `admin`
3. Sekarang Anda harus melihat file-file:
   ```
   📄 .htaccess           ← EDIT FILE INI!
   📄 export.php
   📄 export_csv.php
   📄 export_zip.php
   📄 admin_export.html
   📄 export_page.html
   🔧 ... (file lainnya)
   ```

---

### **STEP 4: Edit File `.htaccess`**

#### **Opsi A: Pakai Text Editor di File Manager**

1. **Cari file `.htaccess`** (mungkin tersembunyi, aktifkan "Show Hidden Files")
2. **Klik kanan pada `.htaccess`**
3. Pilih: **Edit** atau **View Source** atau **Edit With Code Editor**
4. File akan terbuka dalam text editor

#### **Opsi B: Download & Edit Local**

1. **Klik kanan `.htaccess`**
2. Pilih: **Download**
3. Buka file dengan **Notepad** atau text editor
4. Lanjut ke STEP 5

---

### **STEP 5: Lihat Isi File Saat Ini**

File `.htaccess` Anda sekarang seperti ini:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /form_DXI/admin/
    
    # Remove .php extension
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.+?)/?$ $1.php [L]
</IfModule>
```

---

### **STEP 6: UPDATE FILE**

**UBAH BARIS INI:**

```apache
RewriteBase /form_DXI/admin/
```

**MENJADI:**

```apache
RewriteBase /admin/
```

---

### **FINAL RESULT**

Setelah edit, file `.htaccess` seharusnya seperti ini:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /admin/
    
    # Remove .php extension
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.+?)/?$ $1.php [L]
</IfModule>
```

---

### **STEP 7: SAVE FILE**

**Jika pakai Editor di File Manager:**
- Klik **Save** atau **Ctrl+S**
- Tunggu sampai ada notifikasi "Saved successfully"

**Jika download & edit local:**
- Edit di Notepad
- Klik **File** → **Save**
- Upload file kembali ke Hostinger File Manager di folder `admin/`
- Klik **Replace** jika ada konflik

---

### **STEP 8: TEST**

1. Buka browser
2. Akses URL:
   ```
   https://underwatershootout.deepextremeindonesia.com/admin/export
   ```

3. **Expected Result:**
   - ✅ Halaman export dimuat dengan benar
   - ✅ Bisa lihat form untuk download CSV & ZIP
   - ✅ Tombol export berfungsi

---

## ❌ JIKA MASIH ERROR

### **Problem: 404 Not Found**

**Solusi:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Refresh page (Ctrl+F5) / Hard Refresh
3. Cek ulang path di `.htaccess` sudah benar atau belum
4. Pastikan file sudah di-save

### **Problem: 500 Internal Server Error**

**Solusi:**
1. Check syntax `.htaccess` - pastikan tidak ada typo
2. Verify indentation sudah benar
3. Check di Hostinger error logs via SSH:
   ```bash
   ssh -p 2222 username@hostinger.com
   tail -50 error_log
   ```

---

## 📋 CHECKLIST

- [ ] Login ke Hostinger hPanel
- [ ] Buka File Manager
- [ ] Navigate ke folder `admin/`
- [ ] Buka file `.htaccess` untuk edit
- [ ] Ubah `RewriteBase /form_DXI/admin/` → `RewriteBase /admin/`
- [ ] Save file
- [ ] Test akses `/admin/export`
- [ ] Verify halaman export bisa dibuka
- [ ] ✅ Done!

---

## 💡 CATATAN PENTING

- **Jangan edit file lain** sekarang, hanya `.htaccess`
- **Jangan hapus atau tambah baris** yang lain
- **Gunakan `/admin/` bukan `/form_DXI/admin/`**
- **Case sensitive** - pastikan huruf besar/kecil benar

---

**Last Updated:** March 3, 2026
**Status:** Ready for Implementation ✅

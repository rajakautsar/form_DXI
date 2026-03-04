# 🚀 GET STARTED GUIDE - Langkah demi Langkah

Panduan lengkap untuk setup, testing, dan deploy sistem DXI.

---

## 📌 STEP 1: DATABASE SETUP (5 minutes)

### A. Start XAMPP
1. Buka `C:\xampp\xampp-control.exe`
2. Klik **Start** untuk Apache dan MySQL (tunggu sampai hijau)

### B. Buat Database
1. Buka browser → `http://localhost/phpmyadmin`
2. Login (biasanya username: `root`, password: kosong)
3. Klik **Databases** (tab atas)
4. Di "Create database" section:
   - **Database name:** `DXI_db`
   - **Collation:** `utf8mb4_unicode_ci`
5. Klik **Create**

### C. Import Schema
1. Pilih database `DXI_db` dari sidebar kiri
2. Klik tab **Import** (atas)
3. Klik **Browse** → pilih file `DXI_db.sql`
   ```
   c:\xampp\htdocs\form_DXI\DXI_db.sql
   ```
4. Klik **Go** (bawah)
5. **Done!** ✅

### D. Verify Schema
1. Di sidebar, expand `DXI_db`
2. Harus ada tables:
   - ✅ `submissions`
   - ✅ `photo_files`
   - ✅ `admin_users`
   - ✅ `admin_actions`
3. Harus ada views:
   - ✅ `vw_submission_stats`
   - ✅ `vw_recent_submissions`

---

## 🧪 STEP 2: TEST FORM (10 minutes)

### A. Open Form
```
http://localhost/form_DXI/
```

### B. Fill Test Data
```
Nama Lengkap:     John Doe
No. HP:           081234567890
Instagram:        @johndoe
Alamat:           Jl. Test No. 123, Jakarta
```

### C. Select Category
- Pilih **Macro Angle** (untuk detail kecil)
- Atau **Wide Angle** (untuk lanskap)

### D. Upload Files
1. **Photo File**: Pilih image (JPG/PNG/TIFF, max 20MB)
   - Bisa 1-3 files
2. **Proof File**: Upload image (max 5MB)
3. **EXIF File**: Upload TXT/PDF/XLSX (max 5MB)

### E. Check Agreement
- ✅ Cek checkbox "Saya menyatakan bahwa..."

### F. Submit
- Klik **"Daftarkan Karya"** button
- Tunggu "Pendaftaran berhasil!" message

### G. Verify in Database
1. Buka PHPMyAdmin
2. Query: `SELECT * FROM submissions ORDER BY id DESC LIMIT 1`
3. Lihat data yang baru disubmit

---

## 📊 STEP 3: TEST ADMIN PANEL (5 minutes)

### A. Open Admin Dashboard
```
http://localhost/form_DXI/admin/admin.html
```

### B. View Statistics
- **Total Submissions**: harus ada 1 (dari test sebelumnya)
- **Macro Count / Wide Count**: sesuai kategori yang dipilih
- **Total Foto": sesuai jumlah file yang diupload

### C. Test Export CSV
1. Klik **"Download CSV"** button
2. File `DXI_submissions_*.csv` di-download
3. Buka di Excel atau text editor
4. Verifikasi data ada

### D. Test Export ZIP
1. Klik **"Download ZIP"** button
2. File `DXI_Submissions_Export_*.zip` di-download
3. Extract ZIP
4. Verifikasi struktur:
   ```
   /data_submissions.csv
   /submissions/
     /John Doe/
       /macro/
         /00001_Test Photo/
           - photo1.jpg
           - bukti_*.jpg
           - exif_*.txt
   ```

---

## ⚙️ STEP 4: FOLDER PERMISSIONS (2 minutes)

### A. Set Folder Permissions
Di Windows Command Prompt (Run as Administrator):
```cmd
cd c:\xampp\htdocs\form_DXI

:: Set permissions (Windows)
icacls uploads /grant Everyone:(F) /T
icacls uploads\macro /grant Everyone:(F) /T
icacls uploads\wide /grant Everyone:(F) /T
icacls uploads\proof /grant Everyone:(F) /T
icacls uploads\exif /grant Everyone:(F) /T
```

Atau di terminal Linux/macOS:
```bash
cd /var/www/html/form_DXI
chmod 755 uploads/
chmod 755 uploads/macro/
chmod 755 uploads/wide/
chmod 755 uploads/proof/
chmod 755 uploads/exif/
```

---

## 🔐 STEP 5: SECURITY CHECK (5 minutes)

### A. Verify .htaccess Files
1. Check file ada:
   - ✅ `/form_DXI/.htaccess` (root)
   - ✅ `/form_DXI/uploads/.htaccess`
   - ✅ `/form_DXI/admin/.htaccess`

2. Test: Try upload `.php` file → harus **rejected**

### B. Check Database Config
- File: `config/database.php`
- Verify credentials match your setup:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');    // Update if needed
  define('DB_NAME', 'DXI_db');
  ```

---

## 📋 STEP 6: FULL TESTING (30 minutes)

### Complete Test Checklist
See detailed test guide: [`TESTING_CHECKLIST.md`](TESTING_CHECKLIST.md)

Quick test summary:
- [ ] Form displays correctly
- [ ] All validations work
- [ ] File upload works (drag-drop, click)
- [ ] Submission saves to database
- [ ] Files saved to correct folders
- [ ] Admin stats update
- [ ] Export CSV works
- [ ] Export ZIP works
- [ ] Database queries return correct data
- [ ] No console errors in DevTools

Run these tests across:
- [ ] Chrome / Firefox / Edge
- [ ] Desktop (1024px+)
- [ ] Mobile (375px)

---

## 🚀 STEP 7: DEPLOYMENT (Production Setup)

### A. Get Production Server
- Hostinger, Domainesia, atau VPS lain
- Requirements: PHP 7.4+, MySQL 5.7+

### B. Upload Files
```bash
# Via FTP/SFTP
1. Upload seluruh folder form_DXI ke public_html/
2. Upload DXI_db.sql terpisah
```

### C. Setup Production Database
1. Login ke cPanel/Hostinger panel
2. Create database `DXI_db`
3. Import `DXI_db.sql`
4. Create database user (not root)

### D. Update Config
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'dxi_user');        // Production user
define('DB_PASS', 'strong_password'); // Secure password
define('DB_NAME', 'DXI_db');
```

### E. Verify
```
https://yourdomain.com/form_DXI/
https://yourdomain.com/form_DXI/admin/
```

---

## 🆘 TROUBLESHOOTING

### Problem: "Database connection error"
**Solution:**
1. Verify MySQL running (XAMPP)
2. Verify database `DXI_db` exists
3. Verify credentials di config/database.php
4. Check MySQL error log di XAMPP folder

### Problem: "File upload failed"
**Solution:**
1. Check folder permissions (755)
2. Check disk space
3. Verify max upload size di php.ini
   ```
   post_max_size = 50M
   upload_max_filesize = 50M
   ```
4. Restart Apache/MySQL

### Problem: "4 atau 500 error saat submit"
**Solution:**
1. Check PHP error log
2. Open DevTools (F12) → Network tab
3. Check response JSON message
4. Verify all validators passing

### Problem: "Export ZIP empty"
**Solution:**
1. Verify files exist di /uploads/
2. Check file permissions
3. Check disk space available
4. Try export CSV first (simpler)

### Problem: Form tidak responsive di mobile
**Solution:**
1. Check viewport meta tag di index.html
2. Check CSS breakpoints di assets/css/style.css
3. Test di DevTools mobile simulator (F12)

---

##  🎯 SUCCESS CHECKLIST

When completed, you have:

- ✅ Database schema imported
- ✅ Form working on desktop & mobile
- ✅ File upload & storage working
- ✅ Admin panel functional
- ✅ Export CSV/ZIP working
- ✅ Security configured
- ✅ All tests passing
- ✅ Ready for production

---

## 📚 Documentation Files

All guides inside `/form_DXI/`:

| File | Purpose |
|------|---------|
| `README.md` | Feature overview |
| `QUICK_START.md` | Quick reference |
| `DATABASE_SETUP.md` | Database details |
| `TESTING_CHECKLIST.md` | Detailed tests |
| `IMPROVEMENTS.md` | What was fixed |
| `SECURITY_DEPLOYMENT.md` | Security guide |
| **`GET_STARTED.md`** | **This guide** |

---

## 💬 Support Tips

**If something doesn't work:**

1. **Check Browser Console** (F12 → Console tab)
   - Look for JavaScript errors
   - Check Network responses

2. **Check PHP Error Log**
   - Windows: `C:\xampp\apache\logs\error.log`
   - Linux: `/var/log/apache2/error.log`

3. **Check Database**
   - Use PHPMyAdmin to verify data
   - Run queries to test

4. **Restart Services**
   ```
   Apache → Stop → Start
   MySQL → Stop → Start
   ```

5. **Check File Permissions**
   ```
   /uploads/ must be readable/writable
   chmod 755 or rwxr-xr-x
   ```

---

**🎉 You're ready to go! Start with STEP 1 and follow the guide in order.** 🎉

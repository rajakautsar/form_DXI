# 🗄️ DATABASE SETUP - DXI_db

## OVERVIEW

Project ini menggunakan **MySQL Database** untuk menyimpan data submissions peserta. Database setup sederhana menggunakan **PHPMyAdmin** (interface web yang disediakan XAMPP).

---

## 📋 DATABASE INFO

| Item | Value |
|------|-------|
| **Database Name** | `DXI_db` |
| **Tables** | 3 (submissions, photo_files, admin_actions) |
| **Views** | 2 (vw_submission_stats, vw_recent_submissions) |
| **Setup Method** | PHPMyAdmin SQL Importer |

---

## ⚙️ SETUP STEPS (LOCAL)

### **STEP 1: Start XAMPP**

1. Buka `C:\xampp\xampp-control.exe`
2. Klik **Start** untuk:
   - ✅ **Apache** (hijau)
   - ✅ **MySQL** (hijau)

### **STEP 2: Buka PHPMyAdmin**

Buka browser, ketik:
```
http://localhost/phpmyadmin
```

atau 

```
http://127.0.0.1/phpmyadmin
```

**Expected:** PHPMyAdmin interface muncul

### **STEP 3: Create Database**

1. **Login ke PHPMyAdmin** (biasanya tidak memerlukan password di XAMPP local)
2. Klik **Databases** tab (atas)
3. Di bagian **"Create database"**, ketik:
   - Database name: `DXI_db`
   - Collation: `utf8mb4_unicode_ci`
4. Klik **Create**

**Expected:** Database `DXI_db` berhasil dibuat ✅

### **STEP 4: Import SQL Schema**

1. **Select database `DXI_db`** dari sidebar kiri
2. Klik tab **Import** (atas)
3. Klik **Browse** → pilih file:
   ```
   C:\xampp\htdocs\form_DXI\DXI_db.sql
   ```
4. Klik **Go** (bawah)

**Expected:** Semua table & views berhasil di-create ✅

### **STEP 5: Verify Structure**

1. Select `DXI_db` database
2. Lihat di sidebar, harus ada:
   - **Tables:**
     - ✅ `submissions` (data submissions)
     - ✅ `photo_files` (tracking files)
     - ✅ `admin_actions` (audit trail)
   - **Views:**
     - ✅ `vw_submission_stats` (statistics)
     - ✅ `vw_recent_submissions` (recent 20 submissions)

---

## 🔧 DATABASE CONNECTION CONFIG

File: `config/database.php`

### **Local (XAMPP Default)**

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty for XAMPP default
define('DB_NAME', 'DXI_db');
define('DB_PORT', 3306);
```

### **Production (Hostinger)**

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost'); // atau hostname dari Hostinger
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'DXI_db');  // or nama lain sesuai Hostinger
define('DB_PORT', 3306);
```

**Dapatkan dari Hostinger:**
- Login ke hPanel
- Go to: **Databases** atau **MySQL Databases**
- Lihat credentials database yang tersedia

---

## 📊 TABLE STRUCTURE

### **Table: `submissions`**

Menyimpan data peserta yang submit form.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Primary key, auto increment |
| `uuid` | VARCHAR(50) | Unique ID: DXI_xxxxx_timestamp |
| `full_name` | VARCHAR(100) | Nama lengkap peserta |
| `phone_number` | VARCHAR(20) | No. HP peserta |
| `instagram` | VARCHAR(100) | Username Instagram |
| `address` | TEXT | Alamat domisili |
| `category` | ENUM | 'macro' atau 'wide' |
| `category_label` | VARCHAR(50) | Human readable (Macro Angle / Wide Angle) |
| `photo_title` | VARCHAR(200) | Judul karya foto |
| `photo_count` | INT | Jumlah foto yang diupload |
| `photo_files` | JSON | Array nama file foto |
| `proof_file` | VARCHAR(255) | Nama file bukti follow |
| `exif_file` | VARCHAR(255) | Nama file Exif data |
| `camera` | VARCHAR(100) | Nama kamera |
| `lens` | VARCHAR(100) | Nama lensa |
| `shutter` | VARCHAR(50) | Shutter speed |
| `aperture` | VARCHAR(50) | Aperture value |
| `iso` | VARCHAR(50) | ISO value |
| `location` | VARCHAR(200) | Lokasi pengambilan foto |
| `agreement` | BOOLEAN | 1 = agree, 0 = disagree |
| `submitted_at` | TIMESTAMP | Waktu submit |
| `updated_at` | TIMESTAMP | Waktu update terakhir |
| `ip_address` | VARCHAR(45) | IP address submitter |
| `user_agent` | TEXT | Browser info |

### **Table: `photo_files`**

Tracking individual file uploads (optional, untuk audit trail).

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Primary key |
| `submission_id` | INT | Foreign key ke submissions |
| `file_name` | VARCHAR(255) | Nama file |
| `file_type` | ENUM | 'photo', 'proof', atau 'exif' |
| `category` | ENUM | 'macro' atau 'wide' (untuk photo files) |
| `file_size` | INT | Size dalam bytes |
| `original_name` | VARCHAR(255) | Nama file original |
| `uploaded_at` | TIMESTAMP | Waktu upload |

### **Table: `admin_actions`**

Audit trail untuk admin actions (delete, export, etc).

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT | Primary key |
| `action` | VARCHAR(100) | Nama action (delete_all, export_csv, etc) |
| `description` | TEXT | Deskripsi detail |
| `ip_address` | VARCHAR(45) | IP admin |
| `user_agent` | TEXT | Browser info |
| `timestamp` | TIMESTAMP | Waktu action |

---

## 📈 VIEWS

### **View: `vw_submission_stats`**

Menampilkan statistik agregat:
- Total submissions
- Count kategori macro
- Count kategori wide
- Total photo count
- Latest submission

**Usage:**
```sql
SELECT * FROM vw_submission_stats;
```

### **View: `vw_recent_submissions`**

Menampilkan 20 submissions terbaru (untuk dashboard).

**Usage:**
```sql
SELECT * FROM vw_recent_submissions;
```

---

## 🧪 TESTING

### **Test 1: Submit Form**

1. Buka: `http://localhost:8000/` (atau `/form_DXI/`)
2. Isi form lengkap
3. Submit
4. Check database:
   - Buka PHPMyAdmin
   - Select `DXI_db` → `submissions`
   - Klik **Browse** tab
   - Verify row baru ada dengan data yang benar

### **Test 2: Check Admin Stats**

1. Buka: `http://localhost:8000/admin/`
2. Verify statistik loading
3. Submit beberapa form
4. Refresh admin page
5. Verify stats terupdate

### **Test 3: Check Audit Log**

1. Klik "Delete All Data" di admin
2. Input password: `admin123`
3. Confirm
4. Check database:
   - PHPMyAdmin → `DXI_db` → `admin_actions`
   - Verify row ada dengan action `delete_all_data`

---

## 🔒 SECURITY TIPS

1. **Change Database Password (Production)**
   - Dont gunakan default credentials
   - Set strong password di Hostinger

2. **Restrict Database Access**
   - MySQL hanya accessible dari localhost
   - Dont expose credentials di git/public

3. **Regular Backups**
   ```sql
   -- Export backup dari PHPMyAdmin
   -- Select database → Export → Go
   ```

4. **Monitor Submissions**
   ```sql
   -- Check terbaru
   SELECT * FROM submissions ORDER BY submitted_at DESC LIMIT 10;
   ```

---

## 🛠️ MAINTENANCE QUERIES

### **View all data**
```sql
SELECT * FROM submissions ORDER BY submitted_at DESC;
```

### **View macro submissions only**
```sql
SELECT * FROM submissions WHERE category = 'macro' ORDER BY submitted_at DESC;
```

### **View wide submissions only**
```sql
SELECT * FROM submissions WHERE category = 'wide' ORDER BY submitted_at DESC;
```

### **Get statistics**
```sql
SELECT * FROM vw_submission_stats;
```

### **Count submissions per day**
```sql
SELECT DATE(submitted_at) as day, COUNT(*) as count FROM submissions GROUP BY DATE(submitted_at);
```

### **Delete specific submission (by ID)**
```sql
DELETE FROM submissions WHERE id = 5;
```

### **Delete specific submission (by UUID)**
```sql
DELETE FROM submissions WHERE uuid = 'DXI_xxx_yyy';
```

### **Backup data (export)**
- Via PHPMyAdmin GUI → Database → Export

---

## 📝 TROUBLESHOOTING

### **Problem: Connection Failed**

**Error:** `Connection failed: Access denied for user 'root'@'localhost'`

**Solution:**
1. Check MySQL running (XAMPP Control Panel)
2. Verify credentials di `config/database.php`
3. Default XAMPP: user=`root`, password=`` (kosong)

### **Problem: Table Not Found**

**Error:** `Table DXI_db.submissions doesn't exist`

**Solution:**
1. Check database `DXI_db` sudah create
2. Import SQL schema file (`DXI_db.sql`)
3. Verify tables ada di PHPMyAdmin

### **Problem: Can't Connect to Database**

**Check:**
1. MySQL service running?
2. Database credentials benar?
3. Database `DXI_db` exist?
4. User `root` punya privilege?

---

## 📤 PRODUCTION MIGRATION

Dari local (JSON) ke production (MySQL):

1. **Create database di Hostinger** (via cPanel/hPanel)
2. **Update `config/database.php`** dengan Hostinger credentials
3. **Run SQL schema** di Hostinger database
4. **Update PHP files** yang sudah dilakukan:
   - ✅ `process_form.php` - insert ke database
   - ✅ `admin/get_stats.php` - query dari database
   - ✅ `admin/delete_all.php` - delete dari database

---

## 📞 REFERENCES

- **MySQL Documentation:** https://dev.mysql.com/doc/
- **PHPMyAdmin:** https://www.phpmyadmin.net/
- **XAMPP:** https://www.apachefriends.org/
- **Hostinger MySQL Setup:** https://support.hostinger.com/article/

---

**Last Updated:** March 3, 2026
**Database Version:** 1.0
**Status:** ✅ Ready for Production

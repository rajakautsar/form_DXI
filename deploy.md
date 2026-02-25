# 🚀 PANDUAN DEPLOYMENT LENGKAP - DXI Kompetisi Fotografi

## 📋 DAFTAR ISI
1. [Setup GitHub](#1-setup-github)
2. [Konfigurasi Lokal](#2-konfigurasi-lokal)
3. [Push ke GitHub](#3-push-ke-github)
4. [Setup Hostinger](#4-setup-hostinger)
5. [Deploy ke Hostinger](#5-deploy-ke-hostinger)
6. [Konfigurasi Production](#6-konfigurasi-production)
7. [Testing & Troubleshooting](#7-testing--troubleshooting)
8. [Maintenance](#8-maintenance)

---

## 1. SETUP GITHUB

### 1.1 Buat Repository di GitHub

1. **Login ke GitHub** → https://github.com/
2. **Klik "New"** (tombol hijau di atas kiri)
3. **Isi detail:**
   - Repository name: `form-dxi` (atau sesuai preferensi)
   - Description: `Sistem Pendaftaran Kompetisi Fotografi Bawah Laut - Diver eXperience Indonesia`
   - Visibility: **Private** (jika data sensitif) atau **Public**
   - ✅ Initialize dengan: Add a README

4. **Klik "Create repository"**

### 1.2 Copy Repository URL

Setelah repository dibuat, copy URL:
```
https://github.com/username/form-dxi.git
```

atau pakai SSH:
```
git@github.com:username/form-dxi.git
```

---

## 2. KONFIGURASI LOKAL

### 2.1 Setup Git di Local Machine

**Buka Command Prompt / PowerShell di folder `form_DXI`:**

```bash
cd c:\xampp\htdocs\form_DXI
```

### 2.2 Inisialisasi Git Repository

```bash
# Initialize git
git init

# Configure user untuk commit
git config user.name "Nama Anda"
git config user.email "email@example.com"

# Atau global (optional):
git config --global user.name "Nama Anda"
git config --global user.email "email@example.com"
```

### 2.3 Add Remote Repository

```bash
# Hubungkan dengan repository GitHub
git remote add origin https://github.com/username/form-dxi.git

# Verify remote
git remote -v
```

### 2.4 Create `.gitignore` (Jika belum ada)

Pastikan file `.gitignore` ada dan berisi:

```
# Uploaded files
uploads/
submissions/
temp/

# Temporary files
*.tmp
*.log
.DS_Store
Thumbs.db

# Environment
.env
.env.local

# IDE
.vscode/
.idea/
*.sublime-*

# Node (jika ada)
node_modules/
npm-debug.log

# Backups
*.bak
*.backup
```

---

## 3. PUSH KE GITHUB

### 3.1 Stage & Commit Files

```bash
# Stage semua file
git add .

# Verify file yang akan di-commit
git status

# Commit dengan message
git commit -m "Initial commit: DXI Kompetisi Fotografi form"
```

### 3.2 Push ke GitHub

```bash
# Push ke branch main
git branch -M main
git push -u origin main
```

**Jika ada error authentication:**

**Option A: HTTPS + Token (Recommended)**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate token dengan scope: `repo`, `workflow`
3. Copy token
4. Paste saat diminta password di Git Bash

**Option B: SSH Keys**
```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "email@example.com"

# Copy public key ke GitHub Settings → SSH Keys
cat ~/.ssh/id_ed25519.pub
```

### 3.3 Verify Push Berhasil

Buka GitHub → Repository → Lihat files sudah terupload ✅

---

## 4. SETUP HOSTINGER

### 4.1 Login ke hPanel Hostinger

1. Buka https://hpanel.hostinger.com/
2. Login dengan credential Hostinger
3. Pilih domain yang akan dipakai

### 4.2 Akses File Manager

1. Di hPanel, pilih **File Manager**
2. Navigate ke folder **public_html** (atau folder sesuai domain)

### 4.3 Setup Terminal/SSH (Recommended untuk deploy)

#### A. Via SSH (Terminal Linux/Mac atau Git Bash di Windows)

1. Buka SSH credentials di hPanel:
   - **Account Settings** → **SSH Keys** atau **FTP & SSH**
   - Catat: `Host`, `Port`, `Username`, `Password`

2. Buka Terminal/Command Prompt:

```bash
# Login ke Hostinger via SSH
ssh -p 2222 username@your.hostinger.com

# Input password saat diminta
```

#### B. Atau Gunakan File Manager GUI (Lebih mudah untuk pemula)

---

## 5. DEPLOY KE HOSTINGER

### 5.1 Deploy via Git (Recommended)

**Di SSH Hostinger (setelah login):**

```bash
# Navigate ke public_html
cd public_html

# Clone repository
git clone https://github.com/username/form-dxi.git form_DXI

# Atau jika sudah ada folder, update:
cd form_DXI
git pull origin main
```

### 5.2 Deploy via File Manager (Jika tidak bisa SSH)

1. **Download project dari local:**
   - Di folder `form_DXI`, zip semua file (kecuali `uploads/` dan `node_modules/`)

2. **Upload via File Manager:**
   - Drag & drop ZIP file ke `public_html` di Hostinger File Manager
   - Extract di Hostinger

3. **Folder Structure setelah upload:**
   ```
   public_html/
   └── form_DXI/
       ├── index.html
       ├── process_form.php
       ├── assets/
       ├── admin/
       ├── config/
       └── uploads/ (buat folder baru)
   ```

### 5.3 Create Folders yang Diperlukan

**Via SSH:**
```bash
cd public_html/form_DXI

# Create uploads directory
mkdir -p uploads/macro
mkdir -p uploads/wide
mkdir -p uploads/proof
mkdir -p uploads/exif

# Set permissions
chmod 755 uploads
chmod 755 uploads/macro
chmod 755 uploads/wide
chmod 755 uploads/proof
```

**Via File Manager:**
- Klik "New Folder" di setiap lokasi yang diperlukan

---

## 6. KONFIGURASI PRODUCTION

### 6.1 Update `process_form.php`

Edit [process_form.php](process_form.php) dan update:

**Line 17-18 (CORS Header):**

Dari:
```php
header('Access-Control-Allow-Origin: *');
```

Ke:
```php
header('Access-Control-Allow-Origin: https://yourdomain.com');
header('Access-Control-Allow-Methods: POST');
```

**Line 24-25 (Upload directory path):**

Pastikan path absolut untuk production:
```php
$uploadDir = '/home/your-cpanel-username/public_html/form_DXI/uploads/';
```

Untuk cek path yang benar di Hostinger:
```bash
# SSH ke Hostinger
pwd
# Output example: /home/yourusername

# Maka upload dir:
# /home/yourusername/public_html/form_DXI/uploads/
```

### 6.2 Setup `.htaccess` untuk Security

**Create file `uploads/.htaccess`:**

```apache
# Prevent direct execution of PHP files in uploads
<FilesMatch "\.(php|phtml|php3|php4|php5|phps|pht)$">
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes

# Allow only specified image types
<FilesMatch "!(\.jpg|\.jpeg|\.png|\.gif|\.tiff|\.csv|\.zip)$">
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteRule ^.*$ - [F]
    </IfModule>
</FilesMatch>
```

**Create file `admin/.htaccess`:**

```apache
# Protect admin folder with basic auth
AuthType Basic
AuthName "Admin Panel - DXI Kompetisi"
AuthUserFile /home/yourusername/.htpasswd
Require valid-user
```

**Create `.htpasswd` file (via SSH):**

```bash
# Create password file
htpasswd -c /home/yourusername/.htpasswd admin_user
# Enter password: your_secure_password

# Verify created
cat /home/yourusername/.htpasswd
```

### 6.3 Update PHP Settings (Production)

**Via hPanel:**
1. Buka **PHP Settings**
2. Pastikan:
   - PHP version: **8.0 atau lebih tinggi**
   - `upload_max_filesize = 20M`
   - `post_max_size = 20M`
   - `max_file_uploads = 10`

**Atau via `.htaccess` di root:**

```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 20M
    php_value post_max_size 20M
    php_value max_file_uploads 10
</IfModule>
```

### 6.4 Setup Error Logging

**Create `logs/` folder:**

```bash
mkdir -p logs
chmod 755 logs
```

**Update `process_form.php` Line 11-12:**

```php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
```

---

## 7. TESTING & TROUBLESHOOTING

### 7.1 Test Form di Production

1. **Buka form:**
   ```
   https://yourdomain.com/form_DXI/
   ```

2. **Test upload file:**
   - Isi semua field
   - Upload file bukti (JPG/PNG)
   - Upload karya (JPG/PNG/TIFF)
   - Submit

3. **Verify files tersimpan:**
   ```bash
   # SSH ke Hostinger
   cd /home/yourusername/public_html/form_DXI/uploads
   ls -la
   ls -la macro/
   ls -la wide/
   ls -la proof/
   ```

4. **Check submissions:**
   ```bash
   cat uploads/submissions.json
   ```

### 7.2 Test Admin Export

1. **Akses admin:**
   ```
   https://yourdomain.com/form_DXI/admin/export/
   ```

2. **Masukkan password** yang sudah di-setup di `.htpasswd`

3. **Test export:**
   - Klik "Download CSV"
   - Klik "Download ZIP"
   - Verify file bisa di-download

### 7.3 Common Issues & Solution

#### ❌ Error: "Permission denied" pada uploads folder

**Solution:**
```bash
chmod 755 /home/yourusername/public_html/form_DXI/uploads
chmod 755 /home/yourusername/public_html/form_DXI/uploads/macro
chmod 755 /home/yourusername/public_html/form_DXI/uploads/wide
chmod 755 /home/yourusername/public_html/form_DXI/uploads/proof
```

#### ❌ Error: "500 Internal Server Error"

**Check PHP error log:**
```bash
cat logs/php_errors.log
# atau di hPanel → Logs
```

**Common causes:**
- PHP version terlalu lama (upgrade ke PHP 8.0+)
- Missing extensions (fileinfo, json)
- Path configuration salah

#### ❌ Error: "File upload failed"

**Check:**
1. Folder permissions (harus 755)
2. PHP config `upload_max_filesize`
3. Available disk space

```bash
# Check disk usage
df -h

# Check inode usage
df -i
```

#### ❌ File tidak tersimpan tapi form submit success

**Kemungkinan:**
- Path di `process_form.php` salah
- Folder tidak writable

**Debug dengan:**
```php
// Di process_form.php, tambah:
error_log("Upload dir: " . $uploadDir);
error_log("Is writable: " . (is_writable($uploadDir) ? 'YES' : 'NO'));
```

---

## 8. MAINTENANCE

### 8.1 Regular Git Updates

**Setelah update file lokal:**

```bash
cd c:\xampp\htdocs\form_DXI

# Stage changes
git add .

# Commit
git commit -m "Update: [deskripsi perubahan]"

# Push
git push origin main
```

**Update di Hostinger:**

```bash
# SSH ke Hostinger
cd public_html/form_DXI
git pull origin main
```

### 8.2 Backup Data Submissions

**Via SSH (setiap hari/minggu):**

```bash
# Backup submissions
cp uploads/submissions.json backups/submissions_$(date +%Y-%m-%d_%H-%M-%S).json

# Backup seluruh folder uploads
tar -czf backups/uploads_backup_$(date +%Y-%m-%d).tar.gz uploads/
```

**Via File Manager:**
- Download folder `uploads/` secara berkala
- Simpan di server backup atau cloud storage

### 8.3 Monitor Disk Usage

```bash
# Check size folder
du -sh public_html/form_DXI/
du -sh public_html/form_DXI/uploads/

# List largest files
find uploads/ -type f -size +10M
```

### 8.4 Clean Old Files (Optional)

```bash
# Hapus file submission lebih dari 6 bulan
find uploads/ -type f -mtime +180 -delete

# Atau backup dulu sebelum delete
find uploads/ -type f -mtime +180 -exec mv {} backups/ \;
```

### 8.5 Security Audit Checklist

- [ ] `.htaccess` di `uploads/` mencegah PHP execution
- [ ] `.htaccess` di `admin/` protect dengan password
- [ ] PHP error logging aktif
- [ ] CORS header restrict key domain only
- [ ] Backup submissions.json rutin (daily/weekly)
- [ ] Monitor file permissions (755 untuk folder, 644 untuk file)
- [ ] Check PHP version up-to-date
- [ ] Review logs untuk suspicious activity

---

## 📊 DEPLOYMENT CHECKLIST

Sebelum go-live:

### Pre-Deployment
- [ ] Git repository dibuat & dikonfigurasikan
- [ ] `.gitignore` proper setup
- [ ] Semua file ter-commit dengan baik
- [ ] GitHub repository accessible

### Hostinger Setup
- [ ] SSH access tested & working
- [ ] Folder structure created (`uploads/macro`, `wide`, `proof`, `exif`)
- [ ] File permissions correct (755 for dirs, 644 for files)
- [ ] `.htaccess` files in place (uploads & admin)
- [ ] `.htpasswd` created untuk admin protection
- [ ] PHP version 8.0+
- [ ] Upload max filesize configured (20M)

### Production Configuration
- [ ] `process_form.php` updated dengan production paths & CORS
- [ ] Error logging configured
- [ ] HTTPS enabled (cek di hPanel, usually automatic)
- [ ] Domain pointing correctly

### Testing
- [ ] Form loads properly
- [ ] File upload working
- [ ] Files saved to correct folder
- [ ] Admin panel accessible with password
- [ ] Export CSV & ZIP working
- [ ] Submissions.json being created/updated

### Final
- [ ] Test dengan data real
- [ ] Performance OK
- [ ] No error di logs
- [ ] Backup setup in place

---

## 🆘 QUICK REFERENCE - USEFUL COMMANDS

### Git
```bash
git status                           # Check status
git add .                           # Stage all files
git commit -m "message"             # Commit
git push origin main                # Push to GitHub
git pull origin main                # Pull from GitHub
git log --oneline                   # View commit history
```

### SSH (di Hostinger)
```bash
cd public_html/form_DXI             # Navigate to folder
ls -la                              # List files with permissions
chmod 755 uploads                   # Change permissions
mkdir -p uploads/macro              # Create directory
pwd                                 # Print working directory
cat uploads/submissions.json        # View JSON
tail -f logs/php_errors.log         # Live log viewing
du -sh .                            # Check folder size
```

### File Manager (Hostinger GUI)
- Right-click folder → Change Permissions → 755
- Right-click file → Change Permissions → 644
- Create new folder
- Upload/Download files

---

## 📞 SUPPORT & RESOURCES

- **GitHub Docs:** https://docs.github.com/
- **Hostinger Help:** https://support.hostinger.com/
- **PHP Manual:** https://www.php.net/manual/
- **Apache .htaccess:** https://httpd.apache.org/docs/current/mod/mod_rewrite.html

---

**Last Updated:** February 25, 2026
**Version:** 1.0
**Status:** Production Ready ✅

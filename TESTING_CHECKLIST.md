# 📋 TESTING & DEPLOYMENT CHECKLIST

## ✅ Pre-Testing Setup

### Database Initialization
- [ ] Start XAMPP (Apache + MySQL)
- [ ] Open PHPMyAdmin: `http://localhost/phpmyadmin`
- [ ] Create database `DXI_db` (charset: utf8mb4_unicode_ci)
- [ ] Import `DXI_db.sql` file via PHPMyAdmin
- [ ] Verify tables exist: submissions, photo_files, admin_users, admin_actions
- [ ] Verify views exist: vw_submission_stats, vw_recent_submissions

### Folder Permissions
- [ ] Set `/uploads/` folder permissions to 755
- [ ] Set `/uploads/macro/`, `/uploads/wide/`, `/uploads/proof/`, `/uploads/exif/` to 755
- [ ] Ensure web server can write to uploads folder

---

## 🧪 Functional Testing

### 1. Form Display & UI
- [ ] Access `http://localhost/form_DXI/` - form displays correctly
- [ ] All sections visible: A (Data Diri), B (Kategori), C (Upload), D (Pernyataan)
- [ ] Form is responsive on mobile (375px), tablet (768px), desktop (1024px+)
- [ ] Banner header displays with gradient background
- [ ] Footer displays correctly

### 2. Input Validation
#### Basic Validation
- [ ] Nama Lengkap field: required, auto-trim whitespace
- [ ] No. HP field:
  - [ ] Rejects invalid format
  - [ ] Auto-formats +62 format
  - [ ] Auto-formats 08 format
  - [ ] Shows validation error on blur
- [ ] Instagram field:
  - [ ] Auto-adds @ symbol if missing
  - [ ] Accepts valid usernames
- [ ] Alamat field: required, textarea works
- [ ] Judul Karya field: required

#### File Validation
- [ ] Photo file upload (photoFile):
  - [ ] Max 3 files allowed
  - [ ] Accepts JPG, PNG, TIFF
  - [ ] Rejects non-image formats
  - [ ] Shows size (max 20MB per file) in UI
  - [ ] Drag & drop works
  - [ ] Click to browse works
- [ ] Proof file (proofFile):
  - [ ] Required, single file
  - [ ] Accepts image formats
  - [ ] Max 5MB file size
- [ ] EXIF file (exifFile):
  - [ ] Required, single file
  - [ ] Accepts TXT, PDF, XLSX, PNG
  - [ ] Max 5MB file size

#### Category Selection
- [ ] Macro Angle card: clickable, shows selected state
- [ ] Wide Angle card: clickable, shows selected state
- [ ] Only one category can be selected at a time
- [ ] Category is required (show error if not selected)

#### Agreement Checkbox
- [ ] Checkbox required to submit
- [ ] Shows 5 agreement points clearly
- [ ] Error message if unchecked during submission

### 3. Form Submission
#### Successful Submission
- [ ] Fill all required fields correctly
- [ ] Select category (Macro OR Wide)
- [ ] Upload files correctly  
- [ ] Check agreement box
- [ ] Click "Daftarkan Karya" button
- [ ] Loader/spinner shows during submission
- [ ] Success message appears: "Pendaftaran berhasil..."
- [ ] Submission ID displayed in response
- [ ] Form resets after 1-2 seconds

#### Database Storage
- [ ] Verify submission ID in response
- [ ] Query database: `SELECT * FROM submissions WHERE uuid='...'`
- [ ] All fields saved correctly:
  - [ ] full_name, phone_number, instagram, address
  - [ ] category (lowercase: 'macro' or 'wide')
  - [ ] category_label, photo_title, photo_count
  - [ ] photo_files (JSON array)
  - [ ] Camera, lens, shutter, aperture, ISO (optional, some may be null)
  - [ ] ip_address, user_agent logged
  - [ ] submitted_at timestamp

#### File Storage
- [ ] Photo files stored in `/uploads/macro/` or `/uploads/wide/` (based on category)
- [ ] Filenames follow format: `TIMESTAMP_RANDOMSTR.EXT`
- [ ] Proof files stored in `/uploads/proof/`
- [ ] EXIF files stored in `/uploads/exif/`
- [ ] All files readable and not corrupted

### 4. Error Handling
#### Validation Errors
- [ ] Submit with empty name → error message shown
- [ ] Submit without selecting category → error message
- [ ] Submit without checking agreement → error message
- [ ] Submit with invalid phone format → error message
- [ ] Submit with file > 20MB → error message (photo)
- [ ] Submit with wrong file type → error message
- [ ] Error messages clear when field is corrected

#### Server Errors
- [ ] Database offline → error message (not crash)
- [ ] File upload fails → specific error message
- [ ] Disk full → graceful error handling
- [ ] Invalid JSON response → handled gracefully

### 5. Draft Auto-Save
- [ ] Fill some form fields
- [ ] Wait 30 seconds
- [ ] Data should be saved to localStorage
- [ ] Refresh page
- [ ] Previously filled data should be restored
- [ ] File inputs not restored (security)

---

## 🔧 Admin Panel Testing

### Admin Dashboard Display
- [ ] Access `http://localhost/form_DXI/admin/admin.html`
- [ ] Page loads (may show 0s if no data yet)
- [ ] Statistics section visible: Total Submissions, Macro, Wide, Total Photos

### Get Statistics API
- [ ] Test endpoint: `GET /admin/admin-api.php?action=get_stats`
- [ ] Returns JSON with:
  ```json
  {
    "success": true,
    "total_submissions": 0,
    "macro_count": 0,
    "wide_count": 0,
    "total_photos": 0
  }
  ```
- [ ] After submitting form, numbers update

### Export CSV
- [ ] Click "Download CSV" button in admin
- [ ] CSV file downloads successfully
- [ ] Filename: `DXI_submissions_YYYY-MM-DD_HH-MM-SS.csv`
- [ ] CSV readable in Excel
- [ ] All columns present: No, ID, Nama, Telepon, Instagram, dst
- [ ] Data rows match database

### Export ZIP
- [ ] Click "Download ZIP" button in admin
- [ ] ZIP file downloads: `DXI_Submissions_Export_YYYY-MM-DD_HH-MM-SS.zip`
- [ ] ZIP contains:
  - [ ] `data_submissions.csv` with all submission data
  - [ ] `submissions/` folder with organized structure
    - [ ] `submissions/<NamaLengkap>/<category>/<id>_<title>/`
    - [ ] Photo files inside each submission folder
    - [ ] bukti_*.jpg files
    - [ ] exif_* files
- [ ] ZIP extractable without errors
- [ ] All files present and readable

### Delete Functions
- [ ] Test endpoint: `POST /admin/admin-api.php?action=delete_submission` with `id=1`
- [ ] Submission deleted from database
- [ ] Files deleted from disk
- [ ] Returns success message
- [ ] Test endpoint: `POST /admin/admin-api.php?action=delete_all`
- [ ] All submissions and files deleted
- [ ] Database and disk cleaned

---

## 🔐 Security Testing

### Input Sanitization
- [ ] SQL Injection test: submit `'; DROP TABLE submissions --` in field
- [ ] Should be escaped/sanitized, NOT executed
- [ ] XSS test: submit `<script>alert('xss')</script>` in field
- [ ] Should display as text, NOT execute

### File Security
- [ ] Try uploading `.php` file → rejected
- [ ] Try uploading `.exe` file → rejected
- [ ] Try uploading 100MB file → rejected with size error
- [ ] Verify uploaded files not in web root or have 755 permissions only

### CORS & Headers
- [ ] Verify `.htaccess` exists in `/uploads/` → no PHP execution
- [ ] Verify X-Content-Type-Options header set
- [ ] Verify X-Frame-Options header set

---

## 📱 Cross-Browser Testing

### Desktop Browsers
- [ ] Chrome: Form display, submission, validation
- [ ] Firefox: Form display, file upload, drag & drop
- [ ] Edge: Form display, submission
- [ ] Safari: Form display, touch events

### Mobile Browsers
- [ ] Chrome Mobile: Form submissible on small screen
- [ ] Safari iOS: Touch-friendly inputs
- [ ] Responsive at 375px, 768px, 1024px widths

---

## 🔍 Performance Testing

### Load Testing
- [ ] Submit 10 forms in sequence
- [ ] Each submission processes within 5 seconds
- [ ] No timeout errors
- [ ] Database remains stable

### File Size Testing
- [ ] Max file size (20MB) upload completes
- [ ] Multiple file uploads (3 photos) process correctly
- [ ] No timeout on ZIP export with 50+ submissions

---

## 📋 Documentation Review

### README.md
- [ ] Documentation covers all features
- [ ] Setup instructions clear
- [ ] Usage examples provided

### Database Files
- [ ] `DXI_db.sql` present and imports successfully
- [ ] Schema documentation in file

### Code Comments
- [ ] `process_form.php` has comments for major sections
- [ ] `script.js` has comments for major functions
- [ ] Admin functions documented

---

## ✨ Final Checks

### Before Production
- [ ] All tests passed
- [ ] No console errors in browser DevTools
- [ ] Database backup created
- [ ] `.env` or sensitive config files not in repo
- [ ] `.htaccess` configured for security
- [ ] File permissions set correctly (755 for dirs, 644 for files)

### Deployment
- [ ] Copy project to production server
- [ ] Database created and schema imported
- [ ] File permissions set: `chmod 755 uploads/`
- [ ] Update database credentials in `config/database.php`
- [ ] Test form submission on production
- [ ] Monitor error logs

---

## 🚀 Post-Deployment

### Monitoring
- [ ] Check error logs weekly
- [ ] Monitor disk space in uploads folder
- [ ] Verify backup strategy in place
- [ ] Monitor database size growth

### Maintenance
- [ ] Archive old submissions periodically
- [ ] Clean up test files
- [ ] Update security patches
- [ ] Review admin logs for suspicious activity

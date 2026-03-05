# ✅ HOSTINGER DEPLOYMENT CHECKLIST

**Domain:** https://underwatershootout.deepextremeindonesia.com/

---

## 🚀 QUICK DEPLOYMENT STEPS

### Phase 1: Upload Files
- [ ] Upload semua file ke Hostinger FTP
- [ ] Folder structure: `/public_html/form_DXI/`
- [ ] Verify: index.html, process_form.php, admin/ folder exist

### Phase 2: Database Setup
- [ ] Create database di Hostinger MySQL
- [ ] Import `DXI_db.sql` schema
- [ ] Verify tables: submissions, photo_files, admin_users, admin_actions
- [ ] Update `config/database.php` dengan credentials Hostinger

### Phase 3: Verify Form
- [ ] Open: https://underwatershootout.deepextremeindonesia.com/
- [ ] Form displays correctly
- [ ] Submit test entry
- [ ] Check `uploads/submissions.json` updated
ko;k

### Phase 4: Setup Admin Access ⭐
- [ ] Open: https://underwatershootout.deepextremeindonesia.com/admin/admin.html
- [ ] Enter password: `admin123`
- [ ] ✅ Dashboard loads successfully

### Phase 5: Change Password 🔐 (CRITICAL)
- [ ] Open `admin/admin.html` via FTP
- [ ] Find: `const ADMIN_PASSWORD = 'admin123';`
- [ ] Change to: `const ADMIN_PASSWORD = 'YOUR_NEW_PASSWORD';`
- [ ] Save and upload
- [ ] Test login with new password

### Phase 6: Test Admin Features
- [ ] [ ] View stats (total submissions)
- [ ] [ ] Export CSV - file downloads
- [ ] [ ] Export ZIP - file downloads with photos
- [ ] [ ] Delete single submission - works with password
- [ ] [ ] Check that data persists correctly

### Phase 7: Security Hardening
- [ ] Verify `.htaccess` files exist in 3 locations:
  - [ ] `/admin/.htaccess`
  - [ ] `/uploads/.htaccess`
  - [ ] `/.htaccess` (root)
- [ ] Check HTTPS working (should be default)
- [ ] Verify database connection secure (no exposed credentials)

### Phase 8: Go Live
- [ ] All tests passed ✅
- [ ] Password changed ✅
- [ ] Security checklist done ✅
- [ ] **READY FOR USERS!** 🎉

---

## 🔑 ADMIN LOGIN CREDENTIALS

**URL:** https://underwatershootout.deepextremeindonesia.com/admin/admin.html

| Field | Value | Notes |
|-------|-------|-------|
| Password | `admin123` | Change immediately! (See Phase 5) |
| Default access | Full dashboard | All features available |

---

## 📋 ADMIN FEATURES AVAILABLE

After successful login:

| Feature | Icon | Function |
|---------|------|----------|
| **Dashboard Stats** | 📊 | View total submissions & categories |
| **Export CSV** | 📄 | Download data in Excel format |
| **Export ZIP** | 📦 | Download data + all photos |
| **Delete Entry** | 🗑️ | Remove single submission |
| **Delete All** | ⚠️ | Remove all submissions (with password) |

---

## 🔗 IMPORTANT URLS

| Page | URL |
|------|-----|
| Form | https://underwatershootout.deepextremeindonesia.com/ |
| Admin | https://underwatershootout.deepextremeindonesia.com/admin/admin.html |
| API | https://underwatershootout.deepextremeindonesia.com/admin-api.php |

---

## 📂 FILE LOCATIONS

```
/public_html/form_DXI/ (Root)
├── index.html (Form UI)
├── process_form.php (Form processor)
├── config/
│   └── database.php (DB credentials)
├── admin/
│   ├── admin.html (Dashboard - contains password)
│   ├── export_csv.php (CSV export)
│   ├── export_zip.php (ZIP export)
│   ├── get_stats.php (Statistics API)
│   └── delete_all.php (Delete operations)
└── uploads/
    ├── submissions.json (Form data)
    ├── makro/ (Macro photos)
    ├── wide/ (Wide photos)
    ├── proof/ (Payment proof)
    └── exif/ (EXIF data)
```

---

## 🆘 QUICK TROUBLESHOOTING

| Problem | Solution |
|---------|----------|
| "404 Not Found" | Check URL, verify files uploaded |
| "Password salah" | Use `admin123` (or your new password) |
| "No data showing" | Check `uploads/submissions.json` exists |
| "Export fails" | Verify folder permissions are correct |
| "Photos missing" | Check uploads/makro/, uploads/wide/ exist |

---

## ⏰ AFTER DEPLOYMENT TASKS

- [ ] Backup database daily via Hostinger control panel
- [ ] Export data weekly (CSV + ZIP)
- [ ] Monitor form submissions daily
- [ ] Check admin dashboard weekly
- [ ] Keep `.htaccess` files for security
- [ ] Never share admin password via email/chat
- [ ] Update password every 3 months

---

## 📞 SUPPORT RESOURCES

**For Hostinger Issues:**
- Hostinger Control Panel: https://hpanel.hostinger.com/
- Support: support@hostinger.com

**For Database Issues:**
- PHPMyAdmin: https://phpmyadmin.hostinger.com/
- Database name: `DXI_db`

**For Code Issues:**
- See: `HOSTINGER_ADMIN_ACCESS.md` (detailed guide)
- See: `GET_STARTED.md` (setup guide)
- See: `DATABASE_SETUP.md` (DB configuration)

---

**Status:** ✅ Ready for Production  
**Last Updated:** March 4, 2026  
**Domain:** https://underwatershootout.deepextremeindonesia.com/

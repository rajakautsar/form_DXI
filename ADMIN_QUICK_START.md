# 🚀 HOSTINGER QUICK START - Setelah Deploy

**Domain:** https://underwatershootout.deepextremeindonesia.com/

---

## ⚡ 5 MENIT - SETUP AWAL

### 1️⃣ Verify Form Berfungsi ✅
```
https://underwatershootout.deepextremeindonesia.com/
```
Pastikan form muncul dan bisa submit

### 2️⃣ Buka Admin Dashboard 🔐
```
https://underwatershootout.deepextremeindonesia.com/admin/admin.html
```
Password: `admin123`

### 3️⃣ Lihat Dashboard 📊
Verifikasi stats muncul dan form submissions terlihat

### 4️⃣ Test Export 💾
Click "Export CSV" atau "Export ZIP" untuk test

### 5️⃣ UBAH PASSWORD ⚠️ **CRITICAL!**
Edit file `admin/admin.html` via FTP:
```javascript
const ADMIN_PASSWORD = 'admin123';  // Ubah ini!
```

Ganti dengan password kuat Anda sendiri.

---

## 📍 LOKASI PENTING

| Nama | URL |
|------|-----|
| **Form** | https://underwatershootout.deepextremeindonesia.com/ |
| **Admin** | https://underwatershootout.deepextremeindonesia.com/admin/admin.html |
| **Database** | /public_html/form_DXI/config/database.php |

---

## 🔑 DEFAULT LOGIN

- **URL:** `/admin/admin.html`
- **Password:** `admin123` ← **CHANGE THIS!**
- **Features:** Stats, Export CSV/ZIP, Delete data

---

## ⚠️ SECURITY CRITICAL STEPS

```
🔴 CRITICAL: Change password in admin.html
🔴 CRITICAL: Check .htaccess files exist
🔴 CRITICAL: Verify HTTPS working
```

---

## 📱 MINIMAL STEPS AFTER DEPLOY

1. **Open form** → https://underwatershootout.deepextremeindonesia.com/
2. **Open admin** → /admin/admin.html with password `admin123`
3. **Change password** in admin/admin.html
4. **Test export** to verify photos/data working
5. **DONE** ✅

---

## 🆘 PROBLEM?

| If... | Then... |
|------|---------|
| Form won't load | Check database connection in config/database.php |
| Admin password wrong | Default is `admin123` |
| Export doesn't work | Check uploads/ folder has write permission |
| Photos missing | Verify files in uploads/makro/ and uploads/wide/ |

---

## 📞 NEXT STEPS

For more detailed instructions:
- See: **`HOSTINGER_ADMIN_ACCESS.md`** (Full guide)
- See: **`HOSTINGER_DEPLOYMENT_CHECKLIST.md`** (Complete checklist)
- See: **`GET_STARTED.md`** (Setup guide)

---

**That's it!** You're ready to accept submissions! 🎉

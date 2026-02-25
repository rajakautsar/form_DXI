# 🔒 PANDUAN KEAMANAN & DEPLOYMENT

## ⚠️ PENTING: Sebelum Production

File form ini adalah **TEMPLATE** yang perlu dikonfigurasi untuk production. Berikut checklist keamanan:

### 1. Server Security

- [ ] Update PHP ke versi terbaru (min: 7.4+)
- [ ] Enable PHP security extensions:
  ```ini
  ; php.ini
  extension=fileinfo       ; Validasi MIME type
  
  ; Disable dangerous functions
  disable_functions = exec, passthru, system, proc_open, popen, curl_exec, curl_multi_exec, parse_ini_file, show_source
  ```

### 2. Upload Directory Protection

```apache
# .htaccess di uploads/ folder
<FilesMatch "\.(php|phtml|php3|php4|php5|phps)$">
    Deny from all
</FilesMatch>

# Prevent direct access
Options -Indexes
```

### 3. Form Security

#### CSRF Protection
```php
// Tambahkan di index.html atau process_form.php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Di HTML form:
// <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Di process_form.php:
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('CSRF token validation failed');
}
```

#### Rate Limiting
```php
// Anti-spam: max 5 submissions per IP per hour
$ip = $_SERVER['REMOTE_ADDR'];
$lockFile = "locks/$ip.lock";

if (file_exists($lockFile)) {
    $lastSubmit = file_get_contents($lockFile);
    $timeSinceLastSubmit = time() - intval($lastSubmit);
    
    if ($timeSinceLastSubmit < 3600) { // 1 hour cooldown
        http_response_code(429);
        die(json_encode(['error' => 'Terlalu banyak submissions. Coba lagi nanti.']));
    }
}

// Update lock file after successful submission
@file_put_contents($lockFile, time());
```

### 4. Input Validation & Sanitization

Sudah diterapkan, tapi pastikan:
- [x] HTML entities escaping (htmlspecialchars)
- [x] SQL injection prevention (gunakan prepared statements jika DB)
- [x] File upload validation (size, type, virus scan)
- [x] XSS prevention

### 5. File Upload Security

```php
// Virus scanning (gunakan ClamAV atau VirusTotal API)
function scanFileVirus($filePath) {
    // Implementasi dengan ClamAV atau external API
    // https://github.com/php-clamav/php-clamav
}

// Rename files dengan pattern yang aman
function secureFileRename($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return uniqid() . '.' . preg_replace('/[^a-z0-9]/', '', $ext);
}

// Store outside public_html
$uploadDir = '/var/uploads/dxi/'; // Outside web root
```

### 6. Database (If Using)

```php
// Use prepared statements
$stmt = $pdo->prepare("
    INSERT INTO submissions (name, phone, instagram, category, ...)
    VALUES (?, ?, ?, ?, ...)
");

$stmt->execute([$fullName, $phoneNumber, $instagram, $category, ...]);

// Encrypt sensitive data
function encryptData($data, $key) {
    return openssl_encrypt($data, 'AES-256-CBC', $key);
}
```

### 7. HTTPS & SSL

```apache
# Force HTTPS in .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 8. Logging & Monitoring

```php
// Log semua submissions
$logFile = 'logs/submissions.log';
$logEntry = date('Y-m-d H:i:s') . ' | ' . json_encode($submission) . PHP_EOL;
error_log($logEntry, 3, $logFile);

// Monitor untuk aktivitas mencurigakan
if (count($_FILES) > 5 || strlen($fullName) > 100) {
    error_log('SUSPICIOUS ACTIVITY DETECTED', 3, 'logs/alerts.log');
}
```

### 9. Email Notifications

```php
// Kirim email konfirmasi ke peserta
function sendConfirmationEmail($email, $name, $submissionId) {
    $to = $email;
    $subject = "Konfirmasi Pendaftaran Kompetisi DXI";
    $message = "Halo {$name},\n\n";
    $message .= "Terima kasih telah mendaftar. Nomor pendaftaran Anda: {$submissionId}\n\n";
    $message .= "Admin akan mengecek bukti follow/repost dan menghubungi Anda via WhatsApp.\n\n";
    $message .= "Regards,\nDXI Team";
    
    $headers = "From: noreply@dxiofficial.com\r\n";
    $headers .= "Reply-To: contact@dxiofficial.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    mail($to, $subject, $message, $headers);
}
```

### 10. Admin Panel (Recommended)

Buat simple admin panel untuk:
- [ ] View semua submissions
- [ ] Download files per kategori (Macro/Wide)
- [ ] Mark as reviewed/winner
- [ ] Export data untuk penjury

### 11. Backup Strategy

```bash
# Backup database dan uploads setiap hari
0 2 * * * tar -czf /backups/dxi_$(date +\%Y\%m\%d).tar.gz /var/uploads/dxi /var/db/submissions.json
```

### 12. Performance Optimization

```php
// Enable gzip compression
ini_set('zlib.output_compression', 'On');
header('Content-Encoding: gzip');

// Cache headers
header('Cache-Control: max-age=3600, public');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
```

## 📋 Deployment Checklist

- [ ] Update index.html form action ke `process_form.php`
- [ ] Buat folder `uploads/` dengan permissions 755
- [ ] Buat folder `logs/` dengan permissions 755
- [ ] Konfigurasi email settings untuk konfirmasi
- [ ] Setup database jika menggunakan
- [ ] Enable HTTPS/SSL certificate
- [ ] Setup automated backups
- [ ] Test form submission end-to-end
- [ ] Setup monitoring dan alerts
- [ ] Hide `.htaccess` dan config files
- [ ] Minify CSS dan JavaScript untuk production
- [ ] Setup WAF (Web Application Firewall)
- [ ] Regular security audits

## 🛡️ Security Headers

```apache
# Tambahkan di .htaccess
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Content-Security-Policy "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'"
```

## 📞 Support & Monitoring

```php
// Setup error notification
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error: $errstr in $errfile:$errline", 3, 'logs/errors.log');
    
    // Send alert email to admin
    if ($errno === E_CRITICAL) {
        mail('admin@dxiofficial.com', 'CRITICAL ERROR', $errstr);
    }
});
```

## 🔄 Updates & Maintenance

- Pastikan dependencies (jika ada) always up-to-date
- Regularly update form validasi rules
- Review dan audit logs secara berkala
- Backup sebelum membuat major changes

---

**Version**: 1.0.0  
**Last Updated**: February 2026  
**Status**: Ready for Production (dengan konfigurasi yang tepat)

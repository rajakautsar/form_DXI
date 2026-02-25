<?php
/**
 * Process Competition Form Submission
 * 
 * File: process_form.php
 * Location: /form_DXI/process_form.php
 * 
 * PENTING: File ini adalah TEMPLATE CONTOH
 * Sesuaikan dengan kebutuhan dan sistem keamanan Anda sebelum production
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set response header
header('Content-Type: application/json');

// CORS headers (adjust as needed)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

// ============================================
// CONFIGURATION
// ============================================

// Buat folder uploads jika belum ada
$uploadDir = __DIR__ . '/uploads/';
$macroDir = $uploadDir . 'macro/';
$wideDir = $uploadDir . 'wide/';
$proofDir = $uploadDir . 'proof/';

foreach ([$uploadDir, $macroDir, $wideDir, $proofDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Max file size (20MB)
$maxFileSize = 20 * 1024 * 1024;

// Allowed MIME types
$allowedMimes = [
    'image/jpeg',
    'image/png',
    'image/tiff'
];

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return (strlen($phone) >= 10 && strlen($phone) <= 15);
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $timestamp = time();
    $randomStr = substr(md5(rand()), 0, 8);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return $timestamp . '_' . $randomStr . '.' . $ext;
}

/**
 * Validate file upload
 */
function validateFile($fileArray, $maxSize, $allowedMimes) {
    if (!isset($fileArray['tmp_name']) || empty($fileArray['tmp_name'])) {
        return ['valid' => false, 'message' => 'File tidak ditemukan'];
    }

    // Check file size
    if ($fileArray['size'] > $maxSize) {
        return ['valid' => false, 'message' => 'Ukuran file terlalu besar'];
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileArray['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        return ['valid' => false, 'message' => 'Format file tidak didukung'];
    }

    return ['valid' => true, 'mimeType' => $mimeType];
}

// ============================================
// GET FORM DATA
// ============================================

$data = [
    'fullName' => sanitizeInput($_POST['fullName'] ?? ''),
    'phoneNumber' => sanitizeInput($_POST['phoneNumber'] ?? ''),
    'instagram' => sanitizeInput($_POST['instagram'] ?? ''),
    'address' => sanitizeInput($_POST['address'] ?? ''),
    'category' => sanitizeInput($_POST['category'] ?? ''),
    'photoTitle' => sanitizeInput($_POST['photoTitle'] ?? ''),
    'camera' => sanitizeInput($_POST['camera'] ?? ''),
    'lens' => sanitizeInput($_POST['lens'] ?? ''),
    'shutter' => sanitizeInput($_POST['shutter'] ?? ''),
    'aperture' => sanitizeInput($_POST['aperture'] ?? ''),
    'iso' => sanitizeInput($_POST['iso'] ?? ''),
    'location' => sanitizeInput($_POST['location'] ?? ''),
    'agreement' => isset($_POST['agreement']) ? true : false
];

// ============================================
// VALIDATION
// ============================================

$errors = [];

// Validate required fields
if (empty($data['fullName'])) {
    $errors[] = 'Nama lengkap harus diisi';
}

if (empty($data['phoneNumber'])) {
    $errors[] = 'No. HP harus diisi';
} elseif (!validatePhone($data['phoneNumber'])) {
    $errors[] = 'Format no. HP tidak valid';
}

if (empty($data['instagram'])) {
    $errors[] = 'Username Instagram harus diisi';
}

if (empty($data['address'])) {
    $errors[] = 'Alamat harus diisi';
}

if (empty($data['category']) || !in_array($data['category'], ['macro', 'wide'])) {
    $errors[] = 'Pilih kategori lomba dengan benar';
}

if (empty($data['photoTitle'])) {
    $errors[] = 'Judul foto harus diisi';
}

if (!$data['agreement']) {
    $errors[] = 'Anda harus menyetujui pernyataan peserta';
}

// Validate files
if (!isset($_FILES['photoFile']) || empty($_FILES['photoFile']['name'][0])) {
    $errors[] = 'File karya harus diupload (minimal 1)';
} else {
    // Validate multiple photo files
    $photoCount = count($_FILES['photoFile']['name']);
    if ($photoCount > 3) {
        $errors[] = 'Maksimal hanya 3 file karya yang diizinkan';
    } else {
        for ($i = 0; $i < $photoCount; $i++) {
            $photoValidation = validateFile([
                'name' => $_FILES['photoFile']['name'][$i],
                'tmp_name' => $_FILES['photoFile']['tmp_name'][$i],
                'size' => $_FILES['photoFile']['size'][$i],
                'error' => $_FILES['photoFile']['error'][$i]
            ], $maxFileSize, $allowedMimes);
            
            if (!$photoValidation['valid']) {
                $errors[] = 'File karya ' . ($i + 1) . ': ' . $photoValidation['message'];
            }
        }
    }
}

if (!isset($_FILES['proofFile']) || $_FILES['proofFile']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'File bukti harus diupload';
} else {
    $proofValidation = validateFile($_FILES['proofFile'], $maxFileSize, $allowedMimes);
    if (!$proofValidation['valid']) {
        $errors[] = 'File bukti: ' . $proofValidation['message'];
    }
}

if (!isset($_FILES['exifFile']) || $_FILES['exifFile']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'File Exif Data harus diupload';
} else {
    $exifValidation = validateFile($_FILES['exifFile'], 5 * 1024 * 1024, ['text/plain', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/png']);
    if (!$exifValidation['valid']) {
        $errors[] = 'File Exif Data: ' . $exifValidation['message'];
    }
}

// If validation fails, return errors
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validasi gagal',
        'errors' => $errors
    ]);
    exit;
}

// ============================================
// PROCESS FILE UPLOADS
// ============================================

try {
    // Upload photo files (multiple)
    $photoFiles = [];
    $photoCount = count($_FILES['photoFile']['name']);
    
    for ($i = 0; $i < $photoCount; $i++) {
        $photoFile = [
            'name' => $_FILES['photoFile']['name'][$i],
            'tmp_name' => $_FILES['photoFile']['tmp_name'][$i],
            'size' => $_FILES['photoFile']['size'][$i]
        ];
        
        $newPhotoName = generateUniqueFilename($photoFile['name']);
        
        // Determine destination based on category
        $photoDestDir = ($data['category'] === 'macro') ? $macroDir : $wideDir;
        $photoPath = $photoDestDir . $newPhotoName;
        
        if (!move_uploaded_file($photoFile['tmp_name'], $photoPath)) {
            throw new Exception('Gagal menyimpan file karya ' . ($i + 1));
        }
        
        $photoFiles[] = $newPhotoName;
    }

    // Upload proof file
    $proofFile = $_FILES['proofFile'];
    $newProofName = generateUniqueFilename($proofFile['name']);
    $proofPath = $proofDir . $newProofName;
    
    if (!move_uploaded_file($proofFile['tmp_name'], $proofPath)) {
        throw new Exception('Gagal menyimpan file bukti');
    }

    // Upload exif file
    $exifFile = $_FILES['exifFile'];
    $newExifName = generateUniqueFilename($exifFile['name']);
    $exifPath = $uploadDir . 'exif/' . $newExifName;
    
    // Create exif directory if not exists
    if (!is_dir($uploadDir . 'exif/')) {
        mkdir($uploadDir . 'exif/', 0755, true);
    }
    
    if (!move_uploaded_file($exifFile['tmp_name'], $exifPath)) {
        throw new Exception('Gagal menyimpan file Exif Data');
    }

    // ============================================
    // SAVE SUBMISSION DATA
    // ============================================

    // Create submission record
    $submission = [
        'id' => uniqid('DXI_', true),
        'timestamp' => date('Y-m-d H:i:s'),
        'fullName' => $data['fullName'],
        'phoneNumber' => $data['phoneNumber'],
        'instagram' => $data['instagram'],
        'address' => $data['address'],
        'category' => $data['category'],
        'photoTitle' => $data['photoTitle'],
        'photoFiles' => $photoFiles,
        'photoCount' => count($photoFiles),
        'proofFile' => $newProofName,
        'exifFile' => $newExifName,
        'agreement' => $data['agreement']
    ];

    // Save to JSON file (gunakan database untuk production)
    $submissionsFile = $uploadDir . 'submissions.json';
    $submissions = [];
    
    if (file_exists($submissionsFile)) {
        $submissions = json_decode(file_get_contents($submissionsFile), true);
    }
    
    $submissions[] = $submission;
    
    if (!file_put_contents($submissionsFile, json_encode($submissions, JSON_PRETTY_PRINT))) {
        throw new Exception('Gagal menyimpan data pendaftaran');
    }

    // ============================================
    // SEND CONFIRMATION EMAIL (OPTIONAL)
    // ============================================

    // $to = $data['email']; // jika ada email field
    // $subject = "Konfirmasi Pendaftaran - Kompetisi Fotografi DXI";
    // $message = "Terima kasih telah mendaftar, {$data['fullName']}!";
    // mail($to, $subject, $message);

    // ============================================
    // SUCCESS RESPONSE
    // ============================================

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Pendaftaran berhasil! Terima kasih telah mengikuti kompetisi kami.',
        'submissionId' => $submission['id'],
        'category' => $data['category'],
        'photoTitle' => $data['photoTitle'],
        'photoCount' => $submission['photoCount']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>

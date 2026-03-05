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

// ============================================
// DATABASE CONNECTION
// ============================================
require_once __DIR__ . '/config/database.php';

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
    'category' => strtolower(trim($_POST['category'] ?? '')), // EXPLICIT: lowercase trim NO HTML SPECIAL CHARS
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

// CRITICAL: Validate category - must be exactly 'macro' or 'wide'
if (empty($data['category'])) {
    $errors[] = 'Pilih kategori lomba (Macro Angle atau Wide Angle)';
} elseif (!in_array($data['category'], ['macro', 'wide'], true)) {
    // Strict comparison to prevent type juggling
    $errors[] = 'Kategori tidak valid. Harus: macro atau wide. Diterima: ' . htmlspecialchars($data['category']);
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

if (!isset($_FILES['proofFile']) || empty($_FILES['proofFile']['name'][0])) {
    $errors[] = 'File bukti follow & repost harus diupload (minimal 1)';
} else {
    // Validate multiple proof files (JPG and PNG, max 2)
    $proofCount = count($_FILES['proofFile']['name']);
    if ($proofCount > 2) {
        $errors[] = 'Maksimal hanya 2 file bukti yang diizinkan';
    } else {
        $proofMimes = ['image/jpeg', 'image/png']; // JPG and PNG
        for ($i = 0; $i < $proofCount; $i++) {
            $proofValidation = validateFile([
                'name' => $_FILES['proofFile']['name'][$i],
                'tmp_name' => $_FILES['proofFile']['tmp_name'][$i],
                'size' => $_FILES['proofFile']['size'][$i],
                'error' => $_FILES['proofFile']['error'][$i]
            ], 5 * 1024 * 1024, $proofMimes);
            
            if (!$proofValidation['valid']) {
                $errors[] = 'File bukti ' . ($i + 1) . ': ' . $proofValidation['message'];
            }
        }
    }
}

if (!isset($_FILES['exifFile']) || empty($_FILES['exifFile']['name'][0])) {
    $errors[] = 'File Exif Data harus diupload (minimal 1)';
} else {
    // Validate multiple exif files (max 2)
    $exifCount = count($_FILES['exifFile']['name']);
    if ($exifCount > 2) {
        $errors[] = 'Maksimal hanya 2 file Exif Data yang diizinkan';
    } else {
        $exifMimes = ['text/plain', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/png', 'image/jpeg'];
        for ($i = 0; $i < $exifCount; $i++) {
            $exifValidation = validateFile([
                'name' => $_FILES['exifFile']['name'][$i],
                'tmp_name' => $_FILES['exifFile']['tmp_name'][$i],
                'size' => $_FILES['exifFile']['size'][$i],
                'error' => $_FILES['exifFile']['error'][$i]
            ], 5 * 1024 * 1024, $exifMimes);
            
            if (!$exifValidation['valid']) {
                $errors[] = 'File Exif Data ' . ($i + 1) . ': ' . $exifValidation['message'];
            }
        }
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
    
    // Determine target directory based on CATEGORY (macro atau wide)
    // This is the CRITICAL logic for file organization
    if ($data['category'] === 'macro') {
        $photoDestDir = $macroDir;
        $categoryLabel = 'Macro Angle';
    } elseif ($data['category'] === 'wide') {
        $photoDestDir = $wideDir;
        $categoryLabel = 'Wide Angle';
    } else {
        throw new Exception('Kategori tidak valid: ' . htmlspecialchars($data['category']));
    }
    
    // Upload each photo file to the appropriate category folder
    for ($i = 0; $i < $photoCount; $i++) {
        $photoFile = [
            'name' => $_FILES['photoFile']['name'][$i],
            'tmp_name' => $_FILES['photoFile']['tmp_name'][$i],
            'size' => $_FILES['photoFile']['size'][$i]
        ];
        
        $newPhotoName = generateUniqueFilename($photoFile['name']);
        $photoPath = $photoDestDir . $newPhotoName;
        
        if (!move_uploaded_file($photoFile['tmp_name'], $photoPath)) {
            throw new Exception('Gagal menyimpan file karya ' . ($i + 1) . ' ke folder ' . htmlspecialchars($categoryLabel));
        }
        
        $photoFiles[] = $newPhotoName;
    }

    // Upload proof files (multiple, max 2)
    $proofFiles = [];
    $proofCount = count($_FILES['proofFile']['name']);
    
    for ($i = 0; $i < $proofCount; $i++) {
        $proofFile = [
            'name' => $_FILES['proofFile']['name'][$i],
            'tmp_name' => $_FILES['proofFile']['tmp_name'][$i],
            'size' => $_FILES['proofFile']['size'][$i]
        ];
        
        $newProofName = generateUniqueFilename($proofFile['name']);
        $proofPath = $proofDir . $newProofName;
        
        if (!move_uploaded_file($proofFile['tmp_name'], $proofPath)) {
            throw new Exception('Gagal menyimpan file bukti ' . ($i + 1));
        }
        
        $proofFiles[] = $newProofName;
    }

    // Upload exif files (multiple, max 2)
    $exifFiles = [];
    $exifCount = count($_FILES['exifFile']['name']);
    
    // Create exif directory if not exists
    if (!is_dir($uploadDir . 'exif/')) {
        mkdir($uploadDir . 'exif/', 0755, true);
    }
    
    for ($i = 0; $i < $exifCount; $i++) {
        $exifFile = [
            'name' => $_FILES['exifFile']['name'][$i],
            'tmp_name' => $_FILES['exifFile']['tmp_name'][$i],
            'size' => $_FILES['exifFile']['size'][$i]
        ];
        
        $newExifName = generateUniqueFilename($exifFile['name']);
        $exifPath = $uploadDir . 'exif/' . $newExifName;
        
        if (!move_uploaded_file($exifFile['tmp_name'], $exifPath)) {
            throw new Exception('Gagal menyimpan file Exif Data ' . ($i + 1));
        }
        
        $exifFiles[] = $newExifName;
    }

    // ============================================
    // SAVE SUBMISSION DATA TO MYSQL DATABASE
    // ============================================

    // Generate unique UUID
    $submission_uuid = 'DXI_' . uniqid() . '_' . time();

    // Prepare data for database insert
    $stmt = $mysqli->prepare(
        "INSERT INTO submissions (
            uuid, full_name, phone_number, instagram, address, 
            category, category_label, photo_title, photo_count, 
            photo_files, proof_file, exif_file, 
            camera, lens, shutter, aperture, iso, location,
            agreement, ip_address, user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $mysqli->error);
    }

    // Bind parameters
    $photo_files_json = json_encode($photoFiles);
    $proof_files_json = json_encode($proofFiles);
    $exif_files_json = json_encode($exifFiles);
    $agreement_int = $data['agreement'] ? 1 : 0;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $stmt->bind_param(
        'ssssssssissssssssisss',
        $submission_uuid,
        $data['fullName'],
        $data['phoneNumber'],
        $data['instagram'],
        $data['address'],
        $data['category'],
        $categoryLabel,
        $data['photoTitle'],
        $photoCount,
        $photo_files_json,
        $proof_files_json,
        $exif_files_json,
        $data['camera'],
        $data['lens'],
        $data['shutter'],
        $data['aperture'],
        $data['iso'],
        $data['location'],
        $agreement_int,
        $ip_address,
        $user_agent
    );

    // Execute insert
    if (!$stmt->execute()) {
        throw new Exception('Database insert error: ' . $stmt->error);
    }

    // Get last insert ID
    $submission_id = $stmt->insert_id;
    $stmt->close();

    // Track photo files (optional - untuk audit trail)
    foreach ($photoFiles as $photoFile) {
        $stmt = $mysqli->prepare(
            "INSERT INTO photo_files (submission_id, file_name, file_type, category) 
             VALUES (?, ?, ?, ?)"
        );
        $file_type = 'photo';
        $stmt->bind_param('isss', $submission_id, $photoFile, $file_type, $data['category']);
        $stmt->execute();
        $stmt->close();
    }

    // Track proof files
    foreach ($proofFiles as $proofFile) {
        $stmt = $mysqli->prepare(
            "INSERT INTO photo_files (submission_id, file_name, file_type) 
             VALUES (?, ?, ?)"
        );
        $file_type = 'proof';
        $stmt->bind_param('iss', $submission_id, $proofFile, $file_type);
        $stmt->execute();
        $stmt->close();
    }

    // Track exif files
    foreach ($exifFiles as $exifFile) {
        $stmt = $mysqli->prepare(
            "INSERT INTO photo_files (submission_id, file_name, file_type) 
             VALUES (?, ?, ?)"
        );
        $file_type = 'exif';
        $stmt->bind_param('iss', $submission_id, $exifFile, $file_type);
        $stmt->execute();
        $stmt->close();
    }



    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Pendaftaran berhasil! Terima kasih telah mengikuti kompetisi kami.',
        'submissionId' => $submission_uuid,
        'category' => $data['category'],
        'categoryLabel' => $categoryLabel,
        'photoTitle' => $data['photoTitle'],
        'photoCount' => $photoCount,
        'folder' => 'uploads/' . $data['category'] . '/'  // Show where files were stored
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>

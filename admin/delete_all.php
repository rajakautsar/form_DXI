<?php
/**
 * Delete All Data - Admin Function
 * 
 * DANGER ZONE: Delete all submissions from MySQL database and uploaded files
 * Requires password authentication
 * 
 * Default Password: admin123
 * Change in production!
 */

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once dirname(dirname(__FILE__)) . '/config/database.php';

// ============================================
// SECURITY: Set admin password here
// ============================================
// 🔒 CHANGE THIS IN PRODUCTION!
define('ADMIN_PASSWORD', 'admin123');

// ============================================
// VALIDATE REQUEST
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

// Get password from POST
$providedPassword = $_POST['password'] ?? '';

// Validate password
if (empty($providedPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password diperlukan']);
    exit;
}

// Check password (use hash_equals for timing attack prevention)
if (!hash_equals(ADMIN_PASSWORD, $providedPassword)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Password salah']);
    exit;
}

// ============================================
// DELETE ALL DATA
// ============================================

try {
    $baseDir = dirname(dirname(__FILE__));
    $uploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

    $deletedItems = [
        'database_records_deleted' => 0,
        'macro_folder' => false,
        'wide_folder' => false,
        'proof_folder' => false,
        'exif_folder' => false,
        'total_files_deleted' => 0
    ];

    // 1. Delete all submissions from database
    $stmt = $mysqli->prepare("DELETE FROM submissions");
    
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $mysqli->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Database delete error: ' . $stmt->error);
    }
    
    $deletedItems['database_records_deleted'] = $stmt->affected_rows;
    $stmt->close();

    // Also delete from photo_files table (should cascade but explicit is safer)
    $stmt = $mysqli->prepare("DELETE FROM photo_files");
    $stmt->execute();
    $stmt->close();

    // 2. Recursive delete function
    function deleteDirectoryContents($dir) {
        $deletedCount = 0;
        
        if (!is_dir($dir)) {
            return $deletedCount;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($path)) {
                $deletedCount += deleteDirectoryContents($path);
                if (@rmdir($path)) {
                    $deletedCount++;
                }
            } else {
                if (@unlink($path)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }

    // 3. Delete contents of category folders
    $macroDir = $uploadsDir . 'macro';
    $wideDir = $uploadsDir . 'wide';
    $proofDir = $uploadsDir . 'proof';
    $exifDir = $uploadsDir . 'exif';

    if (is_dir($macroDir)) {
        $deletedItems['total_files_deleted'] += deleteDirectoryContents($macroDir);
        $deletedItems['macro_folder'] = true;
    }

    if (is_dir($wideDir)) {
        $deletedItems['total_files_deleted'] += deleteDirectoryContents($wideDir);
        $deletedItems['wide_folder'] = true;
    }

    if (is_dir($proofDir)) {
        $deletedItems['total_files_deleted'] += deleteDirectoryContents($proofDir);
        $deletedItems['proof_folder'] = true;
    }

    if (is_dir($exifDir)) {
        $deletedItems['total_files_deleted'] += deleteDirectoryContents($exifDir);
        $deletedItems['exif_folder'] = true;
    }

    // ============================================
    // LOG DELETE ACTION IN DATABASE
    // ============================================
    
    $action = 'delete_all_data';
    $description = 'Deleted ' . $deletedItems['database_records_deleted'] . ' submissions and ' . $deletedItems['total_files_deleted'] . ' files';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $stmt = $mysqli->prepare(
        "INSERT INTO admin_actions (action, description, ip_address, user_agent) VALUES (?, ?, ?, ?)"
    );
    
    if ($stmt) {
        $stmt->bind_param('ssss', $action, $description, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
    }

    // ============================================
    // SUCCESS RESPONSE
    // ============================================

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Semua data berhasil dihapus!',
        'details' => $deletedItems,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log('Delete all error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>


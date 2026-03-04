<?php
/**
 * Export Submissions Data to CSV & Photos ZIP (ZIP generator)
 * Reads from MySQL database (DXI_db.submissions)
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once dirname(dirname(__FILE__)) . '/config/database.php';

// Define directories
$baseDir = dirname(__DIR__);
$uploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$macroDir = $uploadsDir . 'macro' . DIRECTORY_SEPARATOR;
$wideDir = $uploadsDir . 'wide' . DIRECTORY_SEPARATOR;
$proofDir = $uploadsDir . 'proof' . DIRECTORY_SEPARATOR;
$exifDir = $uploadsDir . 'exif' . DIRECTORY_SEPARATOR;
$tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'form_dxi_export_' . time();

try {
    // Query submissions from database
    $stmt = $mysqli->prepare("
        SELECT id, uuid, full_name, phone_number, instagram, address, category, category_label, 
               photo_title, photo_count, photo_files, proof_file, exif_file, submitted_at 
        FROM submissions 
        ORDER BY submitted_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception('Query prepare error: ' . $mysqli->error);
    }
    
    $stmt->execute();
    $submissionsData = fetchAllResults($stmt);
    $stmt->close();

    if (empty($submissionsData)) {
        http_response_code(400);
        die('Belum ada data submissions');
    }

    // Create temp directory
    if (!mkdir($tempDir, 0755, true)) {
        http_response_code(500);
        die('Gagal membuat direktori sementara');
    }

    // Create CSV file
    $csvFile = $tempDir . DIRECTORY_SEPARATOR . 'data_submissions.csv';
    $csvHandle = fopen($csvFile, 'w');
    if (!$csvHandle) { 
        deleteDirectory($tempDir); 
        http_response_code(500); 
        die('Gagal membuat file CSV'); 
    }

    // BOM for UTF-8
    fprintf($csvHandle, chr(0xEF).chr(0xBB).chr(0xBF));
    
    $headers = ['No.','ID Pendaftaran','Tanggal Daftar','Nama Lengkap','No. Telepon','Instagram','Alamat','Kategori Lomba','Judul Karya','Jumlah Foto','File Foto','File Proof Pembayaran','File EXIF Data'];
    fputcsv($csvHandle, $headers, ',', '"');
    
    $no = 1;
    foreach ($submissionsData as $submission) {
        // Parse photo_files JSON
        $photoFiles = [];
        if (!empty($submission['photo_files'])) {
            $parsed = json_decode($submission['photo_files'], true);
            if (is_array($parsed)) {
                $photoFiles = $parsed;
            }
        }
        $photoFilesList = !empty($photoFiles) ? implode('; ', $photoFiles) : '-';
        
        $row = [
            $no++, 
            $submission['uuid'] ?? '-',
            $submission['submitted_at'] ?? '-',
            $submission['full_name'] ?? '-', 
            $submission['phone_number'] ?? '-', 
            $submission['instagram'] ?? '-', 
            $submission['address'] ?? '-', 
            $submission['category_label'] ?? '-',
            $submission['photo_title'] ?? '-', 
            $submission['photo_count'] ?? 0, 
            $photoFilesList, 
            $submission['proof_file'] ?? '-', 
            $submission['exif_file'] ?? '-'
        ];
        fputcsv($csvHandle, $row, ',', '"');
    }
    fclose($csvHandle);

    // Create organized folder structure
    $submissionsRootDir = $tempDir . DIRECTORY_SEPARATOR . 'submissions';
    mkdir($submissionsRootDir, 0755, true);

    // Helper to sanitize folder names
    function sanitize_folder_name($name) {
        $name = trim($name);
        if ($name === '') return 'untitled';
        $name = preg_replace('/[^A-Za-z0-9 _\-\.]/u', '_', $name);
        $name = preg_replace('/[_]{2,}/', '_', $name);
        return mb_substr($name, 0, 80);
    }

    // Copy files organized by person and category
    $missing = [];
    foreach ($submissionsData as $submission) {
        $fullName = sanitize_folder_name($submission['full_name'] ?? 'unknown');
        $category = $submission['category'] ?? 'unknown';
        $title = $submission['photo_title'] ?? '';
        $id = $submission['uuid'] ?? uniqid('DXI_', true);
        
        // Create folder: submissions/<fullName>/<category>/<id>_<photoTitle>/
        $personDir = $submissionsRootDir . DIRECTORY_SEPARATOR . $fullName;
        $categoryDir = $personDir . DIRECTORY_SEPARATOR . $category;
        $folderName = $id . '_' . sanitize_folder_name($title);
        $destFolder = $categoryDir . DIRECTORY_SEPARATOR . $folderName;
        
        if (!is_dir($destFolder)) {
            mkdir($destFolder, 0755, true);
        }

        // Copy photo files
        if (!empty($submission['photo_files'])) {
            $photoFiles = json_decode($submission['photo_files'], true) ?? [];
            if (is_array($photoFiles)) {
                foreach ($photoFiles as $photoFile) {
                    $sourceFile = null;
                    
                    // Try based on category first
                    if ($submission['category'] === 'macro' && file_exists($macroDir . $photoFile)) {
                        $sourceFile = $macroDir . $photoFile;
                    } elseif ($submission['category'] === 'wide' && file_exists($wideDir . $photoFile)) {
                        $sourceFile = $wideDir . $photoFile;
                    } else {
                        // Try both dirs as fallback
                        if (file_exists($macroDir . $photoFile)) {
                            $sourceFile = $macroDir . $photoFile;
                        } elseif (file_exists($wideDir . $photoFile)) {
                            $sourceFile = $wideDir . $photoFile;
                        }
                    }

                    if ($sourceFile && file_exists($sourceFile)) {
                        $destFile = $destFolder . DIRECTORY_SEPARATOR . $photoFile;
                        copy($sourceFile, $destFile);
                    } else {
                        $missing[] = $photoFile . ' (from ' . $fullName . ')';
                    }
                }
            }
        }

        // Copy proof file
        if (!empty($submission['proof_file'])) {
            $proofSrc = $proofDir . $submission['proof_file'];
            if (file_exists($proofSrc)) {
                copy($proofSrc, $destFolder . DIRECTORY_SEPARATOR . $submission['proof_file']);
            } else {
                $missing[] = $submission['proof_file'];
            }
        }

        // Copy exif file
        if (!empty($submission['exif_file'])) {
            $exifSrc = $exifDir . $submission['exif_file'];
            if (file_exists($exifSrc)) {
                copy($exifSrc, $destFolder . DIRECTORY_SEPARATOR . $submission['exif_file']);
            } else {
                $missing[] = $submission['exif_file'];
            }
        }
    }

    // Create ZIP archive
    $zipFilename = 'DXI_Submissions_Export_' . date('Y-m-d_His') . '.zip';
    $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFilename;
    $zip = new ZipArchive();
    
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        deleteDirectory($tempDir);
        http_response_code(500);
        die('Gagal membuat file ZIP');
    }

    // Add CSV file
    $zip->addFile($csvFile, 'data_submissions.csv');
    
    // Add submissions folder recursively
    function addFolderToZip($zip, $folderPath, $localPath = '') {
        $files = scandir($folderPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
            $local = $localPath === '' ? $file : ($localPath . '/' . $file);
            if (is_dir($fullPath)) {
                $zip->addEmptyDir('submissions/' . $local);
                addFolderToZip($zip, $fullPath, $local);
            } else {
                $zip->addFile($fullPath, 'submissions/' . $local);
            }
        }
    }

    addFolderToZip($zip, $submissionsRootDir);
    $zip->close();

    // Download ZIP
    if (file_exists($zipPath)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Pragma: public');
        header('Cache-Control: public, must-revalidate');
        readfile($zipPath);
        deleteDirectory($tempDir);
        exit;
    } else {
        deleteDirectory($tempDir);
        http_response_code(500);
        die('Gagal membuat file download');
    }

} catch (Exception $e) {
    error_log('ZIP Export Error: ' . $e->getMessage());
    deleteDirectory($tempDir ?? null);
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Export gagal: ' . $e->getMessage()]);
    exit;
}

// Helper function to recursively delete directory
function deleteDirectory($path) {
    if (!$path || !is_dir($path)) return;
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
    }
    rmdir($path);
}
?>


if (!mkdir($tempDir, 0755, true)) {
    http_response_code(500);
    die('Gagal membuat direktori sementara');
}

$csvFile = $tempDir . DIRECTORY_SEPARATOR . 'data_submissions.csv';
$csvHandle = fopen($csvFile, 'w');
if (!$csvHandle) { deleteDirectory($tempDir); http_response_code(500); die('Gagal membuat file CSV'); }
fprintf($csvHandle, chr(0xEF).chr(0xBB).chr(0xBF));
$headers = ['No.','ID Pendaftaran','Tanggal Daftar','Nama Lengkap','No. Telepon','Instagram','Alamat','Kategori Lomba','Judul Karya','Jumlah Foto','File Foto','File Proof Pembayaran','File EXIF Data','Setuju Pernyataan'];
fputcsv($csvHandle, $headers, ',', '"');
$no = 1;
foreach ($submissionsData as $submission) {
    $photoFilesList = !empty($submission['photoFiles']) ? implode('; ', $submission['photoFiles']) : '-';
    $row = [$no++, $submission['id'] ?? '-', $submission['timestamp'] ?? '-', $submission['fullName'] ?? '-', $submission['phoneNumber'] ?? '-', $submission['instagram'] ?? '-', $submission['address'] ?? '-', $submission['category'] ?? '-', $submission['photoTitle'] ?? '-', $submission['photoCount'] ?? 0, $photoFilesList, $submission['proofFile'] ?? '-', $submission['exifFile'] ?? '-', ($submission['agreement'] ?? false) ? 'Ya' : 'Tidak'];
    fputcsv($csvHandle, $row, ',', '"');
}
fclose($csvHandle);

// Create organized folder 'submissions' with structure: submissions/<fullName>/<category>/<id>_<photoTitle>/
$submissionsRootDir = $tempDir . DIRECTORY_SEPARATOR . 'submissions';
mkdir($submissionsRootDir, 0755, true);

function sanitize_folder_name($name) {
    $name = trim($name);
    if ($name === '') return 'untitled';
    // replace non-filesystem chars with underscore
    $name = preg_replace('/[^A-Za-z0-9 _\-\.]/u', '_', $name);
    // collapse multiple underscores
    $name = preg_replace('/[_]{2,}/', '_', $name);
    // limit length
    return mb_substr($name, 0, 80);
}

$copiedCount = 0;
$missing = [];
foreach ($submissionsData as $submission) {
    $fullName = sanitize_folder_name($submission['fullName'] ?? 'unknown');
    $category = $submission['category'] ?? 'unknown';
    $title = $submission['photoTitle'] ?? '';
    $id = $submission['id'] ?? uniqid('DXI_', true);
    
    // Create folder: submissions/<fullName>/<category>/<id>_<photoTitle>/
    $personDir = $submissionsRootDir . DIRECTORY_SEPARATOR . $fullName;
    $categoryDir = $personDir . DIRECTORY_SEPARATOR . $category;
    $folderName = $id . '_' . sanitize_folder_name($title);
    $destFolder = $categoryDir . DIRECTORY_SEPARATOR . $folderName;
    
    if (!is_dir($destFolder)) {
        mkdir($destFolder, 0755, true);
    }

    if (!empty($submission['photoFiles']) && is_array($submission['photoFiles'])) {
        foreach ($submission['photoFiles'] as $photoFile) {
            $sourceFile = null;
            if (($submission['category'] ?? '') === 'macro' && file_exists($macroDir . $photoFile)) {
                $sourceFile = $macroDir . $photoFile;
            } elseif (($submission['category'] ?? '') === 'wide' && file_exists($wideDir . $photoFile)) {
                $sourceFile = $wideDir . $photoFile;
            } else {
                // try both dirs
                if (file_exists($macroDir . $photoFile)) $sourceFile = $macroDir . $photoFile;
                elseif (file_exists($wideDir . $photoFile)) $sourceFile = $wideDir . $photoFile;
            }

            if ($sourceFile && file_exists($sourceFile)) {
                $destFile = $destFolder . DIRECTORY_SEPARATOR . $photoFile;
                if (copy($sourceFile, $destFile)) { $copiedCount++; }
            } else {
                // Do NOT try other category directories to avoid mixing files between submissions
                $missing[] = $photoFile;
            }
        }
    }

    // Copy proof file into submission folder (if exists)
    if (!empty($submission['proofFile'])) {
        $proofName = $submission['proofFile'];
        $proofSrc = $proofDir . $proofName;
        if (file_exists($proofSrc)) {
            @copy($proofSrc, $destFolder . DIRECTORY_SEPARATOR . $proofName);
        } else {
            $missing[] = $proofName;
        }
    }

    // Copy exif file into submission folder (if exists)
    if (!empty($submission['exifFile'])) {
        $exifName = $submission['exifFile'];
        $exifSrc = $exifDir . $exifName;
        if (file_exists($exifSrc)) {
            @copy($exifSrc, $destFolder . DIRECTORY_SEPARATOR . $exifName);
        } else {
            $missing[] = $exifName;
        }
    }
}

$zipFilename = 'DXI_Submissions_Export_' . date('Y-m-d_His') . '.zip';
$zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFilename;
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { deleteDirectory($tempDir); http_response_code(500); die('Gagal membuat file ZIP'); }
$zip->addFile($csvFile, 'data_submissions.csv');
// Add organized submissions/ folder into ZIP
function addFolderToZip($zip, $folderPath, $localPath = '') {
    $files = scandir($folderPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
        $local = $localPath === '' ? $file : ($localPath . '/' . $file);
        if (is_dir($fullPath)) {
            // add empty dir entry under submissions/
            $zip->addEmptyDir('submissions/' . $local);
            addFolderToZip($zip, $fullPath, $local);
        } else {
            $zip->addFile($fullPath, 'submissions/' . $local);
        }
    }
}

addFolderToZip($zip, $submissionsRootDir, '');
$zip->close();

if (file_exists($zipPath)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Pragma: public');
    header('Cache-Control: public, must-revalidate');
    readfile($zipPath);
    deleteDirectory($tempDir);
    exit;
} else { deleteDirectory($tempDir); http_response_code(500); die('Gagal membuat file download'); }

function deleteDirectory($path) {
    if (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $file) { if ($file !== '.' && $file !== '..') { $filePath = $path . DIRECTORY_SEPARATOR . $file; if (is_dir($filePath)) { deleteDirectory($filePath); } else { unlink($filePath); } } }
        rmdir($path);
    }
}

?>

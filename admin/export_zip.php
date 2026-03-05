<?php
/**
 * Export Submissions Data to ZIP
 * Structure: 
 *   DXI_Export.zip
 *   ├── data_submissions.csv
 *   └── submissions/
 *       ├── macro/
 *       │   └── NamaPeserta/
 *       │       ├── NamaPeserta_JudulKarya_1.jpg
 *       │       ├── NamaPeserta_proof_1.jpg
 *       │       └── NamaPeserta_exif_1.txt
 *       └── wide/
 *           └── NamaPeserta/
 *               └── ...
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

/**
 * Sanitize folder/file name for filesystem
 */
function sanitize_name($name)
{
    $name = trim($name);
    if ($name === '')
        return 'untitled';
    $name = preg_replace('/[^A-Za-z0-9 _\-\.]/u', '_', $name);
    // Replace spaces with underscores
    $name = str_replace(' ', '_', $name);
    // Collapse multiple underscores
    $name = preg_replace('/[_]{2,}/', '_', $name);
    // Trim trailing underscores
    $name = trim($name, '_');
    // Limit length
    return mb_substr($name, 0, 80);
}

/**
 * Recursively delete directory
 */
function deleteDirectory($path)
{
    if (!$path || !is_dir($path))
        return;
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

/**
 * Recursively add folder to ZIP
 */
function addFolderToZip($zip, $folderPath, $zipBasePath = '')
{
    $files = scandir($folderPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..')
            continue;
        $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
        $zipPath = $zipBasePath === '' ? $file : ($zipBasePath . '/' . $file);
        if (is_dir($fullPath)) {
            $zip->addEmptyDir($zipPath);
            addFolderToZip($zip, $fullPath, $zipPath);
        } else {
            $zip->addFile($fullPath, $zipPath);
        }
    }
}

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

    // ============================================
    // CREATE CSV FILE
    // ============================================
    $csvFile = $tempDir . DIRECTORY_SEPARATOR . 'data_submissions.csv';
    $csvHandle = fopen($csvFile, 'w');
    if (!$csvHandle) {
        deleteDirectory($tempDir);
        http_response_code(500);
        die('Gagal membuat file CSV');
    }

    // BOM for UTF-8
    fprintf($csvHandle, chr(0xEF) . chr(0xBB) . chr(0xBF));

    $headers = [
        'No.',
        'ID Pendaftaran',
        'Tanggal Daftar',
        'Nama Lengkap',
        'No. Telepon',
        'Instagram',
        'Alamat',
        'Kategori Lomba',
        'Judul Karya',
        'Jumlah Foto',
        'File Foto',
        'File Bukti Follow',
        'File EXIF Data'
    ];
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

        // Parse proof files JSON
        $proofFilesList = '-';
        if (!empty($submission['proof_file'])) {
            $proofParsed = json_decode($submission['proof_file'], true);
            if (is_array($proofParsed)) {
                $proofFilesList = implode('; ', $proofParsed);
            } else {
                $proofFilesList = $submission['proof_file'];
            }
        }

        // Parse exif files JSON
        $exifFilesList = '-';
        if (!empty($submission['exif_file'])) {
            $exifParsed = json_decode($submission['exif_file'], true);
            if (is_array($exifParsed)) {
                $exifFilesList = implode('; ', $exifParsed);
            } else {
                $exifFilesList = $submission['exif_file'];
            }
        }

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
            $proofFilesList,
            $exifFilesList
        ];
        fputcsv($csvHandle, $row, ',', '"');
    }
    fclose($csvHandle);

    // ============================================
    // CREATE ORGANIZED FOLDER STRUCTURE
    // Structure: submissions/<category>/<participant_name>/files
    // ============================================
    $submissionsRootDir = $tempDir . DIRECTORY_SEPARATOR . 'submissions';
    $macroSubmDir = $submissionsRootDir . DIRECTORY_SEPARATOR . 'macro';
    $wideSubmDir = $submissionsRootDir . DIRECTORY_SEPARATOR . 'wide';
    mkdir($macroSubmDir, 0755, true);
    mkdir($wideSubmDir, 0755, true);

    $missing = [];
    foreach ($submissionsData as $submission) {
        $fullName = sanitize_name($submission['full_name'] ?? 'unknown');
        $category = $submission['category'] ?? 'unknown';
        $photoTitle = sanitize_name($submission['photo_title'] ?? 'karya');

        // Determine parent category folder
        if ($category === 'macro') {
            $categoryDir = $macroSubmDir;
        } elseif ($category === 'wide') {
            $categoryDir = $wideSubmDir;
        } else {
            $categoryDir = $submissionsRootDir . DIRECTORY_SEPARATOR . sanitize_name($category);
            if (!is_dir($categoryDir))
                mkdir($categoryDir, 0755, true);
        }

        // Create participant folder: submissions/<category>/<fullName>/
        $participantDir = $categoryDir . DIRECTORY_SEPARATOR . $fullName;
        if (!is_dir($participantDir)) {
            mkdir($participantDir, 0755, true);
        }

        // ---- Copy & rename PHOTO files ----
        if (!empty($submission['photo_files'])) {
            $photoFiles = json_decode($submission['photo_files'], true) ?? [];
            if (is_array($photoFiles)) {
                $photoIdx = 1;
                foreach ($photoFiles as $photoFile) {
                    $sourceFile = null;

                    // Try based on category first
                    if ($category === 'macro' && file_exists($macroDir . $photoFile)) {
                        $sourceFile = $macroDir . $photoFile;
                    } elseif ($category === 'wide' && file_exists($wideDir . $photoFile)) {
                        $sourceFile = $wideDir . $photoFile;
                    } else {
                        // Fallback: try both dirs
                        if (file_exists($macroDir . $photoFile)) {
                            $sourceFile = $macroDir . $photoFile;
                        } elseif (file_exists($wideDir . $photoFile)) {
                            $sourceFile = $wideDir . $photoFile;
                        }
                    }

                    if ($sourceFile && file_exists($sourceFile)) {
                        $ext = pathinfo($photoFile, PATHINFO_EXTENSION);
                        $newName = $fullName . '_' . $photoTitle . '_' . $photoIdx . '.' . $ext;
                        $destFile = $participantDir . DIRECTORY_SEPARATOR . $newName;

                        // Avoid overwrite if same participant submits multiple times
                        while (file_exists($destFile)) {
                            $photoIdx++;
                            $newName = $fullName . '_' . $photoTitle . '_' . $photoIdx . '.' . $ext;
                            $destFile = $participantDir . DIRECTORY_SEPARATOR . $newName;
                        }

                        copy($sourceFile, $destFile);
                        $photoIdx++;
                    } else {
                        $missing[] = $photoFile . ' (karya dari ' . ($submission['full_name'] ?? 'unknown') . ')';
                    }
                }
            }
        }

        // ---- Copy & rename PROOF files ----
        if (!empty($submission['proof_file'])) {
            $proofFiles = json_decode($submission['proof_file'], true);
            if (!is_array($proofFiles)) {
                $proofFiles = [$submission['proof_file']]; // backward compat: single string
            }
            $proofIdx = 1;
            foreach ($proofFiles as $proofFile) {
                $proofSrc = $proofDir . $proofFile;
                if (file_exists($proofSrc)) {
                    $ext = pathinfo($proofFile, PATHINFO_EXTENSION);
                    $newName = $fullName . '_proof_' . $proofIdx . '.' . $ext;
                    $destFile = $participantDir . DIRECTORY_SEPARATOR . $newName;

                    while (file_exists($destFile)) {
                        $proofIdx++;
                        $newName = $fullName . '_proof_' . $proofIdx . '.' . $ext;
                        $destFile = $participantDir . DIRECTORY_SEPARATOR . $newName;
                    }

                    copy($proofSrc, $destFile);
                    $proofIdx++;
                } else {
                    $missing[] = $proofFile . ' (bukti dari ' . ($submission['full_name'] ?? 'unknown') . ')';
                }
            }
        }

        // ---- Copy & rename EXIF files ----
        if (!empty($submission['exif_file'])) {
            $exifFiles = json_decode($submission['exif_file'], true);
            if (!is_array($exifFiles)) {
                $exifFiles = [$submission['exif_file']]; // backward compat: single string
            }
            $exifIdx = 1;
            foreach ($exifFiles as $exifFile) {
                $exifSrc = $exifDir . $exifFile;
                if (file_exists($exifSrc)) {
                    $ext = pathinfo($exifFile, PATHINFO_EXTENSION);
                    $newName = $fullName . '_exif_' . $exifIdx . '.' . $ext;
                    $destFile = $participantDir . DIRECTORY_SEPARATOR . $newName;

                    while (file_exists($destFile)) {
                        $exifIdx++;
                        $newName = $fullName . '_exif_' . $exifIdx . '.' . $ext;
                        $destFile = $participantDir . DIRECTORY_SEPARATOR . $newName;
                    }

                    copy($exifSrc, $destFile);
                    $exifIdx++;
                } else {
                    $missing[] = $exifFile . ' (exif dari ' . ($submission['full_name'] ?? 'unknown') . ')';
                }
            }
        }
    }

    // ============================================
    // CREATE ZIP ARCHIVE
    // ============================================
    $zipFilename = 'DXI_Submissions_Export_' . date('Y-m-d_His') . '.zip';
    $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFilename;
    $zip = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        deleteDirectory($tempDir);
        http_response_code(500);
        die('Gagal membuat file ZIP');
    }

    // Add CSV file at root level
    $zip->addFile($csvFile, 'data_submissions.csv');

    // Add submissions folder recursively
    addFolderToZip($zip, $submissionsRootDir, 'submissions');

    $zip->close();

    // ============================================
    // DOWNLOAD ZIP
    // ============================================
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
    if (isset($tempDir))
        deleteDirectory($tempDir);
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Export gagal: ' . $e->getMessage()]);
    exit;
}
?>
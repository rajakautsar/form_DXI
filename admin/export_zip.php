<?php
/**
 * Export Submissions Data to Excel & Photos ZIP (ZIP generator)
 *
 * This file is intended to be called directly to download ZIP.
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Determine base directory
$baseDir = dirname(__DIR__);
$uploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$submissionsFile = $uploadsDir . 'submissions.json';
$macroDir = $uploadsDir . 'macro' . DIRECTORY_SEPARATOR;
$wideDir = $uploadsDir . 'wide' . DIRECTORY_SEPARATOR;
$tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'form_dxi_export_' . time();
$proofDir = $uploadsDir . 'proof' . DIRECTORY_SEPARATOR;
$exifDir = $uploadsDir . 'exif' . DIRECTORY_SEPARATOR;

if (!file_exists($submissionsFile)) {
    http_response_code(404);
    die('File submissions tidak ditemukan');
}

$submissionsData = json_decode(file_get_contents($submissionsFile), true);
if (!$submissionsData || !is_array($submissionsData)) {
    http_response_code(400);
    die('Data submissions tidak valid');
}

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

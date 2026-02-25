<?php
/**
 * Export CSV only (Excel-compatible)
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);

$baseDir = dirname(__DIR__);
$uploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$submissionsFile = $uploadsDir . 'submissions.json';

if (!file_exists($submissionsFile)) {
    http_response_code(404);
    die('File submissions tidak ditemukan');
}

$submissionsData = json_decode(file_get_contents($submissionsFile), true);
if (!$submissionsData || !is_array($submissionsData) || count($submissionsData) === 0) {
    http_response_code(200);
    header('Content-Type: text/plain; charset=utf-8');
    echo "No submissions available to export";
    exit;
}

$csvFilename = 'data_submissions_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $csvFilename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
$headers = ['No.','ID Pendaftaran','Tanggal Daftar','Nama Lengkap','No. Telepon','Instagram','Alamat','Kategori Lomba','Judul Karya','Jumlah Foto','File Foto','File Proof Pembayaran','File EXIF Data','Setuju Pernyataan'];
fputcsv($output, $headers, ',', '"');

$no = 1;
foreach ($submissionsData as $submission) {
    $photoFilesList = !empty($submission['photoFiles']) ? implode('; ', $submission['photoFiles']) : '-';
    $row = [$no++, $submission['id'] ?? '-', $submission['timestamp'] ?? '-', $submission['fullName'] ?? '-', $submission['phoneNumber'] ?? '-', $submission['instagram'] ?? '-', $submission['address'] ?? '-', $submission['category'] ?? '-', $submission['photoTitle'] ?? '-', $submission['photoCount'] ?? 0, $photoFilesList, $submission['proofFile'] ?? '-', $submission['exifFile'] ?? '-', ($submission['agreement'] ?? false) ? 'Ya' : 'Tidak'];
    fputcsv($output, $row, ',', '"');
}

fclose($output);
exit;

?>

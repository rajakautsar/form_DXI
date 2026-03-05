<?php
/**
 * Get Statistics - Admin Dashboard
 * 
 * Query dari MySQL database DXI_db
 * Return JSON dengan statistik submissions dan kategori
 */

header('Content-Type: application/json');

require_once dirname(dirname(__FILE__)) . '/config/database.php';

$response = [
    'success' => false,
    'total_submissions' => 0,
    'macro_count' => 0,
    'wide_count' => 0,
    'total_photos' => 0,
    'submissions' => []
];

try {
    // Query using the vw_submission_stats view
    $stmt = $mysqli->prepare("SELECT * FROM vw_submission_stats");

    if (!$stmt) {
        throw new Exception('Query prepare error: ' . $mysqli->error);
    }

    $stmt->execute();
    $stats = fetchSingleResult($stmt);
    $stmt->close();

    if ($stats) {
        $response['total_submissions'] = intval($stats['total_submissions'] ?? 0);
        $response['macro_count'] = intval($stats['macro_count'] ?? 0);
        $response['wide_count'] = intval($stats['wide_count'] ?? 0);

        // Calculate true total files (karya + proof + exif) from photo_files table
        $countStmt = $mysqli->prepare("SELECT COUNT(*) as total_files FROM photo_files");
        if ($countStmt) {
            $countStmt->execute();
            $fileCountResult = fetchSingleResult($countStmt);
            $response['total_photos'] = intval($fileCountResult['total_files'] ?? $stats['total_photos']);
            $countStmt->close();
        } else {
            $response['total_photos'] = intval($stats['total_photos'] ?? 0);
        }
    }

    // Get recent submissions (last 10)
    $stmt = $mysqli->prepare("
        SELECT id, uuid, full_name, category, category_label, photo_count, photo_title, submitted_at, photo_files, proof_file, exif_file
        FROM submissions 
        ORDER BY submitted_at DESC 
        LIMIT 10
    ");

    if (!$stmt) {
        throw new Exception('Query prepare error: ' . $mysqli->error);
    }

    $stmt->execute();
    $results = fetchAllResults($stmt);
    $stmt->close();

    // Map database field names to JavaScript field names
    $response['submissions'] = array_map(function ($row) {
        // Calculate total files
        $photoFiles = json_decode($row['photo_files'], true) ?? [];

        $proofFiles = json_decode($row['proof_file'], true) ?? [];
        if (!is_array($proofFiles) && !empty($row['proof_file']))
            $proofFiles = [$row['proof_file']];

        $exifFiles = json_decode($row['exif_file'], true) ?? [];
        if (!is_array($exifFiles) && !empty($row['exif_file']))
            $exifFiles = [$row['exif_file']];

        $totalFiles = count($photoFiles) + count($proofFiles) + count($exifFiles);

        return [
            'id' => $row['uuid'],
            'fullName' => $row['full_name'],
            'category' => $row['category'],
            'categoryLabel' => $row['category_label'],
            'fileCount' => $totalFiles,
            'photoTitle' => $row['photo_title'],
            'timestamp' => $row['submitted_at']
        ];
    }, $results);

    $response['success'] = true;

} catch (Exception $e) {
    error_log('Get stats error: ' . $e->getMessage());
    $response['error'] = $e->getMessage();
    $response['success'] = false;
}

echo json_encode($response);
?>
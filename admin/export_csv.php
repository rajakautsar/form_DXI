<?php
/**
 * Export Submissions to CSV (MySQL DXI_db)
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once dirname(dirname(__FILE__)) . '/config/database.php';

try {
    $stmt = $mysqli->prepare("SELECT id, uuid, full_name, phone_number, instagram, address, category_label, photo_title, photo_count, submitted_at FROM submissions ORDER BY submitted_at DESC");
    
    if (!$stmt) {
        throw new Exception('Query error: ' . $mysqli->error);
    }
    
    $stmt->execute();
    $submissions = fetchAllResults($stmt);
    $stmt->close();
    
    $filename = 'DXI_submissions_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    $headers = ['No.', 'ID', 'Nama', 'Telepon', 'Instagram', 'Alamat', 'Kategori', 'Judul Karya', 'Jumlah Foto', 'Tanggal Submit'];
    fputcsv($output, $headers, ',', '"');
    
    $no = 1;
    foreach ($submissions as $row) {
        fputcsv($output, [$no++, $row['id'], $row['full_name'], $row['phone_number'], $row['instagram'], substr($row['address'], 0, 50), $row['category_label'], $row['photo_title'], $row['photo_count'], date('Y-m-d H:i', strtotime($row['submitted_at']))], ',', '"');
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log('CSV Export Error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Export gagal']);
    exit;
}
?>

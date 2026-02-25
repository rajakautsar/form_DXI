<?php
/**
 * Export Submissions Data to Excel & Photos ZIP
 * 
 * URL: http://localhost/form_DXI/admin/export
 * Access: Manual URL (no button)
 * 
 * Output:
 * - ZIP file containing:
 *   - data_submissions.csv (Excel compatible)
 *   - folder: photos/ (all uploaded photos)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================
// SETUP PATHS
// ============================================
// Determine the base directory correctly
if (defined('STDIN')) {
    // Command line execution
    $baseDir = realpath(dirname(dirname(__FILE__)));
} else {
    // Web execution
    $baseDir = dirname(dirname(__FILE__));
}
// Admin Export UI
// Shows counts and buttons to download CSV or ZIP (CSV + photos)

$uploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$submissionsFile = $uploadsDir . 'submissions.json';
$submissionsData = [];
if (file_exists($submissionsFile)) {
    $submissionsData = json_decode(file_get_contents($submissionsFile), true) ?: [];
}

// Compute photo statistics: total referenced and available files on disk
$totalReferencedPhotos = 0;
$availablePhotos = 0;
$missingPhotos = [];
foreach ($submissionsData as $s) {
    if (!empty($s['photoFiles']) && is_array($s['photoFiles'])) {
        $totalReferencedPhotos += count($s['photoFiles']);
        foreach ($s['photoFiles'] as $pf) {
            $macroPath = $uploadsDir . 'macro' . DIRECTORY_SEPARATOR . $pf;
            $widePath = $uploadsDir . 'wide' . DIRECTORY_SEPARATOR . $pf;
            if (file_exists($macroPath) || file_exists($widePath)) {
                $availablePhotos++;
            } else {
                $missingPhotos[] = $pf;
            }
        }
    }
}

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Export - DXI</title>
    <style>
        :root{--bg:#f6f8fb;--card:#fff;--primary:#0066cc;--accent:#00a8cc;--muted:#657786}
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;margin:0;background:var(--bg);color:#111}
        .wrap{max-width:980px;margin:36px auto;padding:20px}
        .card{background:var(--card);padding:20px;border-radius:12px;box-shadow:0 6px 18px rgba(16,24,40,0.06)}
        h1{margin:0 0 6px;font-size:22px}
        p.lead{margin:0;color:var(--muted)}
        .stats{display:flex;gap:12px;margin:18px 0}
        .stat{flex:1;background:linear-gradient(180deg,#fff,#fbfdff);padding:14px;border-radius:10px;border:1px solid #eef3fb}
        .stat h3{margin:0;font-size:20px}
        .actions{display:flex;gap:12px;align-items:center;margin-top:18px}
        .btn{display:inline-flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:0;cursor:pointer;font-weight:600}
        .btn--csv{background:#f7fafc;color:#0b5;box-shadow:inset 0 -1px 0 rgba(0,0,0,0.02)}
        .btn--zip{background:linear-gradient(90deg,var(--primary),var(--accent));color:#fff}
        .btn svg{width:18px;height:18px}
        .note{margin-top:12px;color:var(--muted);font-size:14px}
        .preview{margin-top:16px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:8px;border-bottom:1px solid #f1f5f9;text-align:left;font-size:13px}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Export Submissions</h1>
            <p class="lead">Download data pendaftaran peserta. CSV dapat dibuka di Excel. Zip berisi CSV dan semua foto karya.</p>

            <div class="stats">
                <div class="stat">
                    <div style="color:var(--muted)">Total Submissions</div>
                    <h3><?php echo count($submissionsData); ?></h3>
                </div>
                <div class="stat">
                    <div style="color:var(--muted)">Photos Referenced / Available</div>
                    <h3><?php echo $totalReferencedPhotos . ' / ' . $availablePhotos; ?></h3>
                </div>
            </div>

            <div class="actions">
                <form method="get" action="export_csv.php" style="margin:0">
                    <button class="btn btn--csv" type="submit" title="Download CSV">
                        <!-- CSV icon -->
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v10" stroke="#064E3B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 9l-7 7-7-7" stroke="#064E3B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Download CSV
                    </button>
                </form>

                <form method="get" action="export_zip.php" style="margin:0">
                    <button class="btn btn--zip" type="submit" title="Download ZIP (CSV + Photos)">
                        <!-- ZIP icon -->
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3v4h-8V3" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Download ZIP
                    </button>
                </form>

                <div style="margin-left:auto;color:var(--muted);font-size:13px">Note: Admin access only. No buttons required elsewhere.</div>
            </div>

            <div class="note">Jika data tidak muncul setelah menekan tombol, pastikan file <strong>uploads/submissions.json</strong> berisi data pendaftaran dan folder foto ada di <strong>uploads/macro</strong> atau <strong>uploads/wide</strong>.</div>
            <?php if (!empty($missingPhotos)): ?>
                <div style="margin-top:12px;background:#fff6f6;border:1px solid #ffdddd;padding:10px;border-radius:8px;color:#9b1c1c;font-size:13px">
                    <strong>Missing photo files detected:</strong>
                    <div style="margin-top:6px">
                        <?php echo htmlspecialchars(implode(', ', array_unique($missingPhotos))); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="preview">
                <h4>Recent Submissions</h4>
                <table>
                    <thead><tr><th>No.</th><th>Nama</th><th>Kategori</th><th>Jumlah Foto</th><th>ID</th></tr></thead>
                    <tbody>
                        <?php $i=1; foreach(array_slice($submissionsData,0,10) as $s): ?>
                            <tr><td><?php echo $i++; ?></td><td><?php echo htmlspecialchars($s['fullName'] ?? '-'); ?></td><td><?php echo htmlspecialchars($s['category'] ?? '-'); ?></td><td><?php echo count($s['photoFiles'] ?? []); ?></td><td><?php echo htmlspecialchars($s['id'] ?? '-'); ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (count($submissionsData)===0): ?><tr><td colspan="5">No submissions found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>


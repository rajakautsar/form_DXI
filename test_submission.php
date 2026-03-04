<?php
/**
 * Test Form Submission Script
 * 
 * Simulates form submission untuk test database integration
 * Run: php test_submission.php
 */

echo "========================================\n";
echo "🧪 TESTING FORM SUBMISSION\n";
echo "========================================\n\n";

// Test data
$testData = [
    'fullName' => 'Budi Hasanudin',
    'phoneNumber' => '+6281234567890',
    'instagram' => '@budi_ocean',
    'address' => 'Jl. Pantai Utama No. 42, Bali',
    'category' => 'macro',
    'photoTitle' => 'Nudibranch Paradise',
    'camera' => 'Canon EOS 5D Mark IV',
    'lens' => 'Canon EF 100mm F/2.8 Macro',
    'shutter' => '1/200',
    'aperture' => 'f/8',
    'iso' => '400',
    'location' => 'Amed, Bali',
    'agreement' => '1'
];

echo "📝 Test Data:\n";
foreach ($testData as $key => $value) {
    echo "   $key: $value\n";
}
echo "\n";

// Connect to database
echo "🔗 Connecting to database...\n";

require_once __DIR__ . '/config/database.php';

try {
    // Test connection
    if ($mysqli->connect_error) {
        throw new Exception('Connection failed: ' . $mysqli->connect_error);
    }
    
    echo "✅ Connected to database 'DXI_db'\n\n";
    
    // Generate test UUID
    $uuid = 'DXI_' . uniqid() . '_' . time();
    
    // Prepare insert statement
    echo "📤 Inserting test data...\n";
    
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
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    // Prepare data
    $photo_files_json = json_encode(['1234567890_test_macro_1.jpg']);
    $categoryLabel = 'Macro Angle';
    $photoCount = 1;
    $proofFile = '1234567890_proof.jpg';
    $exifFile = '1234567890_exif.txt';
    $ipAddress = '127.0.0.1';
    $userAgent = 'Test/Script';
    $agreementInt = 1;
    
    // Bind parameters with correct types
    // s=string, i=integer
    $types = 'ssssssssisssssssssss'; // 21 chars for 21 parameters
    
    $params = [
        $uuid,
        $testData['fullName'],
        $testData['phoneNumber'],
        $testData['instagram'],
        $testData['address'],
        $testData['category'],
        $categoryLabel,
        $testData['photoTitle'],
        $photoCount,
        $photo_files_json,
        $proofFile,
        $exifFile,
        $testData['camera'],
        $testData['lens'],
        $testData['shutter'],
        $testData['aperture'],
        $testData['iso'],
        $testData['location'],
        $agreementInt,
        $ipAddress,
        $userAgent
    ];
    
    // Use call_user_func_array for better binding
    $refParams = [&$types];
    foreach ($params as &$param) {
        $refParams[] = &$param;
    }
    call_user_func_array([$stmt, 'bind_param'], $refParams);
    
    // Execute
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $insertId = $stmt->insert_id;
    $stmt->close();
    
    echo "✅ Data inserted successfully!\n";
    echo "   Insert ID: $insertId\n";
    echo "   UUID: $uuid\n\n";
    
    // Verify data
    echo "🔍 Verifying data...\n";
    
    $stmt = $mysqli->prepare("SELECT * FROM submissions WHERE uuid = ?");
    $stmt->bind_param('s', $uuid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row) {
        echo "✅ Data retrieved successfully!\n";
        echo "   ID: {$row['id']}\n";
        echo "   Name: {$row['full_name']}\n";
        echo "   Category: {$row['category']} ({$row['category_label']})\n";
        echo "   Instagram: {$row['instagram']}\n";
        echo "   Submitted: {$row['submitted_at']}\n";
    } else {
        throw new Exception('Data not found after insert!');
    }
    
    echo "\n";
    
    // Check statistics view
    echo "📊 Checking statistics...\n";
    
    $stmt = $mysqli->prepare("SELECT * FROM vw_submission_stats");
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    echo "✅ Statistics:\n";
    echo "   Total submissions: {$stats['total_submissions']}\n";
    echo "   Macro count: {$stats['macro_count']}\n";
    echo "   Wide count: {$stats['wide_count']}\n";
    echo "   Total photos: {$stats['total_photos']}\n";
    echo "   Latest submission: {$stats['latest_submission']}\n";
    
    echo "\n";
    
    // Check audit log
    echo "📝 Checking admin actions table...\n";
    
    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM admin_actions");
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    echo "✅ Admin actions logged: {$count['count']}\n\n";
    
    echo "========================================\n";
    echo "🎉 ALL TESTS PASSED!\n";
    echo "========================================\n";
    echo "\nNext steps:\n";
    echo "1. Open browser: http://localhost:8000/\n";
    echo "2. Fill form and submit\n";
    echo "3. Check admin panel: http://localhost:8000/admin/\n";
    echo "4. Verify data in database\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}

?>

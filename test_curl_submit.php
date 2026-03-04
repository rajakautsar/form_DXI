<?php
/**
 * Test submission dengan curl
 */

// Sample form data
$form_data = [
    'fullName' => 'Budi Ahmad',
    'phoneNumber' => '+62812345678',
    'instagram' => '@buditest',
    'address' => 'Jakarta',
    'category' => 'wide',
    'photoTitle' => 'Test Photo',
    'camera' => 'Canon 5D',
    'lens' => 'Canon 24-70mm',
    'shutter' => '1/100',
    'aperture' => 'f/11',
    'iso' => '200',
    'location' => 'Coral',
    'agreement' => 'on'
];

// Create test files
$test_dir = sys_get_temp_dir();
file_put_contents("$test_dir/test_photo.jpg", "fake jpg content for test");
file_put_contents("$test_dir/test_proof.jpg", "fake proof jpg for test");
file_put_contents("$test_dir/test_exif.txt", "fake exif data");

echo "Testing form submission...\n\n";

// Build curl command
$curl_cmd = 'curl -X POST http://localhost:8000/process_form.php';

// Add form fields
foreach ($form_data as $key => $value) {
    $curl_cmd .= " -F \"$key=$value\"";
}

// Add files
$curl_cmd .= " -F \"photoFile[]=@$test_dir/test_photo.jpg\"";
$curl_cmd .= " -F \"proofFile=@$test_dir/test_proof.jpg\"";
$curl_cmd .= " -F \"exifFile=@$test_dir/test_exif.txt\"";

echo "Command: $curl_cmd\n\n";

// Execute
echo "Response:\n";
system($curl_cmd);

echo "\n\nCleanup...\n";
unlink("$test_dir/test_photo.jpg");
unlink("$test_dir/test_proof.jpg");
unlink("$test_dir/test_exif.txt");

echo "Test complete!\n";
?>

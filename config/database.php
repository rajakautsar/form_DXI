<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'DXI_db');
define('DB_PORT', 3306);


try {
    // MySQLi connection
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception('Connection failed: ' . $mysqli->connect_error);
    }
    
    // Set charset to UTF-8
    $mysqli->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log error
    error_log('Database connection error: ' . $e->getMessage());
    
    // Return error response if this is API
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection error'
        ]);
        exit;
    }
    
    die('Database Error: ' . htmlspecialchars($e->getMessage()));
}


function executeQuery($mysqli, $query, $types = '', $params = []) {
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    return $stmt;
}

/**
 * Fetch associative array result
 */
function fetchAllResults($stmt) {
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Fetch single row
 */
function fetchSingleResult($stmt) {
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

?>

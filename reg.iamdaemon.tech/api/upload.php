<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$username = $_SESSION['username'];
$uploadDir = "/var/www/users/$username/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$allowedExt = ['html','htm','css','js','json','txt','xml','png','jpg','jpeg','gif','svg','ico','pdf','md','zip'];
$maxSize = 20 * 1024 * 1024; // 20MB

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = basename($file['name']);

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload error code: ' . $file['error']]);
    exit;
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large (max 20MB)']);
    exit;
}

if (!in_array($ext, $allowedExt)) {
    http_response_code(400);
    echo json_encode(['error' => 'Extension not allowed']);
    exit;
}

if (preg_match('/\.\./i', $filename) || !preg_match('/^[a-z0-9\.\-_]+$/i', $filename)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid filename']);
    exit;
}

$target = $uploadDir . $filename;
if (move_uploaded_file($file['tmp_name'], $target)) {
    chmod($target, 0644);
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
}
?>
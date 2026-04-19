<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['username'])) { http_response_code(401); echo json_encode(['error' => 'Не авторизован']); exit; }

$username = $_SESSION['username'];
$uploadDir = "/var/www/users/$username/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$allowedExt = ['html','htm','css','js','json','txt','xml','png','jpg','jpeg','gif','svg','ico','pdf','md','zip'];
$maxSize = 10 * 1024 * 1024;

if (!isset($_FILES['file'])) { http_response_code(400); echo json_encode(['error' => 'Файл не выбран']); exit; }

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = basename($file['name']);

if ($file['size'] > $maxSize) { http_response_code(400); echo json_encode(['error' => 'Файл слишком большой']); exit; }
if (!in_array($ext, $allowedExt)) { http_response_code(400); echo json_encode(['error' => 'Тип файла не поддерживается']); exit; }
if (preg_match('/\.\./i', $filename) || !preg_match('/^[a-z0-9\.\-_]+$/i', $filename)) { http_response_code(400); echo json_encode(['error' => 'Недопустимое имя файла']); exit; }

$target = $uploadDir . $filename;
if (move_uploaded_file($file['tmp_name'], $target)) {
    chmod($target, 0644);
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сохранения на сервере']);
}
?>

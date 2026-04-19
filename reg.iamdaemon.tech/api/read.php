<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['username'])) { http_response_code(401); echo json_encode(['error' => 'Не авторизован']); exit; }

$filename = $_GET['file'] ?? '';
if (!$filename || !preg_match('/^[a-z0-9\.\-_]+$/i', $filename)) { http_response_code(400); echo json_encode(['error' => 'Недопустимое имя файла']); exit; }

$userDir = '/var/www/users/' . $_SESSION['username'];
$target = realpath("$userDir/$filename");

if ($target === false || strpos($target, $userDir) !== 0 || !is_file($target)) { http_response_code(404); echo json_encode(['error' => 'Файл не найден']); exit; }

$ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
$allowed = ['html','htm','css','js','json','txt','xml','md','svg'];
if (!in_array($ext, $allowed)) { http_response_code(400); echo json_encode(['error' => 'Редактирование этого типа запрещено']); exit; }

echo json_encode(['content' => file_get_contents($target)]);
?>

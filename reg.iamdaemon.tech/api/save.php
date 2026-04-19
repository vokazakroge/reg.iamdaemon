<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['username'])) { http_response_code(401); echo json_encode(['error' => 'Не авторизован']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$filename = $input['file'] ?? '';
$content = $input['content'] ?? '';

if (!$filename || !preg_match('/^[a-z0-9\.\-_]+$/i', $filename)) { http_response_code(400); echo json_encode(['error' => 'Недопустимое имя файла']); exit; }

$userDir = '/var/www/users/' . $_SESSION['username'];
$target = realpath("$userDir/$filename");

if ($target === false || strpos($target, $userDir) !== 0 || !is_file($target)) { http_response_code(404); echo json_encode(['error' => 'Файл не найден']); exit; }

if (file_put_contents($target, $content) !== false) { echo json_encode(['success' => true]); }
else { http_response_code(500); echo json_encode(['error' => 'Ошибка сохранения']); }
?>

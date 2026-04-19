<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$username = strtolower(trim($input['username'] ?? ''));
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

$errors = [];
if (!preg_match('/^[a-z0-9-]{3,20}$/', $username)) $errors[] = 'Имя: только a–z, 0–9 и дефис, 3–20 символов';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
if (strlen($password) < 8) $errors[] = 'Пароль: минимум 8 символов';

if ($errors) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}

$dbPath = __DIR__ . '/../data/users.db';
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) mkdir($dbDir, 0755, true);

$db = new SQLite3($dbPath);
$db->busyTimeout(5000);
$db->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE NOT NULL, email TEXT NOT NULL, password_hash TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');

$stmt = $db->prepare('SELECT id FROM users WHERE username = :u');
$stmt->bindValue(':u', $username, SQLITE3_TEXT);
if ($stmt->execute()->fetchArray()) {
    $db->close();
    http_response_code(409);
    echo json_encode(['errors' => ['Это имя уже занято']]);
    exit;
}

// === НАДЁЖНАЯ КОПИЯ ШАБЛОНА ===
$baseDir = '/var/www/users';
$templateIndex = "$baseDir/template/index.html";
$userDir = "$baseDir/$username";

// 1. Создаём шаблон, если его нет
if (!is_dir("$baseDir/template")) mkdir("$baseDir/template", 0755, true);
if (!file_exists($templateIndex)) {
    file_put_contents($templateIndex, '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>USER</title><style>body{margin:0;min-height:100vh;display:flex;justify-content:center;align-items:center;background:#0a0a0f;color:#e2e8f0;font-family:system-ui,sans-serif}h1{font-family:Orbitron,sans-serif;color:#8b5cf6}</style></head><body><h1>✦ страница пользователя</h1></body></html>');
}

// 2. Создаём папку пользователя и копируем только index.html
if (!is_dir($userDir)) mkdir($userDir, 0755, true);
if (!copy($templateIndex, "$userDir/index.html")) {
    error_log("PHP: Failed to copy template to $userDir");
    $db->close();
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка создания страницы']);
    exit;
}
// Выставляем права на созданную папку и файл
chmod($userDir, 0755);
chmod("$userDir/index.html", 0644);
// ============================

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :h)');
$stmt->bindValue(':u', $username, SQLITE3_TEXT);
$stmt->bindValue(':e', $email, SQLITE3_TEXT);
$stmt->bindValue(':h', $hash, SQLITE3_TEXT);
if (!$stmt->execute()) {
    $db->close();
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сохранения в БД']);
    exit;
}

$db->close();
echo json_encode(['success' => true, 'username' => $username]);
?>

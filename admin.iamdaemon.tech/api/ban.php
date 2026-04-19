<?php
require_once __DIR__ . '/../../reg.iamdaemon.tech/config.php';
header('Content-Type: application/json; charset=utf-8');

requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$username = strtolower(trim($input['username'] ?? ''));
$status = $input['status'] ?? 'active';

if (!$username || !in_array($status, ['active', 'banned'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Нельзя забанить админа
if ($username === getAdminUsername()) {
    http_response_code(403);
    echo json_encode(['error' => 'Cannot ban admin']);
    exit;
}

$db = getDb();
$stmt = $db->prepare('UPDATE users SET status = :s WHERE username = :u');
$stmt->bindValue(':s', $status, SQLITE3_TEXT);
$stmt->bindValue(':u', $username, SQLITE3_TEXT);

if ($stmt->execute()) {
    // Если бан — удаляем папку с файлами (опционально)
    if ($status === 'banned') {
        $userDir = "/var/www/users/$username";
        if (is_dir($userDir)) {
            // Можно добавить рекурсивное удаление, но пока просто переименуем
            rename($userDir, "/var/www/users_banned/$username");
        }
    }
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
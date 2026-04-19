<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Читаем JSON из POST-тела
$input = json_decode(file_get_contents('php://input'), true);
$username = strtolower(trim($input['username'] ?? ''));

if (!$username || !preg_match('/^[a-z0-9-]{3,20}$/', $username)) {
    echo json_encode(['available' => false]);
    exit;
}

$dbPath = realpath(__DIR__ . '/../data/users.db');
if ($dbPath === false || !file_exists($dbPath)) {
    echo json_encode(['available' => true, 'debug' => 'db_missing']);
    exit;
}

try {
    $db = new SQLite3($dbPath, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000);
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM users WHERE username = :u');
    $stmt->bindValue(':u', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $db->close();

    $count = (int)($row['cnt'] ?? 0);
    echo json_encode(['available' => $count === 0]);
} catch (Exception $e) {
    error_log("CHECK_USERNAME EXCEPTION: " . $e->getMessage());
    echo json_encode(['available' => false, 'error' => 'db_query_failed']);
}
?>

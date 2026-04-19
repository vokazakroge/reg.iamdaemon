<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '/var/www/reg.iamdaemon.tech/config.php';
    
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $username = strtolower(trim($input['username'] ?? ''));
    $status = $input['status'] ?? 'active';

    if (!$username || !in_array($status, ['active', 'banned'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

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
        $userDir = "/var/www/users/$username";
        $bannedDir = "/var/www/users_banned/$username";
        
        if ($status === 'banned') {
            // БАН: перемещаем из users в users_banned
            if (is_dir($userDir)) {
                if (!is_dir(dirname($bannedDir))) {
                    mkdir(dirname($bannedDir), 0755, true);
                }
                rename($userDir, $bannedDir);
                error_log("BANNED: $username - moved to $bannedDir");
            }
        } else {
            // РАЗБАН: перемещаем из users_banned в users
            if (is_dir($bannedDir)) {
                if (!is_dir($userDir)) {
                    rename($bannedDir, $userDir);
                    error_log("UNBANNED: $username - moved to $userDir");
                } else {
                    // Папка уже есть в users, удаляем banned
                    array_map('unlink', glob("$bannedDir/*"));
                    rmdir($bannedDir);
                }
            } else {
                // Если banned папки нет, создаём пустую users
                if (!is_dir($userDir)) {
                    mkdir($userDir, 0755, true);
                }
            }
        }
        
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
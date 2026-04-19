<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$username = strtolower(trim($input['username'] ?? ''));
$code = trim($input['code'] ?? '');

$db = getDb();

$stmt = $db->prepare('SELECT id, code, verified FROM users WHERE username = :u');
$stmt->bindValue(':u', $username, SQLITE3_TEXT);
$user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Пользователь не найден']);
    exit;
}

if ($user['verified']) {
    echo json_encode(['error' => 'Аккаунт уже активирован']);
    exit;
}

if ($user['code'] == $code) {
    // Активируем
    $stmt = $db->prepare('UPDATE users SET verified = 1 WHERE id = :id');
    $stmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);
    $stmt->execute();

    // Создаём папку и шаблон
    $userDir = "/var/www/users/$username";
    if (!is_dir($userDir)) {
        mkdir($userDir, 0755, true);
        
        // Создаём index.html из шаблона
        $template = getTemplateIndex();
        $indexContent = str_replace('{{username}}', $username, $template);
        file_put_contents("$userDir/index.html", $indexContent);
        chmod("$userDir/index.html", 0644);
        
        // Создаём .htaccess для чистых URL
        $htaccess = "Options -Indexes\n<IfModule mod_rewrite.c>\n  RewriteEngine On\n  RewriteCond %{REQUEST_FILENAME} !-f\n  RewriteCond %{REQUEST_FILENAME} !-d\n  RewriteRule ^ index.html [L]\n</IfModule>";
        file_put_contents("$userDir/.htaccess", $htaccess);
        chmod("$userDir/.htaccess", 0644);
        
        chown($userDir, 'www-data');
        chown("$userDir/index.html", 'www-data');
        chown("$userDir/.htaccess", 'www-data');
    }

    // Логиним
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;

    echo json_encode(['success' => true, 'redirect' => "https://reg.iamdaemon.tech/dashboard"]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный код']);
}
?>
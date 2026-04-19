<?php
// Гарантируем JSON-ответ даже при фатальных ошибках
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/send_mail.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $username = strtolower(trim($input['username'] ?? ''));
    $email = strtolower(trim($input['email'] ?? ''));
    $password = $input['password'] ?? '';

    if (!$username || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Заполни все поля']);
        exit;
    }

    if (!preg_match('/^[a-z0-9-]{3,20}$/', $username)) {
        http_response_code(400);
        echo json_encode(['error' => 'Некорректное имя (a-z, 0-9, -, 3-20 символов)']);
        exit;
    }

    $db = getDb();

    // Проверка занятости
    $stmt = $db->prepare('SELECT id FROM users WHERE username = :u OR email = :e');
    $stmt->bindValue(':u', $username, SQLITE3_TEXT);
    $stmt->bindValue(':e', $email, SQLITE3_TEXT);
    if ($stmt->execute()->fetchArray()) {
        http_response_code(409);
        echo json_encode(['error' => 'Ник или Email уже заняты']);
        exit;
    }

    $code = (string)rand(100000, 999999);
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, code, verified) VALUES (:u, :e, :h, :c, 0)');
    $stmt->bindValue(':u', $username, SQLITE3_TEXT);
    $stmt->bindValue(':e', $email, SQLITE3_TEXT);
    $stmt->bindValue(':h', $hash, SQLITE3_TEXT);
    $stmt->bindValue(':c', $code, SQLITE3_TEXT);

    if (!$stmt->execute()) {
        throw new Exception('Ошибка записи в базу данных');
    }

    // Отправка письма
    $subject = "Твой код доступа к DAEMON";
    $htmlBody = "
        <h2>Привет, {$username}!</h2>
        <p>Спасибо за регистрацию на DAEMON.</p>
        <p>Твой код подтверждения:</p>
        <h1 style='color:#8b5cf6; font-size:32px; letter-spacing:4px;'>{$code}</h1>
        <p>Введи его на странице регистрации.</p>
    ";

    if (sendEmail($email, $subject, $htmlBody)) {
        echo json_encode(['success' => true, 'message' => 'Код отправлен на почту']);
    } else {
        // Если письмо не ушло, откатываем регистрацию
        $db->exec("DELETE FROM users WHERE username = '$username'");
        http_response_code(500);
        echo json_encode(['error' => 'Не удалось отправить письмо. Попробуй позже.']);
    }

} catch (Exception $e) {
    // Логируем ошибку на сервере и отдаём клиенту безопасный ответ
    error_log("REGISTER ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Внутренняя ошибка сервера']);
}
?>
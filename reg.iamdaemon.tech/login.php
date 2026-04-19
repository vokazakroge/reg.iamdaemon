<?php
require_once __DIR__ . '/config.php';

// Если уже залогинен -> перекидываем на дашборд
if (isLoggedIn()) {
    header('Location: https://reg.iamdaemon.tech/dashboard');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Введите логин и пароль';
    } else {
        $db = getDb();
        // Запрашиваем id, пароль и статус
        $stmt = $db->prepare('SELECT id, password_hash, status FROM users WHERE username = :u');
        $stmt->bindValue(':u', $username, SQLITE3_TEXT);
        $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Пароль верный -> ПРОВЕРЯЕМ СТАТУС
            if ($user['status'] === 'banned') {
                $error = 'Ваш аккаунт заблокирован администрацией';
            } else {
                // Если не забанен -> создаем сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                
                // Успех -> редирект на дашборд
                header('Location: https://reg.iamdaemon.tech/dashboard');
                exit;
            }
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN — DAEMON</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="container">
        <h1>LOGIN</h1>
        <?php if ($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="логин" required autocomplete="off">
            <input type="password" name="password" placeholder="пароль" required>
            <button type="submit">войти</button>
        </form>
        <a href="https://reg.iamdaemon.tech" class="link">нет аккаунта? регистрация</a>
    </div>
</body>
</html>
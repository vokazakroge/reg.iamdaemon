<?php
session_start();
if (isset($_SESSION['username'])) {
    header('Location: /dashboard');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'];

    $dbPath = __DIR__ . '/data/users.db';
    $db = new SQLite3($dbPath);
    $stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE username = :u');
    $stmt->bindValue(':u', $username, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray();

    if ($result && password_verify($password, $result['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        header('Location: /dashboard');
        exit;
    } else {
        $error = 'Неверное имя пользователя или пароль';
    }
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ВХОД — DAEMON</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#0a0a0f; --card:#12121a; --primary:#8b5cf6; --text:#e2e8f0; --muted:#94a3b8; --border:#2a2a3a; --error:#ef4444; --glow:0 0 20px rgba(139,92,246,0.2); }
        *{box-sizing:border-box;margin:0;padding:0} body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}
        .container{width:100%;max-width:360px;background:var(--card);border:1px solid var(--border);border-radius:14px;padding:28px 24px;box-shadow:var(--glow)}
        .logo{font-family:'Orbitron',sans-serif;font-weight:700;font-size:1.2rem;letter-spacing:2px;color:var(--primary);text-align:center;display:block;margin-bottom:18px}
        h1{font-family:'Orbitron',sans-serif;font-size:1.8rem;text-align:center;margin-bottom:20px}
        .form-group{margin-bottom:14px} label{display:block;font-size:0.8rem;color:var(--muted);margin-bottom:6px}
        input{width:100%;padding:12px 14px;background:#0c0c12;border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.95rem} input:focus{outline:none;border-color:var(--primary)}
        button{width:100%;padding:13px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-family:'Orbitron',sans-serif;font-weight:500;cursor:pointer;margin-top:8px}
        .error-msg{color:var(--error);font-size:0.8rem;text-align:center;margin-top:10px;min-height:18px}
        .back-link{text-align:center;margin-top:14px;font-size:0.8rem;color:var(--muted)} .back-link a{color:var(--primary);text-decoration:none}
    </style>
</head>
<body>
    <div class="container">
        <span class="logo">DAEMON</span>
        <h1>ВХОД</h1>
        <form method="POST" action="/login">
            <div class="form-group">
                <label>имя пользователя</label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>пароль</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit">войти</button>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        </form>
        <div class="back-link"><a href="/reg">← вернуться к регистрации</a></div>
    </div>
</body>
</html>

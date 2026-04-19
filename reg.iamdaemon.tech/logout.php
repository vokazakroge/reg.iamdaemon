<?php
session_start();

// Уничтожаем все данные сессии
$_SESSION = [];

// Удаляем куку сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Физически уничтожаем сессию
session_destroy();

// Заголовки, запрещающие кеширование
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Редирект на страницу входа с параметром сброса кеша
header("Location: /login?t=" . time());
exit;
?>

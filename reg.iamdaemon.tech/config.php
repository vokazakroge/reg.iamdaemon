<?php
// === НАСТРОЙКИ СИСТЕМЫ ===

// 1. Безопасность: отключаем вывод ошибок в браузер (в продакшене)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. ГЛОБАЛЬНЫЕ СЕССИИ (КУКИ)
// Это ключевой момент. Точка в начале '.iamdaemon.tech' означает, 
// что кука будет работать на ВСЕХ поддоменах (reg., admin., user. и т.д.)
ini_set('session.cookie_domain', '.iamdaemon.tech');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Работает только по HTTPS
ini_set('session.cookie_samesite', 'Lax');

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. БАЗА ДАННЫХ
define('DB_PATH', __DIR__ . '/data/users.db');

function getDb() {
    static $db = null;
    if ($db === null) {
        $db = new SQLite3(DB_PATH, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $db->busyTimeout(5000);
        // Создаем таблицу, если её нет
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT 'active'
        )");
    }
    return $db;
}

// 4. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Если не залогинен -> редирект на вход
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: https://reg.iamdaemon.tech/login');
        exit;
    }
}

// Проверка админа (замени 'vokazakroge' на свой логин)
function isAdmin() {
    return isLoggedIn() && $_SESSION['username'] === 'vokazakroge';
}

// Требование быть админом
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: https://reg.iamdaemon.tech/login');
        exit;
    }
}
?>
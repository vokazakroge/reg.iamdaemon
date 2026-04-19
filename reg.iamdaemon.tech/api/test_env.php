<?php
require __DIR__ . '/../lib/PHPMailer.php';
require __DIR__ . '/../lib/SMTP.php';
require __DIR__ . '/../lib/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

// Загружаем .env
function loadEnv($path) {
    if (!file_exists($path)) {
        return "❌ Файл .env не найден: $path";
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
    return "✅ .env загружен";
}

echo "<h2>Тест SMTP подключения</h2>";
echo "<pre>";

// Проверяем загрузку .env
echo loadEnv(__DIR__ . '/../.env') . "\n\n";

// Показываем, что загрузилось (пароль скрываем)
echo "📋 Переменные из .env:\n";
echo "SMTP_HOST: " . ($_ENV['SMTP_HOST'] ?? 'не задан') . "\n";
echo "SMTP_PORT: " . ($_ENV['SMTP_PORT'] ?? 'не задан') . "\n";
echo "SMTP_USER: " . ($_ENV['SMTP_USER'] ?? 'не задан') . "\n";
echo "SMTP_PASS: " . (isset($_ENV['SMTP_PASS']) ? substr($_ENV['SMTP_PASS'], 0, 4) . '****' : 'не задан') . "\n\n";

// Пробуем подключиться
$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.mail.ru';
$mail->SMTPAuth   = true;
$mail->Username   = $_ENV['SMTP_USER'] ?? '';
$mail->Password   = $_ENV['SMTP_PASS'] ?? '';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port       = $_ENV['SMTP_PORT'] ?? 465;
$mail->Timeout    = 10;

echo "🔄 Попытка подключения к {$mail->Host}:{$mail->Port}...\n";

try {
    $mail->smtpConnect();
    echo "✅ Успешно подключено к SMTP серверу!\n";
    $mail->smtpClose();
} catch (Exception $e) {
    echo "❌ Ошибка подключения:\n";
    echo $e->getMessage() . "\n";
}

echo "</pre>";
?>
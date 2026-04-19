<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Полная диагностика SMTP</h2>";
echo "<pre>";

// 1. Проверяем файлы
echo "📁 Проверка файлов:\n";
$files = [
    __DIR__ . '/../lib/PHPMailer.php',
    __DIR__ . '/../lib/SMTP.php',
    __DIR__ . '/../lib/Exception.php',
    __DIR__ . '/../.env'
];
foreach ($files as $file) {
    echo "  " . (file_exists($file) ? "✅" : "❌") . " $file\n";
}

// 2. Загружаем .env вручную
echo "\n📋 Чтение .env:\n";
$envContent = file_get_contents(__DIR__ . '/../.env');
$envLines = explode("\n", $envContent);
$env = [];
foreach ($envLines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#') continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
        echo "  $key = " . (strpos($key, 'PASS') !== false ? substr($value, 0, 4) . '****' : $value) . "\n";
        echo "    Длина: " . strlen($value) . " символов\n";
        echo "    HEX: " . bin2hex($value) . "\n";
    }
}

// 3. Проверяем PHPMailer
echo "\n📦 Проверка PHPMailer:\n";
require __DIR__ . '/../lib/PHPMailer.php';
require __DIR__ . '/../lib/SMTP.php';
require __DIR__ . '/../lib/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

echo "  PHPMailer версия: " . PHPMailer::VERSION . "\n";

// 4. Тест подключения
echo "\n🔄 Тест SMTP подключения:\n";
$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = $env['SMTP_HOST'] ?? 'smtp.mail.ru';
$mail->SMTPAuth = true;
$mail->Username = $env['SMTP_USER'] ?? '';
$mail->Password = $env['SMTP_PASS'] ?? '';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = $env['SMTP_PORT'] ?? 465;
$mail->Timeout = 10;
$mail->SMTPAutoTLS = false;

// Принудительно PLAIN
$mail->AuthType = 'PLAIN';

// Включаем отладку
$mail->SMTPDebug = 2;
$mail->Debugoutput = function($str, $level) {
    echo htmlspecialchars($str) . "\n";
};

echo "  Подключение к {$mail->Host}:{$mail->Port}...\n";
try {
    if ($mail->smtpConnect()) {
        echo "  ✅ Успешно подключено!\n";
        $mail->smtpClose();
    } else {
        echo "  ❌ Ошибка подключения\n";
    }
} catch (Exception $e) {
    echo "  ❌ Исключение: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
<?php
/**
 * اتصال قاعدة البيانات — PDO
 */
require_once __DIR__ . '/config.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if (defined('DISPLAY_ERRORS') && DISPLAY_ERRORS) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    }
    die("تعذّر الاتصال بقاعدة البيانات. تحقق من الإعدادات.");
}

<?php
/**
 * إعدادات النظام — نظام أونكس ERP
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'onyx_erp');
define('DB_USER', 'root');
define('DB_PASS', '');

// إعدادات النظام
define('APP_NAME', 'نظام أونكس ERP');
define('APP_VERSION', '8.0.0');

// اكتشاف APP_URL تلقائيًا (يعمل مع أي مسار/بورت)
if (!defined('APP_URL')) {
    // اكتشاف البروتوكول (http أو https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    // اكتشاف المضيف والبورت
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // اكتشاف المسار النسبي للمشروع
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($scriptName));
    // إزالة المسارات الفرعية مثل /includes /setup /operations /inputs /system
    $dir = preg_replace('#/(includes|setup|operations|inputs|system|config|database|assets)$#', '', $dir);
    // إذا كان الجذر فقط
    if ($dir === '.' || $dir === '/' || $dir === '\\') $dir = '';
    define('APP_URL', $protocol . '://' . $host . $dir);
}

// إعدادات الجلسة
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 أيام
define('SESSION_NAME', 'ONYX_SESSION');

// المنطقة الزمنية
date_default_timezone_set('Asia/Aden');

// عرض الأخطاء (عطّله في الإنتاج)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// اللغة
define('APP_LANG', 'ar');
define('APP_DIR', 'rtl');

// ترميز قاعدة البيانات (للاستخدام في PDO)
define('DB_CHARSET', 'utf8mb4');

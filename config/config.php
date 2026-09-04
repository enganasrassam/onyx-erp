<?php
/**
 * إعدادات النظام — نظام أونكس ERP
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'onyx_erp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// إعدادات النظام
define('APP_NAME', 'نظام أونكس ERP');
define('APP_VERSION', '8.0.0');
define('APP_URL', 'http://localhost/onyx');

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

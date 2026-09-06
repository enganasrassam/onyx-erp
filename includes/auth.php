<?php
/**
 * نظام المصادقة — تسجيل الدخول، الجلسات، الصلاحيات
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * تسجيل الدخول
 */
function login(string $username, string $password): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'بيانات الدخول غير صحيحة'];
    }

    // تحديث آخر تسجيل دخول
    $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);

    // إنشاء الجلسة
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    // تسجيل النشاط
    log_activity($user['id'], 'login', 'login', 'تسجيل دخول ناجح');

    return ['success' => true, 'user' => $user];
}

/**
 * تسجيل الخروج
 */
function logout(): void {
    if (!empty($_SESSION['user_id'])) {
        log_activity($_SESSION['user_id'], 'logout', 'login', 'تسجيل خروج');
    }
    $_SESSION = [];
    session_destroy();
}

/**
 * التحقق من تسجيل الدخول
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * المستخدم الحالي
 */
function current_user(): ?array {
    if (!is_logged_in()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'username'  => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role'      => $_SESSION['role'],
    ];
}

/**
 * التحقق من الصلاحية
 */
function has_role(string ...$roles): bool {
    $current = $_SESSION['role'] ?? '';
    return in_array($current, $roles, true);
}

/**
 * طلب تسجيل الدخول
 */
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/**
 * طلب صلاحية
 */
function require_role(string ...$roles): void {
    require_login();
    if (!has_role(...$roles)) {
        http_response_code(403);
        die('غير مصرح لك بالوصول لهذه الصفحة');
    }
}

/**
 * تسجيل النشاط
 */
function log_activity(?int $userId, string $action, ?string $screen = null, ?string $details = null): void {
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, screen, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $screen, $details, $ip]);
    } catch (Exception $e) {
        // تجاهل أخطاء التسجيل
    }
}

/**
 * CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    if (empty($token)) {
        return false;
    }
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (empty($sessionToken)) {
        return false;
    }
    return hash_equals($sessionToken, $token);
}

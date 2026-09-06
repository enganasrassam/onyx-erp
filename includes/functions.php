<?php
/**
 * دوال مساعدة عامة
 */

/**
 * تنظيف المدخلات
 */
function sanitize($value) {
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * تحويل التاريخ للعرض
 */
function format_date(?string $date, string $format = 'd/m/Y'): string {
    if (!$date) return '—';
    $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $ts = strtotime($date);
    if (!$ts) return '—';
    return date('d', $ts) . ' ' . $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

/**
 * تنسيق الأرقام
 */
function format_number($number, int $decimals = 2): string {
    return number_format((float)$number, $decimals, '.', ',');
}

/**
 * تنسيق المبلغ بالعملة
 */
function format_money($amount, $currencyCode = 'YER'): string {
    return format_number($amount) . ' ' . $currencyCode;
}

/**
 * شارة الحالة
 */
function status_badge(string $status): string {
    $map = [
        'active'    => ['نشط', 'bg-emerald-50 text-emerald-700'],
        'inactive'  => ['غير نشط', 'bg-slate-100 text-slate-500'],
        'draft'     => ['مسودة', 'bg-slate-100 text-slate-700'],
        'reviewed'  => ['معتمد', 'bg-blue-50 text-blue-700'],
        'posted'    => ['مرحّل', 'bg-emerald-50 text-emerald-700'],
        'cancelled' => ['ملغي', 'bg-rose-50 text-rose-700'],
        'paid'      => ['مدفوعة', 'bg-blue-50 text-blue-700'],
        'partial'   => ['جزئي', 'bg-amber-50 text-amber-700'],
        'reversed'  => ['عكسي', 'bg-amber-50 text-amber-700'],
        'open'      => ['مفتوحة', 'bg-emerald-50 text-emerald-700'],
        'closed'    => ['مغلقة', 'bg-rose-50 text-rose-700'],
        'locked'    => ['مقفلة', 'bg-slate-100 text-slate-500'],
    ];
    $info = $map[$status] ?? [$status, 'bg-slate-100 text-slate-700'];
    return "<span class=\"badge-status {$info[1]}\">{$info[0]}</span>";
}

/**
 * إعادة توجيه
 */
function redirect(string $url): void {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    // fallback: JavaScript redirect إذا كانت headers مرسلة
    echo '<script>window.location.href="' . addslashes($url) . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></noscript>';
    exit;
}

/**
 * فلاش messages
 */
function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * توليد رقم مستندي تسلسلي
 */
function generate_number(string $prefix, string $table, string $column = 'voucher_number'): string {
    global $pdo;
    $year = date('Y');
    $like = "{$prefix}-{$year}-%";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` LIKE ?");
    $stmt->execute([$like]);
    $count = (int)$stmt->fetchColumn();
    return "{$prefix}-{$year}-" . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

/**
 * تنفيذ استعلام SQL مع bound parameters
 */
function db_query(string $sql, array $params = []): PDOStatement {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_fetch_one(string $sql, array $params = []): ?array {
    $result = db_query($sql, $params)->fetch();
    return $result ?: null;
}

function db_fetch_all(string $sql, array $params = []): array {
    return db_query($sql, $params)->fetchAll();
}

function db_insert(string $table, array $data): int {
    global $pdo;
    $columns = implode(', ', array_map(function($k) { return "`{$k}`"; }, array_keys($data)));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
    db_query($sql, array_values($data));
    return (int)$pdo->lastInsertId();
}

function db_update(string $table, array $data, string $where, array $whereParams = []): int {
    global $pdo;
    $set = implode(', ', array_map(function($k) { return "`{$k}` = ?"; }, array_keys($data)));
    $sql = "UPDATE `{$table}` SET {$set} WHERE {$where}";
    $stmt = db_query($sql, array_merge(array_values($data), $whereParams));
    return $stmt->rowCount();
}

function db_delete(string $table, string $where, array $params = []): void {
    db_query("DELETE FROM `{$table}` WHERE {$where}", $params);
}

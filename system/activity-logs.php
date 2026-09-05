<?php
/**
 * سجل النشاط
 */
require_once __DIR__ . '/../includes/header.php';

$logs = db_fetch_all("SELECT al.*, u.full_name, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 100");
?>

<div class="card mb-4">
    <div class="card-body">
        <h4 class="fw-bold text-slate-800 mb-1">سجل النشاط</h4>
        <p class="text-sm text-slate-500 mb-0">عرض آخر 100 نشاط في النظام</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>الإجراء</th>
                    <th>الشاشة</th>
                    <th>التفاصيل</th>
                    <th>IP</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-slate-400">لا توجد نشاطات</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-indigo-50 text-indigo-600 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px; font-weight: 700;">
                                        <?= mb_substr($log['full_name'] ?? '؟', 0, 1) ?>
                                    </div>
                                    <span><?= sanitize($log['full_name'] ?? 'غير معروف') ?></span>
                                </div>
                            </td>
                            <td><span class="badge bg-blue-50 text-blue-700" style="font-size: 0.7rem;"><?= sanitize($log['action']) ?></span></td>
                            <td class="text-slate-600"><?= sanitize($log['screen'] ?? '—') ?></td>
                            <td class="text-slate-600 text-sm" style="font-size: 0.8rem;"><?= sanitize($log['details'] ?? '—') ?></td>
                            <td class="font-mono text-xs" dir="ltr"><?= sanitize($log['ip_address'] ?? '—') ?></td>
                            <td class="text-xs text-slate-500" dir="ltr"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

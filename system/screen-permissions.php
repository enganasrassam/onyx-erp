<?php
require_once __DIR__ . '/../includes/header.php';
echo '<div class="card mb-4"><div class="card-body"><h4 class="fw-bold text-slate-800 mb-1">صلاحيات الشاشة</h4><p class="text-sm text-slate-500 mb-0">تحكم في وصول المستخدمين للشاشات.</p></div></div>';
$users = db_fetch_all("SELECT id, username, full_name, role FROM users ORDER BY username");
echo '<div class="card"><div class="card-body p-0"><table class="table mb-0"><thead><tr><th>المستخدم</th><th>الدور</th><th class="text-center">صلاحيات</th></tr></thead><tbody>';
foreach ($users as $u) {
    $roleLabels = ['admin'=>'مدير','accountant'=>'محاسب','viewer'=>'مشاهد'];
    echo '<tr><td><code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded">' . sanitize($u['username']) . '</code> ' . sanitize($u['full_name']) . '</td>';
    echo '<td><span class="badge bg-indigo-50 text-indigo-700" style="font-size:0.7rem;">' . ($roleLabels[$u['role']] ?? $u['role']) . '</span></td>';
    echo '<td class="text-center"><span class="badge bg-emerald-50 text-emerald-700" style="font-size:0.7rem;">صلاحية كاملة</span></td></tr>';
}
echo '</tbody></table></div></div>';
require_once __DIR__ . '/../includes/footer.php';

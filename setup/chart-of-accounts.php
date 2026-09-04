<?php
/**
 * الدليل المحاسبي — شجرة هرمية
 */
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$accounts = db_fetch_all("SELECT a.*, p.code as parent_code, p.name_ar as parent_name FROM accounts a LEFT JOIN accounts p ON a.parent_id = p.id ORDER BY a.code");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $children = db_fetch_one("SELECT COUNT(*) as c FROM accounts WHERE parent_id = ?", [$id]);
    if ($children['c'] > 0) {
        flash('error', 'لا يمكن حذف حساب له أبناء');
    } else {
        db_delete('accounts', 'id = ?', [$id]);
        flash('success', 'تم حذف الحساب');
    }
    redirect(APP_URL . '/setup/chart-of-accounts.php');
}

// بناء شجرة
$byParent = [];
foreach ($accounts as $a) {
    $byParent[$a['parent_id'] ?? 0][] = $a;
}

$types = ['asset' => ['الأصول', 'bg-indigo-50 text-indigo-700'], 'liability' => ['الخصوم', 'bg-rose-50 text-rose-700'], 'equity' => ['حقوق ملكية', 'bg-purple-50 text-purple-700'], 'revenue' => ['الإيرادات', 'bg-emerald-50 text-emerald-700'], 'expense' => ['المصروفات', 'bg-amber-50 text-amber-700']];

function render_node($node, $depth, $byParent, $types, $search) {
    $children = $byParent[$node['id']] ?? [];
    $hasChildren = count($children) > 0;
    $type = $types[$node['account_type']] ?? [$node['account_type'], 'bg-slate-100 text-slate-700'];

    echo '<tr class="hover:bg-indigo-50/40 border-b border-slate-100">';
    echo '<td class="px-2 py-2"><div class="d-flex align-items-center gap-1.5" style="padding-right: ' . ($depth * 24) . 'px">';
    if ($hasChildren) {
        echo '<button onclick="toggleNode(' . $node['id'] . ')" class="btn btn-sm p-0"><svg id="chev-' . $node['id'] . '" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(-90deg)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>';
    } else echo '<span style="width:22px"></span>';

    if ($hasChildren) echo '<svg width="16" height="16" fill="#f59e0b" viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>';
    else echo '<svg width="16" height="16" fill="#94a3b8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/></svg>';

    echo '<code dir="ltr" class="text-xs font-mono bg-slate-100 px-1.5 py-0.5 rounded">' . sanitize($node['code']) . '</code>';
    echo '<span class="text-sm text-slate-700">' . sanitize($node['name_ar']) . '</span>';
    if ($node['is_detail']) echo '<span class="badge bg-indigo-50 text-indigo-600" style="font-size:0.65rem;">تفصيلي</span>';
    echo '</div></td>';
    echo '<td class="text-center"><span class="badge ' . $type[1] . '" style="font-size:0.7rem;">' . $type[0] . '</span></td>';
    echo '<td class="text-center text-xs text-slate-500">مستوى ' . $node['level'] . '</td>';
    echo '<td class="text-end font-mono text-xs">' . ($node['opening_balance'] ? number_format($node['opening_balance'], 2) : '—') . '</td>';
    echo '<td class="text-center">' . ($node['active'] ? status_badge('active') : status_badge('inactive')) . '</td>';
    echo '<td class="text-center"><div class="d-flex gap-1 justify-content-center">';
    echo '<a href="account-form.php?id=' . $node['id'] . '" class="btn btn-sm btn-outline-primary"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></a>';
    if (!$hasChildren) echo '<a href="?delete=' . $node['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete(\'حذف الحساب؟\')"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>';
    echo '</div></td></tr>';

    if ($hasChildren) {
        echo '<tbody id="children-' . $node['id'] . '">';
        foreach ($children as $child) render_node($child, $depth + 1, $byParent, $types, $search);
        echo '</tbody>';
    }
}
?>

<div class="card mb-4">
    <div class="card-body d-flex align-items-center justify-content-between gap-3">
        <div>
            <h4 class="fw-bold text-slate-800 mb-1">الدليل المحاسبي</h4>
            <p class="text-sm text-slate-500 mb-0">شجرة الحسابات الهرمية. الحسابات التفصيلية فقط هي التي تقبل القيود.</p>
        </div>
        <a href="account-form.php" class="btn btn-primary d-flex align-items-center gap-1">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>
            إضافة حساب
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>الحساب</th>
                    <th class="text-center" style="width:100px">النوع</th>
                    <th class="text-center" style="width:80px">المستوى</th>
                    <th class="text-end" style="width:120px">رصيد افتتاحي</th>
                    <th class="text-center" style="width:100px">الحالة</th>
                    <th class="text-center" style="width:100px">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($byParent[0] ?? []) as $root) render_node($root, 0, $byParent, $types, $search); ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleNode(id) {
    const tbody = document.getElementById('children-' + id);
    const chev = document.getElementById('chev-' + id);
    if (tbody) {
        const hidden = tbody.style.display === 'none';
        tbody.style.display = hidden ? '' : 'none';
        chev.style.transform = hidden ? 'rotate(-90deg)' : '';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

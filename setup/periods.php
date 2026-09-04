<?php
/**
 * إعداد فترات النظام — السنوات المالية والفترات
 */
require_once __DIR__ . '/../includes/header.php';

if (isset($_GET['close_year'])) {
    $yearId = (int)$_GET['close_year'];
    db_update('fiscal_years', ['closed' => 1], 'id = ?', [$yearId]);
    db_update('fiscal_periods', ['status' => 'closed'], 'fiscal_year_id = ?', [$yearId]);
    flash('success', 'تم إغلاق السنة المالية');
    redirect(APP_URL . '/setup/periods.php');
}

if (isset($_GET['toggle_period'])) {
    $pid = (int)$_GET['toggle_period'];
    $period = db_fetch_one("SELECT * FROM fiscal_periods WHERE id = ?", [$pid]);
    if ($period) {
        $newStatus = $period['status'] === 'open' ? 'closed' : 'open';
        db_update('fiscal_periods', ['status' => $newStatus], 'id = ?', [$pid]);
        flash('success', $newStatus === 'closed' ? 'تم إغلاق الفترة' : 'تم فتح الفترة');
    }
    redirect(APP_URL . '/setup/periods.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $yearName = trim($_POST['year_name'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $periodType = $_POST['period_type'] ?? 'monthly';
    $periodsCount = (int)($_POST['periods_count'] ?? 12);

    if (empty($yearName) || empty($startDate) || empty($endDate)) {
        flash('error', 'الاسم وتاريخ البداية والنهاية مطلوبة');
    } elseif (db_fetch_one("SELECT id FROM fiscal_years WHERE year_name = ?", [$yearName])) {
        flash('error', 'السنة المالية موجودة مسبقًا');
    } else {
        $fyId = db_insert('fiscal_years', [
            'year_name' => $yearName, 'start_date' => $startDate, 'end_date' => $endDate,
            'period_type' => $periodType, 'periods_count' => $periodsCount, 'active' => 1, 'closed' => 0,
        ]);

        $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        $cursor = new DateTime($startDate);
        for ($i = 0; $i < $periodsCount; $i++) {
            $pStart = clone $cursor;
            $pEnd = (clone $cursor)->modify('last day of this month');
            if ($pEnd > new DateTime($endDate)) $pEnd = new DateTime($endDate);
            db_insert('fiscal_periods', [
                'fiscal_year_id' => $fyId, 'period_number' => $i + 1,
                'name' => $months[$cursor->format('n') - 1] . ' ' . $cursor->format('Y'),
                'start_date' => $pStart->format('Y-m-d'), 'end_date' => $pEnd->format('Y-m-d'),
                'status' => 'open',
            ]);
            $cursor->modify('first day of next month');
        }
        flash('success', "تم إنشاء السنة المالية {$yearName} بـ {$periodsCount} فترة");
        redirect(APP_URL . '/setup/periods.php');
    }
}

$years = db_fetch_all("SELECT fy.*, COUNT(fp.id) as period_count FROM fiscal_years fy LEFT JOIN fiscal_periods fp ON fy.id = fp.fiscal_year_id GROUP BY fy.id ORDER BY fy.year_name DESC");
$expanded = $_GET['expand'] ?? ($years[0]['id'] ?? 0);
$periodsByYear = [];
foreach ($years as $y) {
    $periodsByYear[$y['id']] = db_fetch_all("SELECT * FROM fiscal_periods WHERE fiscal_year_id = ? ORDER BY period_number", [$y['id']]);
}
?>

<div class="card mb-4">
    <div class="card-body">
        <h4 class="fw-bold text-slate-800 mb-1">إعداد فترات النظام</h4>
        <p class="text-sm text-slate-500 mb-0">السنوات المالية والفترات المحاسبية. تُستخدم في إقفال الحسابات وتوقيف العمليات شهريًا أو ربعيًا.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">إضافة سنة مالية جديدة</h6></div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-2"><label class="form-label small fw-semibold">اسم السنة *</label><input type="text" name="year_name" class="form-control font-mono" dir="ltr" placeholder="2025" required></div>
                <div class="col-md-3"><label class="form-label small fw-semibold">تاريخ البداية *</label><input type="date" name="start_date" class="form-control" dir="ltr" value="2025-01-01" required></div>
                <div class="col-md-3"><label class="form-label small fw-semibold">تاريخ النهاية *</label><input type="date" name="end_date" class="form-control" dir="ltr" value="2025-12-31" required></div>
                <div class="col-md-2"><label class="form-label small fw-semibold">نوع الفترة</label><select name="period_type" class="form-select"><option value="monthly">شهرية (12)</option><option value="quarterly">ربعية (4)</option><option value="yearly">سنوية (1)</option></select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">إنشاء السنة</button></div>
            </div>
        </form>
    </div>
</div>

<div class="space-y-3">
<?php foreach ($years as $y): ?>
    <div class="card">
        <div class="card-body p-0">
            <button class="btn btn-light w-100 text-end d-flex align-items-center gap-3 p-3 border-0" onclick="togglePeriods(<?= $y['id'] ?>)" id="year-btn-<?= $y['id'] ?>">
                <div class="d-flex align-items-center justify-content-center rounded text-white" style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #7c3aed);">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1 text-end">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold text-slate-800 mb-0">السنة المالية <?= sanitize($y['year_name']) ?></h5>
                        <?php if ($y['closed']): ?><span class="badge bg-rose-50 text-rose-700" style="font-size:0.65rem;">مغلقة</span><?php else: ?><span class="badge bg-emerald-50 text-emerald-700" style="font-size:0.65rem;">نشطة</span><?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 mb-0" dir="ltr"><?= date('d/m/Y', strtotime($y['start_date'])) ?> — <?= date('d/m/Y', strtotime($y['end_date'])) ?> · <?= $y['period_count'] ?> فترة</p>
                </div>
                <?php if (!$y['closed']): ?>
                    <a href="?close_year=<?= $y['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('إغلاق السنة المالية نهائيًا؟')">إقفال السنة</a>
                <?php endif; ?>
                <svg id="chevron-<?= $y['id'] ?>" class="transition-transform" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="<?= $expanded == $y['id'] ? 'transform: rotate(-90deg);' : '' ?>"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div id="periods-<?= $y['id'] ?>" class="border-top bg-slate-50" style="display: <?= $expanded == $y['id'] ? 'block' : 'none' ?>;">
                <div class="row g-2 p-3">
                    <?php foreach ($periodsByYear[$y['id']] as $p): ?>
                        <div class="col-md-3 col-6">
                            <div class="bg-white border rounded p-2 d-flex align-items-center gap-2">
                                <div class="flex-1">
                                    <p class="small fw-medium text-slate-700 mb-0"><?= sanitize($p['name']) ?></p>
                                    <p class="text-[10px] text-slate-400 mb-0" dir="ltr" style="font-size:0.65rem;"><?= date('d/m/Y', strtotime($p['start_date'])) ?> → <?= date('d/m/Y', strtotime($p['end_date'])) ?></p>
                                </div>
                                <a href="?toggle_period=<?= $p['id'] ?>" class="btn btn-sm <?= $p['status'] === 'open' ? 'btn-outline-success' : 'btn-outline-danger' ?>" title="<?= $p['status'] === 'open' ? 'فتح/إغلاق' : 'فتح/إغلاق' ?>" <?= $y['closed'] ? 'disabled' : '' ?>>
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><?= $p['status'] === 'open' ? '<path d="M10 2a5 5 0 00-5 5v3H4a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V7a5 5 0 00-5-5zm-3 8V7a3 3 0 016 0v3H7z"/>' : '<path d="M5 9V7a5 5 0 0110 0v2H5zm10 2H5v6h10v-6z"/>' ?></svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
function togglePeriods(id) {
    const div = document.getElementById('periods-' + id);
    const chev = document.getElementById('chevron-' + id);
    div.style.display = div.style.display === 'none' ? 'block' : 'none';
    chev.style.transform = div.style.display === 'none' ? '' : 'rotate(-90deg)';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

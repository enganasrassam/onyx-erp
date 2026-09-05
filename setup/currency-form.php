<?php
/**
 * نموذج إضافة/تعديل عملة
 */
require_once __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$currency = $id ? db_fetch_one("SELECT * FROM currencies WHERE id = ?", [$id]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $code = trim($_POST['code'] ?? '');
    $nameAr = trim($_POST['name_ar'] ?? '');
    $nameEn = trim($_POST['name_en'] ?? '');
    $symbol = trim($_POST['symbol'] ?? '');
    $rate = (float)($_POST['exchange_rate'] ?? 1);
    $isBase = isset($_POST['is_base']) ? 1 : 0;
    $isForeign = isset($_POST['is_foreign']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;

    if (empty($code) || empty($nameAr)) {
        flash('error', 'الرمز والاسم مطلوبان');
    } else {
        $data = [
            'code' => $code, 'name_ar' => $nameAr, 'name_en' => $nameEn ?: null,
            'symbol' => $symbol ?: null, 'exchange_rate' => $isBase ? 1 : $rate,
            'is_base' => $isBase, 'is_foreign' => $isForeign, 'active' => $active,
        ];
        if ($isBase) {
            db_update('currencies', ['is_base' => 0], '1=1');
            $data['is_foreign'] = 0;
        }
        if ($id) {
            db_update('currencies', $data, 'id = ?', [$id]);
            flash('success', 'تم تحديث العملة');
        } else {
            db_insert('currencies', $data);
            flash('success', 'تمت إضافة العملة');
        }
        redirect(APP_URL . '/setup/currencies.php');
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= $currency ? 'تعديل عملة' : 'إضافة عملة جديدة' ?></h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">رمز العملة *</label>
                    <input type="text" name="code" class="form-control font-mono" value="<?= sanitize($currency['code'] ?? '') ?>" dir="ltr" required <?= $currency ? 'readonly' : '' ?> placeholder="USD">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">الرمز ($)</label>
                    <input type="text" name="symbol" class="form-control" value="<?= sanitize($currency['symbol'] ?? '') ?>" dir="ltr" placeholder="$">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">الاسم بالعربية *</label>
                    <input type="text" name="name_ar" class="form-control" value="<?= sanitize($currency['name_ar'] ?? '') ?>" required placeholder="دولار أمريكي">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">الاسم بالإنجليزية</label>
                    <input type="text" name="name_en" class="form-control" value="<?= sanitize($currency['name_en'] ?? '') ?>" dir="ltr" placeholder="US Dollar">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">سعر الصرف مقابل العملة الأساسية</label>
                    <input type="number" step="0.000001" name="exchange_rate" class="form-control font-mono" value="<?= $currency['exchange_rate'] ?? 1 ?>" dir="ltr" <?= !empty($currency['is_base']) ? 'readonly' : '' ?>>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-4">
                    <div class="form-check">
                        <input type="checkbox" name="is_base" class="form-check-input" id="isBase" <?= !empty($currency['is_base']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isBase">عملة أساسية</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_foreign" class="form-check-input" id="isForeign" <?= !empty($currency['is_foreign']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isForeign">عملة أجنبية</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="active" class="form-check-input" id="active" <?= !isset($currency) || $currency['active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">نشطة</label>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2 justify-content-end">
                <a href="currencies.php" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

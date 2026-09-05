<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'intermediary_accounts', 'listUrl' => 'inputs/intermediary-accounts.php',
    'addTitle' => 'إضافة حساب وسيط', 'editTitle' => 'تعديل حساب وسيط',
    'fields' => [
        ['name' => 'code', 'label' => 'الرمز', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
        ['name' => 'type', 'label' => 'النوع', 'type' => 'select', 'required' => true, 'options' => ['currency_diff'=>'فروق عملة','missing_items'=>'أصناف مفقودة','payment_notes'=>'أوراق دفع','receipt_notes'=>'أوراق قبض','fraction_diff'=>'فروق كسور','cost_diff'=>'فروق تكلفة','commission'=>'عمولة المندوبين','commission_num'=>'عمولة رقم','other'=>'أخرى']],
        ['name' => 'linked_account_id', 'label' => 'الحساب المرتبط', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM accounts WHERE is_detail=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'description_ar', 'label' => 'الوصف', 'type' => 'text', 'col' => 12],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'accounts', 'listUrl' => 'setup/chart-of-accounts.php',
    'addTitle' => 'إضافة حساب', 'editTitle' => 'تعديل حساب',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز الحساب', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'الاسم بالعربية', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم بالإنجليزية', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'parent_id', 'label' => 'الحساب الأب', 'type' => 'select', 'options' => ['' => '— بدون —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM accounts ORDER BY code"), 'name', 'id')],
        ['name' => 'account_type', 'label' => 'نوع الحساب', 'type' => 'select', 'required' => true, 'options' => ['asset' => 'أصول', 'liability' => 'خصوم', 'equity' => 'حقوق ملكية', 'revenue' => 'إيرادات', 'expense' => 'مصروفات']],
        ['name' => 'level', 'label' => 'المستوى', 'type' => 'number', 'dir' => 'ltr', 'default' => 1],
        ['name' => 'opening_balance', 'label' => 'الرصيد الافتتاحي', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'is_detail', 'label' => 'حساب تفصيلي (يقبل القيود)', 'type' => 'checkbox', 'default' => 0],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'cash_boxes', 'listUrl' => 'inputs/cash-boxes.php',
    'addTitle' => 'إضافة صندوق', 'editTitle' => 'تعديل صندوق',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز الصندوق', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'اسم الصندوق', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'operation_type', 'label' => 'نوع العملية', 'type' => 'select', 'options' => ['both' => 'قبض وصرف', 'receipt' => 'قبض فقط', 'payment' => 'صرف فقط']],
        ['name' => 'branch_id', 'label' => 'الفرع', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, name_ar FROM branches ORDER BY name_ar"), 'name_ar', 'id')],
        ['name' => 'account_id', 'label' => 'حساب الصندوق', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM accounts WHERE is_detail=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'sequence', 'label' => 'التسلسل', 'type' => 'number', 'dir' => 'ltr', 'default' => 1],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

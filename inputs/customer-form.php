<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'customers', 'listUrl' => 'inputs/customers.php',
    'addTitle' => 'إضافة عميل', 'editTitle' => 'تعديل عميل',
    'fields' => [
        ['name' => 'code', 'label' => 'رمز العميل', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'name_ar', 'label' => 'اسم العميل', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'group_id', 'label' => 'مجموعة العملاء', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM customer_groups ORDER BY code"), 'name', 'id')],
        ['name' => 'tax_number', 'label' => 'الرقم الضريبي', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'phone', 'label' => 'الهاتف', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'contact_person', 'label' => 'شخص التواصل', 'type' => 'text'],
        ['name' => 'address', 'label' => 'العنوان', 'type' => 'text', 'col' => 12],
        ['name' => 'opening_balance', 'label' => 'الرصيد الافتتاحي', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'credit_limit', 'label' => 'حد الائتمان', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

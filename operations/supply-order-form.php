<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'inventory_orders',
    'listUrl' => 'operations/supply-orders.php',
    'addTitle' => 'إضافة أمر توريد',
    'editTitle' => 'تعديل أمر توريد',
    'fields' => [
        ['name' => 'warehouse_id', 'label' => 'المخزن', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM warehouses WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'order_date', 'label' => 'التاريخ', 'type' => 'date', 'dir' => 'ltr', 'default' => date('Y-m-d')],
        ['name' => 'party_type', 'label' => 'نوع الطرف', 'type' => 'select', 'options' => ['' => '— اختر —', 'supplier' => 'مورد', 'customer' => 'عميل', 'account' => 'حساب']],
        ['name' => 'party_name', 'label' => 'اسم الطرف', 'type' => 'text'],
        ['name' => 'notes', 'label' => 'ملاحظات', 'type' => 'text', 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

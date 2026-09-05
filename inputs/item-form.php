<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'items', 'listUrl' => 'inputs/items.php',
    'addTitle' => 'إضافة صنف', 'editTitle' => 'تعديل صنف',
    'fields' => [
        ['name' => 'code', 'label' => 'كود الصنف', 'type' => 'text', 'required' => true, 'dir' => 'ltr', 'readonly' => isset($_GET['id'])],
        ['name' => 'barcode', 'label' => 'الباركود', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'name_ar', 'label' => 'اسم الصنف بالعربية', 'type' => 'text', 'required' => true],
        ['name' => 'name_en', 'label' => 'الاسم بالإنجليزية', 'type' => 'text', 'dir' => 'ltr'],
        ['name' => 'main_group_id', 'label' => 'المجموعة الرئيسية', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM inventory_main_groups ORDER BY code"), 'name', 'id')],
        ['name' => 'base_unit_id', 'label' => 'الوحدة الأساسية', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, name_ar FROM units ORDER BY name_ar"), 'name_ar', 'id')],
        ['name' => 'item_type', 'label' => 'نوع الصنف', 'type' => 'select', 'options' => ['simple' => 'بسيط', 'composite' => 'مركب (BOM)', 'attached' => 'ملحق']],
        ['name' => 'inventory_method', 'label' => 'طريقة التقييم', 'type' => 'select', 'options' => ['weighted_average' => 'متوسط مرجح', 'fifo' => 'FIFO', 'lifo' => 'LIFO']],
        ['name' => 'description', 'label' => 'الوصف', 'type' => 'text', 'col' => 12],
        ['name' => 'active', 'label' => 'نشط', 'type' => 'checkbox', 'default' => 1, 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

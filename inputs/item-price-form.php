<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'item_prices',
    'listUrl' => 'inputs/item-prices.php',
    'addTitle' => 'إضافة تسعيرة',
    'editTitle' => 'تعديل تسعيرة',
    'fields' => [
        ['name' => 'item_id', 'label' => 'الصنف', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM items WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'price_level_id', 'label' => 'مستوى التسعيرة', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT('مستوى ', level, ' — ', name_ar) as name FROM price_levels ORDER BY level"), 'name', 'id')],
        ['name' => 'unit_id', 'label' => 'الوحدة', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, name_ar FROM units WHERE active=1 ORDER BY name_ar"), 'name_ar', 'id')],
        ['name' => 'currency_id', 'label' => 'العملة', 'type' => 'select', 'options' => ['' => '— اختر —'] + array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM currencies WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'purchase_price', 'label' => 'سعر الشراء', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'wholesale_price', 'label' => 'سعر الجملة', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'retail_price', 'label' => 'سعر التجزئة', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'min_price', 'label' => 'الحد الأدنى للسعر', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'discount_pct', 'label' => 'نسبة الخصم %', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

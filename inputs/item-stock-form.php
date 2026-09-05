<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'item_stocks',
    'listUrl' => 'inputs/item-stock.php',
    'addTitle' => 'إضافة مخزون افتتاحي',
    'editTitle' => 'تعديل مخزون افتتاحي',
    'fields' => [
        ['name' => 'item_id', 'label' => 'الصنف', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM items WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'warehouse_id', 'label' => 'المخزن', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM warehouses WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'quantity', 'label' => 'الكمية', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'unit_cost', 'label' => 'تكلفة الوحدة', 'type' => 'number', 'dir' => 'ltr', 'default' => 0],
        ['name' => 'type', 'label' => 'النوع', 'type' => 'select', 'options' => ['opening' => 'افتتاحي', 'current' => 'حالي']],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

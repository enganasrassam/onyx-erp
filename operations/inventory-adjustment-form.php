<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'inventory_adjustments',
    'listUrl' => 'operations/inventory-adjustments.php',
    'addTitle' => 'إضافة تسوية مخزون',
    'editTitle' => 'تعديل تسوية مخزون',
    'fields' => [
        ['name' => 'warehouse_id', 'label' => 'المخزن', 'type' => 'select', 'required' => true, 'options' => array_column(db_fetch_all("SELECT id, CONCAT(code, ' — ', name_ar) as name FROM warehouses WHERE active=1 ORDER BY code"), 'name', 'id')],
        ['name' => 'adjustment_date', 'label' => 'التاريخ', 'type' => 'date', 'dir' => 'ltr', 'default' => date('Y-m-d')],
        ['name' => 'type', 'label' => 'نوع التسوية', 'type' => 'select', 'options' => ['increase' => 'زيادة', 'decrease' => 'نقص']],
        ['name' => 'notes', 'label' => 'ملاحظات', 'type' => 'text', 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

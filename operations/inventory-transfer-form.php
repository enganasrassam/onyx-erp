<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_form([
    'table' => 'inventory_transfers',
    'listUrl' => 'operations/inventory-transfers.php',
    'addTitle' => 'إضافة تحويل مخزني',
    'editTitle' => 'تعديل تحويل مخزني',
    'fields' => [
        ['name' => 'transfer_date', 'label' => 'التاريخ', 'type' => 'date', 'dir' => 'ltr', 'default' => date('Y-m-d')],
        ['name' => 'type', 'label' => 'النوع', 'type' => 'select', 'options' => ['transfer' => 'تحويل', 'receipt' => 'استلام']],
        ['name' => 'notes', 'label' => 'ملاحظات', 'type' => 'text', 'col' => 12],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

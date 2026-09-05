<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'inventory_transfers',
    'title' => 'التحويلات المخزنية',
    'description' => 'تحويل الأصناف بين المخازن. عند الترحيل، تُخصم من المصدر وتُضاف للوجهة.',
    'listUrl' => 'operations/inventory-transfers.php',
    'addUrl' => 'inventory-transfer-form.php',
    'editUrl' => 'inventory-transfer-form.php',
    'searchFields' => ['transfer_number'],
    'orderBy' => 'transfer_date DESC',
    'columns' => [
        ['key' => 'transfer_number', 'label' => 'رقم التحويل', 'code' => true],
        ['key' => 'transfer_date', 'label' => 'التاريخ', 'render' => function($i) { return date('d/m/Y', strtotime($i['transfer_date'])); }],
        ['key' => 'total_quantity', 'label' => 'الكمية', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_quantity']) . '</code>'; }],
        ['key' => 'total_cost', 'label' => 'التكلفة', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_cost'], 2) . '</code>'; }],
        ['key' => 'status', 'label' => 'الحالة', 'align' => 'text-center', 'render' => function($i) { return status_badge($i['status']); }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

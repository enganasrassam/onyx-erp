<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'inventory_orders',
    'title' => 'أوامر الصرف المخزني',
    'description' => 'أوامر صرف البضاعة من المخازن. عند الترحيل، تُخصم الكميات من المخزون.',
    'listUrl' => 'operations/discharge-orders.php',
    'addUrl' => 'discharge-order-form.php',
    'editUrl' => 'discharge-order-form.php',
    'searchFields' => ['order_number'],
    'orderBy' => 'order_date DESC',
    'columns' => [
        ['key' => 'order_number', 'label' => 'رقم الأمر', 'code' => true],
        ['key' => 'order_date', 'label' => 'التاريخ', 'render' => function($i) { return date('d/m/Y', strtotime($i['order_date'])); }],
        ['key' => 'party_name', 'label' => 'الطرف', 'render' => function($i) { return $i['party_name'] ?? '—'; }],
        ['key' => 'total_quantity', 'label' => 'الكمية', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_quantity']) . '</code>'; }],
        ['key' => 'total_cost', 'label' => 'التكلفة', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_cost'], 2) . '</code>'; }],
        ['key' => 'status', 'label' => 'الحالة', 'align' => 'text-center', 'render' => function($i) { return status_badge($i['status']); }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

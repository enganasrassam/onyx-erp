<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'inventory_adjustments',
    'title' => 'تسوية المخزون',
    'description' => 'تسوية الفروقات بين الكمية الدفترية والفعلية (جرد).',
    'listUrl' => 'operations/inventory-adjustments.php',
    'addUrl' => 'inventory-adjustment-form.php',
    'editUrl' => 'inventory-adjustment-form.php',
    'searchFields' => ['adjustment_number'],
    'orderBy' => 'adjustment_date DESC',
    'columns' => [
        ['key' => 'adjustment_number', 'label' => 'رقم التسوية', 'code' => true],
        ['key' => 'adjustment_date', 'label' => 'التاريخ', 'render' => function($i) { return date('d/m/Y', strtotime($i['adjustment_date'])); }],
        ['key' => 'type', 'label' => 'النوع', 'align' => 'text-center', 'render' => function($i) {
            return '<span class="badge ' . ($i['type']==='increase'?'bg-emerald-50 text-emerald-700':'bg-rose-50 text-rose-700') . '" style="font-size:0.7rem;">' . ($i['type']==='increase'?'زيادة':'نقص') . '</span>';
        }],
        ['key' => 'total_quantity', 'label' => 'الكمية', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_quantity']) . '</code>'; }],
        ['key' => 'total_cost', 'label' => 'القيمة', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_cost'], 2) . '</code>'; }],
        ['key' => 'status', 'label' => 'الحالة', 'align' => 'text-center', 'render' => function($i) { return status_badge($i['status']); }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

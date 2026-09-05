<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'item_stocks',
    'title' => 'المخزون الافتتاحي',
    'description' => 'إدخال كميات وتكلفة الأصناف في المخازن عند بدء النشاط.',
    'listUrl' => 'inputs/item-stock.php',
    'addUrl' => 'item-stock-form.php',
    'editUrl' => 'item-stock-form.php',
    'searchFields' => [],
    'orderBy' => 'id ASC',
    'columns' => [
        ['key' => 'item_id', 'label' => 'الصنف', 'render' => function($i) {
            $item = db_fetch_one("SELECT code, name_ar FROM items WHERE id = ?", [$i['item_id']]);
            return $item ? '<code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded">' . $item['code'] . '</code> ' . $item['name_ar'] : '—';
        }],
        ['key' => 'warehouse_id', 'label' => 'المخزن', 'render' => function($i) {
            $wh = db_fetch_one("SELECT code, name_ar FROM warehouses WHERE id = ?", [$i['warehouse_id']]);
            return $wh ? '<code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded">' . $wh['code'] . '</code> ' . $wh['name_ar'] : '—';
        }],
        ['key' => 'quantity', 'label' => 'الكمية', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['quantity']) . '</code>'; }],
        ['key' => 'total_cost', 'label' => 'الإجمالي', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr" class="text-emerald-700">' . number_format($i['total_cost'], 2) . '</code>'; }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

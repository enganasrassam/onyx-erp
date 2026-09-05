<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'item_prices',
    'title' => 'تسعيرة الأصناف',
    'description' => 'تحديد أسعار الأصناف حسب المستوى والوحدة والعملة.',
    'listUrl' => 'inputs/item-prices.php',
    'addUrl' => 'item-price-form.php',
    'editUrl' => 'item-price-form.php',
    'searchFields' => [],
    'orderBy' => 'id ASC',
    'columns' => [
        ['key' => 'item_id', 'label' => 'الصنف', 'render' => function($i) {
            $item = db_fetch_one("SELECT code, name_ar FROM items WHERE id = ?", [$i['item_id']]);
            return $item ? '<code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded">' . $item['code'] . '</code> ' . $item['name_ar'] : '—';
        }],
        ['key' => 'purchase_price', 'label' => 'سعر الشراء', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['purchase_price'], 2) . '</code>'; }],
        ['key' => 'wholesale_price', 'label' => 'سعر الجملة', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['wholesale_price'], 2) . '</code>'; }],
        ['key' => 'retail_price', 'label' => 'سعر التجزئة', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['retail_price'], 2) . '</code>'; }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

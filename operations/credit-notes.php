<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'daily_entries',
    'title' => 'الإشعارات الدائنة',
    'description' => 'الإشعارات الدائنة — تعرض القيود اليومية المرتبطة بهذا النوع',
    'listUrl' => 'operations/credit-notes.php',
    'addUrl' => '#',
    'editUrl' => '#',
    'searchFields' => ['entry_number'],
    'orderBy' => 'entry_date DESC',
    'columns' => [
        ['key' => 'entry_number', 'label' => 'الرقم', 'code' => true],
        ['key' => 'entry_date', 'label' => 'التاريخ', 'render' => function($i) { return date('d/m/Y', strtotime($i['entry_date'])); }],
        ['key' => 'description', 'label' => 'البيان', 'render' => function($i) { return $i['description'] ?? '—'; }],
        ['key' => 'total_debit', 'label' => 'مدين', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_debit'], 2) . '</code>'; }],
        ['key' => 'total_credit', 'label' => 'دائن', 'align' => 'text-end', 'render' => function($i) { return '<code dir="ltr">' . number_format($i['total_credit'], 2) . '</code>'; }],
        ['key' => 'status', 'label' => 'الحالة', 'align' => 'text-center', 'render' => function($i) { return status_badge($i['status']); }],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

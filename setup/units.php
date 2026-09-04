<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'units', 'title' => 'وحدات القياس', 'description' => 'وحدات قياس الأصناف المخزنية (حبة، باكت، كرتون...).',
    'listUrl' => 'setup/units.php', 'addUrl' => 'unit-form.php', 'editUrl' => 'unit-form.php',
    'searchFields' => ['name_ar', 'name_en'], 'orderBy' => 'name_ar ASC',
    'columns' => [
        ['key' => 'name_ar', 'label' => 'الاسم (عربي)'],
        ['key' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['name_en'] ?? '—')],
        ['key' => 'factor', 'label' => 'معامل التحويل', 'align' => 'text-end', 'render' => fn($i) => '<code dir="ltr">' . number_format($i['factor'], 4) . '</code>'],
        ['key' => 'base_unit', 'label' => 'وحدة أساسية', 'align' => 'text-center', 'render' => fn($i) => $i['base_unit'] ? '<span class="badge bg-emerald-50 text-emerald-700" style="font-size:0.7rem;">نعم</span>' : '—'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'discharge_types', 'title' => 'أنواع الصرف', 'description' => 'أنواع الصرف المخزني (صرف مخزني، نقدي، آجل، كمية).',
    'listUrl' => 'setup/discharge-types.php', 'addUrl' => 'discharge-type-form.php', 'editUrl' => 'discharge-type-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم (عربي)'],
        ['key' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['name_en'] ?? '—')],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

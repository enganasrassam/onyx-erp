<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'cost_centers', 'title' => 'مراكز التكلفة', 'description' => 'مراكز التكلفة لتوزيع المصروفات والإيرادات على الأقسام.',
    'listUrl' => 'setup/cost-centers.php', 'addUrl' => 'cost-center-form.php', 'editUrl' => 'cost-center-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'رمز المركز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'الاسم (عربي)'],
        ['key' => 'name_en', 'label' => 'الاسم (إنجليزي)', 'align' => 'text-end', 'render' => fn($i) => sanitize($i['name_en'] ?? '—')],
        ['key' => 'level', 'label' => 'المستوى', 'align' => 'text-center', 'render' => fn($i) => '<span class="badge bg-slate-100 text-slate-700">' . $i['level'] . '</span>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

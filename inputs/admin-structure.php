<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud-helper.php';
render_crud_page([
    'table' => 'admin_structures', 'title' => 'الهيكل الإداري', 'description' => 'الهرم الإداري للشركة. يُستخدم في تنظيم التقارير وتوزيع الصلاحيات.',
    'listUrl' => 'inputs/admin-structure.php', 'addUrl' => 'admin-structure-form.php', 'editUrl' => 'admin-structure-form.php',
    'searchFields' => ['code', 'name_ar'], 'orderBy' => 'code ASC',
    'columns' => [
        ['key' => 'code', 'label' => 'الرمز', 'code' => true],
        ['key' => 'name_ar', 'label' => 'المسمى الإداري'],
        ['key' => 'level', 'label' => 'المستوى', 'align' => 'text-center', 'render' => fn($i) => '<span class="badge bg-slate-100 text-slate-700">' . $i['level'] . '</span>'],
        ['key' => 'active', 'label' => 'الحالة', 'align' => 'text-center', 'render' => fn($i) => $i['active'] ? status_badge('active') : status_badge('inactive')],
    ],
]);
require_once __DIR__ . '/../includes/footer.php';

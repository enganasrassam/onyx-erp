<?php
/**
 * توليد شجرة القائمة الجانبية — مطابقة بالكامل لنظام أونكس ERP
 * بناءً على فهرس كتاب الاونكس للطالب 2024
 */
function render_sidebar_menu(string $active = ''): string {
    $sections = get_menu_sections();
    $html = '';
    foreach ($sections as $section) {
        $html .= '<div class="mb-1">';
        $html .= '<div class="px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider text-sidebar-foreground/50 d-flex align-items-center gap-2">';
        $html .= render_icon($section['icon'], 'h-3.5 w-3.5');
        $html .= htmlspecialchars($section['label']);
        $html .= '</div><ul class="sidebar-list">';

        foreach ($section['items'] as $item) {
            $html .= render_menu_item($item, $active, 0);
        }

        $html .= '</ul></div>';
    }
    return $html;
}

function render_menu_item(array $item, string $active, int $depth): string {
    $hasChildren = !empty($item['children']);
    $isDisabled = !empty($item['disabled']);

    // Build URL from url_path
    $url = '#';
    if (!empty($item['url_path'])) {
        $url = APP_URL . '/' . $item['url_path'];
    }

    $paddingRight = $depth * 16 + 12;
    $isActive = ($item['id'] ?? '') === $active;

    $html = '<li>';

    if ($hasChildren) {
        $html .= '<button type="button" class="sidebar-item sidebar-parent" onclick="toggleSubmenu(this)">';
        $html .= '<span class="sidebar-indent" style="width:' . $paddingRight . 'px"></span>';
        if (!empty($item['icon'])) {
            $html .= render_icon($item['icon'], 'h-4 w-4 shrink-0');
        }
        $html .= '<span class="sidebar-label">' . htmlspecialchars($item['label']) . '</span>';
        $html .= '<svg class="sidebar-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>';
        $html .= '</button>';
        $html .= '<ul class="sidebar-submenu">';
        foreach ($item['children'] as $child) {
            $html .= render_menu_item($child, $active, $depth + 1);
        }
        $html .= '</ul>';
    } else {
        $classes = 'sidebar-item';
        if ($isActive) $classes .= ' sidebar-item-active';
        if ($isDisabled) $classes .= ' sidebar-item-disabled';

        $html .= '<a href="' . $url . '" class="' . $classes . '"' . ($isDisabled ? ' onclick="return false;"' : '') . '>';
        $html .= '<span class="sidebar-indent" style="width:' . $paddingRight . 'px"></span>';
        if (!empty($item['icon'])) {
            $html .= render_icon($item['icon'], 'h-4 w-4 shrink-0');
        } else {
            $html .= '<svg width="6" height="6" viewBox="0 0 6 6" class="shrink-0 opacity-40"><circle cx="3" cy="3" r="2.5" fill="currentColor"/></svg>';
        }
        $html .= '<span class="sidebar-label">' . htmlspecialchars($item['label']) . '</span>';
        if (!empty($item['badge'])) {
            $badgeClass = $item['badge'] === 'قريبًا' ? 'sidebar-badge-soon' : 'sidebar-badge';
            $html .= '<span class="' . $badgeClass . '">' . htmlspecialchars($item['badge']) . '</span>';
        }
        $html .= '</a>';
    }

    $html .= '</li>';
    return $html;
}

/**
 * أيقونات SVG
 */
function render_icon(string $name, string $classes = 'h-4 w-4'): string {
    $icons = [
        'dashboard' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
        'settings' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'database' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>',
        'file-text' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'users' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'boxes' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        'truck' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>',
        'cart' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'book' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'branch' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'calendar' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        'coins' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'globe' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'building' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'arrow-left-right' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l-4 4 4 4M4 11h12m4-4l4 4-4 4M20 13H8"/></svg>',
        'package' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        'wallet' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9"/></svg>',
        'landmark' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m4-11v11m4-11v11m4-11v11m4-11v11"/></svg>',
        'lock' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
        'clipboard' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
        'layers' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>',
        'tag' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
        'receipt' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-7-3-7 3V5a2 2 0 012-2h10a2 2 0 012 2v16z"/></svg>',
        'swap' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5"/></svg>',
        'edit-check' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
        'key' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a4 4 0 11-8 0 4 4 0 018 0zM3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>',
        'activity' => '<svg class="' . $classes . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
    ];
    return $icons[$name] ?? '<span class="' . $classes . '">●</span>';
}

/**
 * هيكل القائمة الجانبية — مطابق لفهرس كتاب الاونكس 2024
 */
function get_menu_sections(): array {
    return [
        // ====== الرئيسية ======
        [
            'id' => 'home', 'label' => 'الرئيسية', 'icon' => 'dashboard',
            'items' => [
                ['id' => 'dashboard', 'label' => 'لوحة التحكم', 'icon' => 'dashboard', 'url_path' => 'dashboard.php'],
            ],
        ],

        // ====== التهيئة (المرحلة 1) ======
        [
            'id' => 'setup', 'label' => 'التهيئة', 'icon' => 'settings',
            'items' => [
                [
                    'label' => 'تهيئة النظام', 'icon' => 'settings',
                    'children' => [
                        ['id' => 'setup-system-variables', 'label' => 'المتغيرات العامة', 'url_path' => 'setup/system-variables.php'],
                        ['id' => 'setup-periods', 'label' => 'إعداد فترات النظام', 'url_path' => 'setup/periods.php'],
                        ['id' => 'setup-currencies', 'label' => 'تهيئة العملات', 'url_path' => 'setup/currencies.php'],
                    ],
                ],
                [
                    'label' => 'الأقاليم والدول', 'icon' => 'globe',
                    'children' => [
                        ['id' => 'setup-countries', 'label' => 'بيانات الدول', 'url_path' => 'setup/countries.php'],
                        ['id' => 'setup-governorates', 'label' => 'بيانات المحافظات', 'url_path' => 'setup/governorates.php'],
                        ['id' => 'setup-cities', 'label' => 'بيانات المدن', 'url_path' => 'setup/cities.php'],
                    ],
                ],
                [
                    'label' => 'بيانات الشركة', 'icon' => 'building',
                    'children' => [
                        ['id' => 'setup-company', 'label' => 'بيانات الشركة', 'url_path' => 'setup/company.php'],
                        ['id' => 'setup-branches', 'label' => 'بيانات الفروع', 'url_path' => 'setup/branches.php'],
                    ],
                ],
                ['id' => 'setup-chart-of-accounts', 'label' => 'الدليل المحاسبي', 'icon' => 'book', 'url_path' => 'setup/chart-of-accounts.php'],
                ['id' => 'setup-cost-centers', 'label' => 'مراكز التكلفة', 'icon' => 'branch', 'url_path' => 'setup/cost-centers.php'],
                [
                    'label' => 'تهيئة أنظمة الحسابات', 'icon' => 'database',
                    'children' => [
                        ['id' => 'setup-accounts-variables', 'label' => 'متغيرات الأستاذ العام', 'url_path' => 'setup/system-variables.php', 'badge' => 'قريبًا'],
                    ],
                ],
                [
                    'label' => 'تهيئة أنظمة المخازن', 'icon' => 'boxes',
                    'children' => [
                        ['id' => 'setup-inventory-variables', 'label' => 'متغيرات المخزون', 'url_path' => 'setup/system-variables.php', 'badge' => 'قريبًا'],
                        ['id' => 'setup-units', 'label' => 'وحدات القياس', 'url_path' => 'setup/units.php'],
                        ['id' => 'setup-price-levels', 'label' => 'مستويات التسعيرة', 'url_path' => 'setup/price-levels.php'],
                        ['id' => 'setup-supply-types', 'label' => 'أنواع التوريد', 'url_path' => 'setup/supply-types.php'],
                        ['id' => 'setup-discharge-types', 'label' => 'أنواع الصرف', 'url_path' => 'setup/discharge-types.php'],
                        ['id' => 'setup-transfer-types', 'label' => 'أنواع التحويل', 'url_path' => 'setup/transfer-types.php'],
                    ],
                ],
                [
                    'label' => 'تهيئة أنظمة الموردين', 'icon' => 'truck',
                    'children' => [
                        ['id' => 'setup-supplier-groups', 'label' => 'مجموعات الموردين', 'url_path' => 'setup/supplier-groups.php'],
                    ],
                ],
                [
                    'label' => 'تهيئة أنظمة العملاء', 'icon' => 'users',
                    'children' => [
                        ['id' => 'setup-customer-groups', 'label' => 'مجموعات العملاء', 'url_path' => 'setup/customer-groups.php'],
                    ],
                ],
            ],
        ],

        // ====== المدخلات (المرحلة 2) ======
        [
            'id' => 'inputs', 'label' => 'المدخلات', 'icon' => 'file-text',
            'items' => [
                [
                    'label' => 'مدخلات عامة', 'icon' => 'file-text',
                    'children' => [
                        ['id' => 'inputs-chart-of-accounts', 'label' => 'الدليل المحاسبي', 'url_path' => 'setup/chart-of-accounts.php'],
                        ['id' => 'inputs-admin-structure', 'label' => 'الهيكل الإداري', 'url_path' => 'inputs/admin-structure.php'],
                        ['id' => 'inputs-employees', 'label' => 'بيانات الموظفين', 'url_path' => 'inputs/employees.php'],
                        ['id' => 'inputs-intermediary-accounts', 'label' => 'الحسابات الوسيطة', 'url_path' => 'inputs/intermediary-accounts.php'],
                        ['id' => 'inputs-cost-centers', 'label' => 'مراكز التكلفة', 'url_path' => 'setup/cost-centers.php'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة الحسابات', 'icon' => 'database',
                    'children' => [
                        ['id' => 'inputs-cash-boxes', 'label' => 'شاشة الصناديق', 'url_path' => 'inputs/cash-boxes.php'],
                        ['id' => 'inputs-banks', 'label' => 'شاشة البنوك', 'url_path' => 'inputs/banks.php'],
                        ['id' => 'inputs-opening-balances', 'label' => 'الأرصدة الافتتاحية', 'url_path' => 'inputs/opening-balances.php', 'badge' => 'قريبًا'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة المخازن', 'icon' => 'boxes',
                    'children' => [
                        ['id' => 'inputs-inventory-main-groups', 'label' => 'المجموعة الرئيسية', 'url_path' => 'setup/inventory-main-groups.php'],
                        ['id' => 'inputs-warehouse-groups', 'label' => 'مجموعات المخازن', 'url_path' => 'setup/warehouse-groups.php'],
                        ['id' => 'inputs-warehouses', 'label' => 'بيانات المخازن', 'url_path' => 'inputs/warehouses.php'],
                        ['id' => 'inputs-items', 'label' => 'بيانات الأصناف', 'url_path' => 'inputs/items.php'],
                        ['id' => 'inputs-item-prices', 'label' => 'تسعيرة الأصناف', 'url_path' => 'inputs/item-prices.php', 'badge' => 'قريبًا'],
                        ['id' => 'inputs-item-stock', 'label' => 'المخزون الافتتاحي', 'url_path' => 'inputs/item-stock.php', 'badge' => 'قريبًا'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة الموردين', 'icon' => 'truck',
                    'children' => [
                        ['id' => 'inputs-suppliers', 'label' => 'بيانات الموردين', 'url_path' => 'inputs/suppliers.php'],
                    ],
                ],
                [
                    'label' => 'مدخلات أنظمة العملاء', 'icon' => 'users',
                    'children' => [
                        ['id' => 'inputs-customers', 'label' => 'بيانات العملاء', 'url_path' => 'inputs/customers.php'],
                    ],
                ],
            ],
        ],

        // ====== العمليات (المرحلة 3) ======
        [
            'id' => 'operations', 'label' => 'العمليات', 'icon' => 'cart',
            'items' => [
                [
                    'label' => 'عمليات أنظمة الحسابات', 'icon' => 'database',
                    'children' => [
                        ['id' => 'ops-payment-vouchers', 'label' => 'سندات الصرف', 'url_path' => 'operations/payment-vouchers.php'],
                        ['id' => 'ops-receipt-vouchers', 'label' => 'سندات القبض', 'url_path' => 'operations/receipt-vouchers.php'],
                        ['id' => 'ops-daily-entries', 'label' => 'القيود اليومية', 'url_path' => 'operations/daily-entries.php'],
                        ['id' => 'ops-currency-diff', 'label' => 'فوارق العملة', 'url_path' => '#', 'badge' => 'قريبًا'],
                        ['id' => 'ops-debit-notes', 'label' => 'الإشعارات المدينة', 'url_path' => '#', 'badge' => 'قريبًا'],
                        ['id' => 'ops-credit-notes', 'label' => 'الإشعارات الدائنة', 'url_path' => '#', 'badge' => 'قريبًا'],
                    ],
                ],
                [
                    'label' => 'عمليات أنظمة المخزون', 'icon' => 'boxes',
                    'children' => [
                        ['id' => 'ops-supply-orders', 'label' => 'أمر التوريد المخزني', 'url_path' => 'operations/supply-orders.php', 'badge' => 'قريبًا'],
                        ['id' => 'ops-discharge-orders', 'label' => 'أمر الصرف المخزني', 'url_path' => 'operations/discharge-orders.php', 'badge' => 'قريبًا'],
                        ['id' => 'ops-inventory-transfers', 'label' => 'التحويل المخزني', 'url_path' => 'operations/inventory-transfers.php', 'badge' => 'قريبًا'],
                        ['id' => 'ops-inventory-adjustments', 'label' => 'تسوية المخزون', 'url_path' => 'operations/inventory-adjustments.php', 'badge' => 'قريبًا'],
                    ],
                ],
                [
                    'label' => 'عمليات المشتريات والمبيعات', 'icon' => 'cart',
                    'children' => [
                        ['id' => 'ops-purchase-invoices', 'label' => 'فواتير المشتريات', 'url_path' => 'operations/purchase-invoices.php'],
                        ['id' => 'ops-purchase-returns', 'label' => 'مردود المشتريات', 'url_path' => 'operations/purchase-returns.php'],
                        ['id' => 'ops-purchase-foreign', 'label' => 'المشتريات الخارجية', 'url_path' => 'operations/purchase-foreign.php'],
                        ['id' => 'ops-sales-invoices', 'label' => 'فواتير المبيعات', 'url_path' => 'operations/sales-invoices.php'],
                        ['id' => 'ops-sales-returns', 'label' => 'مردود المبيعات', 'url_path' => 'operations/sales-returns.php'],
                    ],
                ],
                [
                    'label' => 'إدارة المراجعة والترحيلات', 'icon' => 'clipboard',
                    'children' => [
                        ['id' => 'ops-reviews', 'label' => 'اعتماد الوثائق', 'url_path' => 'operations/reviews.php'],
                        ['id' => 'ops-closures', 'label' => 'الإقفال والتوقيف', 'url_path' => 'operations/closures.php'],
                    ],
                ],
            ],
        ],

        // ====== إدارة النظام (المرحلة 4) ======
        [
            'id' => 'system', 'label' => 'إدارة النظام', 'icon' => 'key',
            'items' => [
                ['id' => 'system-users', 'label' => 'بيانات المستخدمين', 'icon' => 'users', 'url_path' => 'system/users.php'],
                ['id' => 'system-activity-logs', 'label' => 'سجل النشاط', 'icon' => 'activity', 'url_path' => 'system/activity-logs.php'],
                ['id' => 'system-backup', 'label' => 'النسخ الاحتياطي', 'icon' => 'database', 'url_path' => '#', 'badge' => 'قريبًا'],
            ],
        ],
    ];
}

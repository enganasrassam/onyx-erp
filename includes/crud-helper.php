<?php
/**
 * مولّد شاشات CRUD عام — يبني شاشة قائمة + نموذج بسرعة
 * مطابق لتصميم أونكس (شريط أدوات + جدول)
 */

function render_crud_page(array $config): void {
    $search = trim($_GET['search'] ?? '');
    $where = $search ? "WHERE " . implode(' OR ', array_map(function($f) { return "{$f} LIKE ?"; }, $config['searchFields'])) : "";
    $params = $search ? array_fill(0, count($config['searchFields']), "%$search%") : [];
    $items = db_fetch_all("SELECT * FROM `{$config['table']}` {$where} ORDER BY {$config['orderBy']}", $params);

    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        db_delete($config['table'], 'id = ?', [$id]);
        flash('success', 'تم الحذف بنجاح');
        redirect(APP_URL . '/' . $config['listUrl']);
    }

    // ====== عنوان الشاشة ======
    echo '<div class="card mb-2"><div class="card-body" style="padding:10px 14px">';
    echo '<h4 style="font-size:15px;font-weight:700;color:var(--onyx-text);margin:0">' . sanitize($config['title']) . '</h4>';
    echo '<p style="font-size:11px;color:var(--onyx-text-muted);margin:2px 0 0">' . sanitize($config['description']) . '</p>';
    echo '</div></div>';

    // ====== شريط الأدوات (Toolbar) — مطابق لأونكس ======
    echo '<div class="onyx-toolbar">';
    if (!empty($config['addUrl'])) {
        echo '<a href="' . $config['addUrl'] . '" class="toolbar-btn toolbar-btn-primary">';
        echo '<svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>';
        echo 'إضافة';
        echo '</a>';
    }
    echo '<div class="toolbar-divider"></div>';
    echo '<button class="toolbar-btn"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>تحديث</button>';
    echo '<button class="toolbar-btn"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>طباعة</button>';
    echo '<div class="toolbar-search">';
    echo '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>';
    echo '<form method="GET" style="display:inline"><input type="text" name="search" placeholder="بحث..." value="' . sanitize($search) . '"></form>';
    echo '</div>';
    echo '</div>';

    // ====== الجدول ======
    echo '<div class="card"><div class="card-body" style="padding:0;overflow-x:auto">';
    echo '<table class="table"><thead><tr>';
    foreach ($config['columns'] as $col) {
        $align = isset($col['align']) ? ' ' . $col['align'] : '';
        echo '<th class="' . trim($align) . '">' . sanitize($col['label']) . '</th>';
    }
    echo '<th class="text-center" style="width:100px">إجراءات</th>';
    echo '</tr></thead><tbody>';
    if (empty($items)) {
        echo '<tr><td colspan="' . (count($config['columns']) + 1) . '" class="empty-state">لا توجد بيانات. اضغط "إضافة" للبدء.</td></tr>';
    } else {
        foreach ($items as $item) {
            echo '<tr>';
            foreach ($config['columns'] as $col) {
                $align = isset($col['align']) ? ' ' . $col['align'] : '';
                echo '<td class="' . trim($align) . '">';
                if (isset($col['render'])) {
                    echo $col['render']($item);
                } else {
                    $val = $item[$col['key']] ?? '';
                    if (!empty($col['code'])) {
                        echo '<code dir="ltr" class="bg-slate-100 px-1 py-0.5 rounded" style="font-size:11px">' . sanitize($val) . '</code>';
                    } else {
                        echo sanitize($val ?: '—');
                    }
                }
                echo '</td>';
            }
            echo '<td class="text-center"><div style="display:flex;gap:4px;justify-content:center">';
            if (!empty($config['editUrl'])) {
                echo '<a href="' . $config['editUrl'] . '?id=' . $item['id'] . '" class="toolbar-btn" style="padding:3px 6px" title="تعديل"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></a>';
            }
            echo '<a href="?delete=' . $item['id'] . '" class="toolbar-btn toolbar-btn-danger" style="padding:3px 6px" title="حذف" onclick="return confirmDelete(\'هل أنت متأكد من الحذف؟\')"><svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>';
            echo '</div></td></tr>';
        }
    }
    echo '</tbody></table>';
    echo '</div></div>';
}

function render_crud_form(array $config): void {
    $id = (int)($_GET['id'] ?? 0);
    $item = $id ? db_fetch_one("SELECT * FROM `{$config['table']}` WHERE id = ?", [$id]) : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
        $data = [];
        foreach ($config['fields'] as $field) {
            $val = $_POST[$field['name']] ?? '';
            if ($field['type'] === 'checkbox') {
                $data[$field['name']] = isset($_POST[$field['name']]) ? 1 : 0;
            } elseif ($field['type'] === 'number') {
                $data[$field['name']] = (float)$val;
            } else {
                $data[$field['name']] = $val === '' ? null : $val;
            }
        }
        foreach ($config['fields'] as $field) {
            if (!empty($field['required']) && empty($data[$field['name']])) {
                flash('error', $field['label'] . ' مطلوب');
                redirect($_SERVER['REQUEST_URI']);
            }
        }
        if ($id) {
            db_update($config['table'], $data, 'id = ?', [$id]);
            flash('success', 'تم التحديث بنجاح');
        } else {
            db_insert($config['table'], $data);
            flash('success', 'تمت الإضافة بنجاح');
        }
        redirect(APP_URL . '/' . $config['listUrl']);
    }

    // عنوان
    echo '<div class="card mb-2"><div class="card-body" style="padding:10px 14px">';
    echo '<h4 style="font-size:15px;font-weight:700;margin:0">' . sanitize($item ? $config['editTitle'] : $config['addTitle']) . '</h4>';
    echo '</div></div>';

    // شريط أدوات النموذج
    echo '<div class="onyx-toolbar">';
    echo '<button type="submit" form="crudForm" class="toolbar-btn toolbar-btn-primary"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L10 12.586l-2.293-2.293z"/></svg>حفظ</button>';
    echo '<div class="toolbar-divider"></div>';
    echo '<a href="' . APP_URL . '/' . $config['listUrl'] . '" class="toolbar-btn"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>إغلاق</a>';
    echo '</div>';

    // النموذج
    echo '<div class="card"><div class="card-body">';
    echo '<form method="POST" id="crudForm"><input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    echo '<div class="row g-3">';
    foreach ($config['fields'] as $field) {
        $value = $item[$field['name']] ?? ($field['default'] ?? '');
        $col = $field['col'] ?? 6;
        echo '<div class="col-md-' . $col . '">';
        if ($field['type'] === 'checkbox') {
            echo '<div class="form-check mt-3"><input type="checkbox" name="' . $field['name'] . '" class="form-check-input" id="' . $field['name'] . '" ' . ($value ? 'checked' : '') . '><label class="form-check-label" for="' . $field['name'] . '" style="font-size:12px">' . sanitize($field['label']) . '</label></div>';
        } else {
            echo '<label class="form-label">' . sanitize($field['label']) . (!empty($field['required']) ? ' *' : '') . '</label>';
            $dir = $field['dir'] ?? 'rtl';
            $extra = ($field['readonly'] ?? false) ? 'readonly' : '';
            if (!empty($field['options'])) {
                echo '<select name="' . $field['name'] . '" class="form-select" dir="' . $dir . '" ' . $extra . '>';
                foreach ($field['options'] as $optVal => $optLabel) {
                    $sel = ($value == $optVal) ? 'selected' : '';
                    echo '<option value="' . $optVal . '" ' . $sel . '>' . sanitize($optLabel) . '</option>';
                }
                echo '</select>';
            } else {
                $type = ($field['type'] === 'number') ? 'number' : (($field['type'] === 'date') ? 'date' : 'text');
                $step = ($field['type'] === 'number') ? ' step="0.01"' : '';
                echo '<input type="' . $type . '" name="' . $field['name'] . '" class="form-control" value="' . sanitize($value) . '" dir="' . $dir . '" ' . $extra . $step . ' placeholder="' . sanitize($field['placeholder'] ?? '') . '">';
            }
        }
        echo '</div>';
    }
    echo '</div>';
    echo '<div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">';
    echo '<a href="' . APP_URL . '/' . $config['listUrl'] . '" class="btn btn-secondary">إلغاء</a>';
    echo '<button type="submit" class="btn btn-primary">حفظ</button>';
    echo '</div>';
    echo '</form>';
    echo '</div></div>';
}

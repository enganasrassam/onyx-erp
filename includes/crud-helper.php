<?php
/**
 * مولّد شاشات CRUD عام — يبني شاشة قائمة + نموذج بسرعة
 */
function render_crud_page(array $config): void {
    $search = trim($_GET['search'] ?? '');
    $where = $search ? "WHERE " . implode(' OR ', array_map(fn($f) => "{$f} LIKE ?", $config['searchFields'])) : "";
    $params = $search ? array_fill(0, count($config['searchFields']), "%$search%") : [];
    $items = db_fetch_all("SELECT * FROM `{$config['table']}` {$where} ORDER BY {$config['orderBy']}", $params);

    // Delete
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        if (!empty($config['beforeDelete'])) {
            ($config['beforeDelete'])($id);
        } else {
            db_delete($config['table'], 'id = ?', [$id]);
        }
        flash('success', 'تم الحذف بنجاح');
        redirect(APP_URL . '/' . $config['listUrl']);
    }

    echo '<div class="card mb-4"><div class="card-body d-flex align-items-center justify-content-between gap-3">';
    echo '<div><h4 class="fw-bold text-slate-800 mb-1">' . sanitize($config['title']) . '</h4>';
    echo '<p class="text-sm text-slate-500 mb-0">' . sanitize($config['description']) . '</p></div>';
    if (!empty($config['addUrl'])) {
        echo '<a href="' . $config['addUrl'] . '" class="btn btn-primary d-flex align-items-center gap-1">';
        echo '<svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/></svg>';
        echo 'إضافة</a>';
    }
    echo '</div></div>';

    echo '<form method="GET" class="mb-3 d-flex gap-2"><input type="text" name="search" class="form-control" placeholder="بحث..." value="' . sanitize($search) . '"><button class="btn btn-outline-secondary">بحث</button>';
    if ($search) echo '<a href="' . APP_URL . '/' . $config['listUrl'] . '" class="btn btn-outline-secondary">إلغاء</a>';
    echo '</form>';

    echo '<div class="card"><div class="card-body p-0"><table class="table mb-0"><thead><tr>';
    foreach ($config['columns'] as $col) {
        echo '<th class="' . ($col['align'] ?? '') . '">' . sanitize($col['label']) . '</th>';
    }
    echo '<th class="text-center">إجراءات</th></tr></thead><tbody>';
    if (empty($items)) {
        echo '<tr><td colspan="' . (count($config['columns']) + 1) . '" class="text-center py-4 text-slate-400">لا توجد بيانات</td></tr>';
    } else {
        foreach ($items as $item) {
            echo '<tr>';
            foreach ($config['columns'] as $col) {
                echo '<td class="' . ($col['align'] ?? '') . '">';
                if (!empty($col['render'])) {
                    echo ($col['render'])($item);
                } else {
                    $val = $item[$col['key']] ?? '';
                    if (!empty($col['code'])) echo '<code dir="ltr" class="bg-slate-100 px-2 py-0.5 rounded font-mono">' . sanitize($val) . '</code>';
                    else echo sanitize($val ?: '—');
                }
                echo '</td>';
            }
            echo '<td class="text-center"><div class="d-flex gap-1 justify-content-center">';
            if (!empty($config['editUrl'])) {
                echo '<a href="' . $config['editUrl'] . '?id=' . $item['id'] . '" class="btn btn-sm btn-outline-primary"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></a>';
            }
            echo '<a href="?delete=' . $item['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete(\'هل أنت متأكد من الحذف؟\')"><svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9z" clip-rule="evenodd"/></svg></a>';
            echo '</div></td></tr>';
        }
    }
    echo '</tbody></table></div></div>';
}

/**
 * مولّد نموذج إضافة/تعديل
 */
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
        // Required validation
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

    echo '<div class="card"><div class="card-header"><h5 class="mb-0">' . sanitize($item ? $config['editTitle'] : $config['addTitle']) . '</h5></div>';
    echo '<div class="card-body"><form method="POST"><input type="hidden" name="csrf_token" value="' . csrf_token() . '"><div class="row g-3">';
    foreach ($config['fields'] as $field) {
        $value = $item[$field['name']] ?? ($field['default'] ?? '');
        $col = $field['col'] ?? 6;
        echo '<div class="col-md-' . $col . '">';
        if ($field['type'] === 'checkbox') {
            echo '<div class="form-check mt-4"><input type="checkbox" name="' . $field['name'] . '" class="form-check-input" id="' . $field['name'] . '" ' . ($value ? 'checked' : '') . '><label class="form-check-label" for="' . $field['name'] . '">' . sanitize($field['label']) . '</label></div>';
        } else {
            echo '<label class="form-label small fw-semibold">' . sanitize($field['label']) . (!empty($field['required']) ? ' *' : '') . '</label>';
            $dir = $field['dir'] ?? 'rtl';
            $extra = ($field['readonly'] ?? false) ? 'readonly' : '';
            $extra .= $field['type'] === 'number' ? ' step="0.01"' : '';
            if (!empty($field['options'])) {
                echo '<select name="' . $field['name'] . '" class="form-select" dir="' . $dir . '" ' . $extra . '>';
                foreach ($field['options'] as $optVal => $optLabel) {
                    $sel = $value == $optVal ? 'selected' : '';
                    echo '<option value="' . $optVal . '" ' . $sel . '>' . sanitize($optLabel) . '</option>';
                }
                echo '</select>';
            } else {
                $type = $field['type'] === 'number' ? 'number' : ($field['type'] === 'date' ? 'date' : 'text');
                echo '<input type="' . $type . '" name="' . $field['name'] . '" class="form-control" value="' . sanitize($value) . '" dir="' . $dir . '" ' . $extra . ' placeholder="' . sanitize($field['placeholder'] ?? '') . '">';
            }
        }
        echo '</div>';
    }
    echo '</div><div class="mt-4 d-flex gap-2 justify-content-end"><a href="' . APP_URL . '/' . $config['listUrl'] . '" class="btn btn-secondary">إلغاء</a><button type="submit" class="btn btn-primary">حفظ</button></div></form></div></div>';
}

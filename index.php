<?php
require_once __DIR__ . '/includes/auth.php';
redirect(APP_URL . (is_logged_in() ? '/dashboard.php' : '/login.php'));

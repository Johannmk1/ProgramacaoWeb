<?php

if (!function_exists('render_login_card')) {
    require_once __DIR__ . '/../../src/Views/Components/LoginTemplate.php';
}

$loginProps = $loginProps ?? [];
echo render_login_card($loginProps);


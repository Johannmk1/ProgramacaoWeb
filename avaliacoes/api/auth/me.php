<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../src/Utils/Auth.php';

    Auth::start();
    if (!empty($_SESSION['user_id'])) {
        echo json_encode(['status' => 'authenticated', 'user' => ['id' => (int)$_SESSION['user_id'], 'username' => (string)($_SESSION['username'] ?? '')]]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'unauthenticated']);
    }


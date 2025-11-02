<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../src/Utils/Auth.php';

    Auth::logout();
    echo json_encode(['status' => 'success']);


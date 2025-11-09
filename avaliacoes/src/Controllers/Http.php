<?php

function http_json(): void {
    header('Content-Type: application/json; charset=utf-8');
}

function body_json(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function json_ok($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

function json_error(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
}

function require_method(string $method): void {
    $current = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (strtoupper($current) !== strtoupper($method)) {
        json_error(405, 'Método não permitido');
        exit;
    }
}


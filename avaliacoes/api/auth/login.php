<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../src/Config/Database.php';
    require_once __DIR__ . '/../../src/Utils/Auth.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
        exit;
    }

    $pdo = (new Database())->connect();
    try { $pdo->exec("SET client_encoding TO 'UTF8'"); } catch (Throwable $e) {}

    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $username = trim($data['username'] ?? '');
    $password = (string)($data['password'] ?? '');

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Credenciais inválidas']);
        exit;
    }

    $ok = Auth::login($pdo, $username, $password);
    if ($ok) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Usuário ou senha incorretos']);
    }


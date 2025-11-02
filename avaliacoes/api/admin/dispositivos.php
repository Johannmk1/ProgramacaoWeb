<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../src/Config/Database.php';
    require_once __DIR__ . '/../../src/Models/Dispositivo.php';
    require_once __DIR__ . '/../../src/Utils/Auth.php';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $pdo = (new Database())->connect();
    try { $pdo->exec("SET client_encoding TO 'UTF8'"); } catch (Throwable $e) {}

    function json_error(int $code, string $msg) {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    function body_json(): array { return json_decode(file_get_contents('php://input'), true) ?? []; }

    switch ($method) {
        case 'GET':
            $ativos = isset($_GET['ativos']) && $_GET['ativos'] == '1';
            $rs = Dispositivo::listar($pdo, $ativos);
            echo json_encode($rs, JSON_UNESCAPED_UNICODE);
            break;
        case 'POST':
            $data = body_json();
            $nome = trim($data['nome'] ?? '');
            $codigo = trim($data['codigo'] ?? '');
            $id_setor = array_key_exists('id_setor', $data) ? ($data['id_setor'] !== null ? (int)$data['id_setor'] : null) : null;
            $status = array_key_exists('status', $data) ? (bool)$data['status'] : true;
            if ($nome === '' || $codigo === '') json_error(400, 'Nome e código são obrigatórios');
            try {
                $id = Dispositivo::criar($pdo, $nome, $codigo, $id_setor, $status);
                echo json_encode(['status' => 'success', 'id' => $id]);
            } catch (Throwable $e) {
                json_error(400, 'Código já existe ou dados inválidos');
            }
            break;
        case 'PUT':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_error(400, 'ID inválido');
            $data = body_json();
            $ok = Dispositivo::atualizar(
                $pdo,
                $id,
                isset($data['nome']) ? trim($data['nome']) : null,
                isset($data['codigo']) ? trim($data['codigo']) : null,
                array_key_exists('id_setor', $data) ? ($data['id_setor'] !== null ? (int)$data['id_setor'] : null) : null,
                isset($data['status']) ? (bool)$data['status'] : null
            );
            echo json_encode(['status' => $ok ? 'success' : 'error']);
            break;
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_error(400, 'ID inválido');
            $hard = isset($_GET['hard']) && $_GET['hard'] == '1';
            $ok = $hard ? Dispositivo::excluir($pdo, $id) : Dispositivo::ativar($pdo, $id, false);
            echo json_encode(['status' => $ok ? 'success' : 'error']);
            break;
        default:
            json_error(405, 'Método não permitido');
    }
    if ($method !== 'GET' || (isset($_GET['admin']) && $_GET['admin'] == '1')) { Auth::requireLogin(); }

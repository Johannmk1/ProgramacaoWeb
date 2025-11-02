<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../src/Config/Database.php';
    require_once __DIR__ . '/../../src/Utils/Auth.php';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $db = (new Database())->connect();
    $pdo = $db;

    try { $pdo->exec("SET client_encoding TO 'UTF8'"); } catch (Throwable $e) {}

    function json_error(int $code, string $msg) {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    function body_json(): array {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    switch ($method) {
        case 'GET':
            $somenteAtivas = isset($_GET['ativas']) && $_GET['ativas'] == '1';
            $sql = 'SELECT id, texto, status, ordem FROM perguntas' . ($somenteAtivas ? ' WHERE status = TRUE' : '') . ' ORDER BY ordem ASC, id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
            break;

        case 'POST':
            $data = body_json();
            $texto = trim($data['texto'] ?? '');
            $ordem = isset($data['ordem']) ? (int)$data['ordem'] : 0;
            $status = array_key_exists('status', $data) ? (bool)$data['status'] : true;
            if ($texto === '') json_error(400, 'Texto é obrigatório');
            $stmt = $pdo->prepare('INSERT INTO perguntas (texto, status, ordem) VALUES (?, ?, ?)');
            $ok = $stmt->execute([$texto, $status, $ordem]);
            echo json_encode(['status' => $ok ? 'success' : 'error'], JSON_UNESCAPED_UNICODE);
            break;

        case 'PUT':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_error(400, 'ID inválido');
            $data = body_json();
            $fields = [];
            $params = [];
            if (isset($data['texto'])) { $fields[] = 'texto = ?'; $params[] = trim($data['texto']); }
            if (isset($data['ordem'])) { $fields[] = 'ordem = ?'; $params[] = (int)$data['ordem']; }
            if (isset($data['status'])) { $fields[] = 'status = ?'; $params[] = (bool)$data['status']; }
            if (empty($fields)) { echo json_encode(['status' => 'success']); break; }
            $params[] = $id;
            $sql = 'UPDATE perguntas SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute($params);
            echo json_encode(['status' => $ok ? 'success' : 'error']);
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_error(400, 'ID inválido');
            $hard = isset($_GET['hard']) && $_GET['hard'] == '1';
            if ($hard) {
                $stmt = $pdo->prepare('DELETE FROM perguntas WHERE id = ?');
                $ok = $stmt->execute([$id]);
            } else {
                $stmt = $pdo->prepare('UPDATE perguntas SET status = FALSE WHERE id = ?');
                $ok = $stmt->execute([$id]);
            }
            echo json_encode(['status' => $ok ? 'success' : 'error']);
            break;

        default:
            json_error(405, 'Método não permitido');
    }
    if ($method !== 'GET' || (isset($_GET['admin']) && $_GET['admin'] == '1')) { Auth::requireLogin(); }

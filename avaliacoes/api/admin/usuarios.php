<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../src/Config/Database.php';
    require_once __DIR__ . '/../../src/Utils/Auth.php';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $pdo = (new Database())->connect();
    try { $pdo->exec("SET client_encoding TO 'UTF8'"); } catch (Throwable $e) {}

    Auth::requireLogin();

    function json_error(int $code, string $msg) {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }
    function body_json(): array { return json_decode(file_get_contents('php://input'), true) ?? []; }

    switch ($method) {
        case 'GET':
            $stmt = $pdo->query('SELECT id, username, status, created_at FROM usuarios ORDER BY id ASC');
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
            break;
        case 'POST':
            $d = body_json();
            $username = trim($d['username'] ?? '');
            $password = (string)($d['password'] ?? '');
            $status = array_key_exists('status', $d) ? (bool)$d['status'] : true;
            if ($username === '' || $password === '') json_error(400, 'Usuário e senha obrigatórios');
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $st = $pdo->prepare('INSERT INTO usuarios (username, password_hash, status) VALUES (?, ?, ?)');
                $st->execute([$username, $hash, $status]);
                echo json_encode(['status' => 'success', 'id' => (int)$pdo->lastInsertId('usuarios_id_seq')]);
            } catch (Throwable $e) {
                json_error(400, 'Usuário já existe ou dados inválidos');
            }
            break;
        case 'PUT':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_error(400, 'ID inválido');
            $d = body_json();
            if (isset($d['password'])) {
                $hash = password_hash((string)$d['password'], PASSWORD_DEFAULT);
                $st = $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
                $ok = $st->execute([$hash, $id]);
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            }
            $fields = [];$params=[];
            if (isset($d['username'])) { $fields[] = 'username = ?'; $params[] = trim($d['username']); }
            if (isset($d['status'])) { $fields[] = 'status = ?'; $params[] = (bool)$d['status']; }
            if (empty($fields)) { echo json_encode(['status' => 'success']); break; }
            $params[] = $id;
            $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $st = $pdo->prepare($sql);
            $ok = $st->execute($params);
            echo json_encode(['status' => $ok ? 'success' : 'error']);
            break;
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_error(400, 'ID inválido');
            $st = $pdo->prepare('UPDATE usuarios SET status = FALSE WHERE id = ?');
            $ok = $st->execute([$id]);
            echo json_encode(['status' => $ok ? 'success' : 'error']);
            break;
        default:
            json_error(405, 'Método não permitido');
    }


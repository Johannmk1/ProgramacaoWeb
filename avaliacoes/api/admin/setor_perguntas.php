<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../../src/Config/Database.php';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $pdo = (new Database())->connect();
    try { $pdo->exec("SET client_encoding TO 'UTF8'"); } catch (Throwable $e) {}

    function json_error(int $code, string $msg) {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    function body_json(): array { return json_decode(file_get_contents('php://input'), true) ?? []; }

    if ($method === 'GET') {
        $id_setor = isset($_GET['id_setor']) ? (int)$_GET['id_setor'] : 0;
        if ($id_setor <= 0) json_error(400, 'id_setor é obrigatório');
        $sql = "SELECT p.id, p.texto, p.status, p.ordem,
                    CASE WHEN ps.id_pergunta IS NULL THEN FALSE ELSE TRUE END AS vinculada
                FROM perguntas p
                LEFT JOIN perguntas_setor ps ON ps.id_pergunta = p.id AND ps.id_setor = ?
                ORDER BY p.ordem ASC, p.id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_setor]);
        echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $data = body_json();
        $id_setor = isset($data['id_setor']) ? (int)$data['id_setor'] : 0;
        $ids = isset($data['ids_perguntas']) && is_array($data['ids_perguntas']) ? $data['ids_perguntas'] : null;
        if ($id_setor <= 0) json_error(400, 'id_setor é obrigatório');
        if ($ids === null) json_error(400, 'ids_perguntas é obrigatório');

        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM perguntas_setor WHERE id_setor = ?')->execute([$id_setor]);
            if (!empty($ids)) {
                $ins = $pdo->prepare('INSERT INTO perguntas_setor (id_setor, id_pergunta) VALUES (?, ?)');
                foreach ($ids as $pid) {
                    $ins->execute([$id_setor, (int)$pid]);
                }
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Throwable $e) {
            $pdo->rollBack();
            json_error(500, 'Falha ao salvar mapeamento');
        }
        exit;
    }

    json_error(405, 'Método não permitido');


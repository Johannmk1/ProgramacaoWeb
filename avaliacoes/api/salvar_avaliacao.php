<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../src/Config/Database.php';
    require_once __DIR__ . '/../src/Controllers/AvaliacaoController.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $respostas = $payload['respostas'] ?? null;
    $feedback = $payload['feedback'] ?? null;
    $device = $payload['device'] ?? ($_GET['device'] ?? null);

    if (!is_array($respostas) || empty($respostas)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $db = (new Database())->connect();
    $controller = new AvaliacaoController($db);

    $ok = $controller->salvarAvaliacao($respostas, $feedback, $device ? (string)$device : null);
    if ($ok) {
        echo json_encode(['status' => 'success', 'message' => 'Avaliação enviada com sucesso'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Falha ao salvar avaliação'], JSON_UNESCAPED_UNICODE);
    }

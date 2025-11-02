<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '/../src/Config/Database.php';
    require_once __DIR__ . '/../src/Controllers/AvaliacaoController.php';
    require_once __DIR__ . '/../src/Models/Pergunta.php';

    $db = (new Database())->connect();
    $pdo = $db; // alias
    try { $pdo->exec("SET client_encoding TO 'UTF8'"); } catch (Throwable $e) {}

    Pergunta::seedIfEmpty($pdo);

    $device = isset($_GET['device']) ? trim($_GET['device']) : null;
    $result = [];

    if ($device) {
        $sqlSetor = "SELECT d.id_setor FROM dispositivos d
                    INNER JOIN setores s ON s.id = d.id_setor AND s.status = TRUE
                    WHERE d.codigo = ? AND d.status = TRUE LIMIT 1";
        $st = $pdo->prepare($sqlSetor);
        $st->execute([$device]);
        $id_setor = $st->fetchColumn();
        if ($id_setor) {
            $sql = "SELECT p.id, p.texto
                    FROM perguntas p
                    INNER JOIN perguntas_setor ps ON ps.id_pergunta = p.id AND ps.id_setor = ?
                    WHERE p.status = TRUE
                    ORDER BY p.ordem ASC, p.id ASC";
            $q = $pdo->prepare($sql);
            $q->execute([(int)$id_setor]);
            $rows = $q->fetchAll();
            if (!empty($rows)) {
                foreach ($rows as $r) { $result[] = ['id' => (int)$r['id'], 'texto' => $r['texto']]; }
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }

    // todas as perguntas ativas
    $controller = new AvaliacaoController($db);
    foreach ($controller->getPerguntas() as $p) {
        $result[] = [ 'id' => $p->getId(), 'texto' => $p->getTexto() ];
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);


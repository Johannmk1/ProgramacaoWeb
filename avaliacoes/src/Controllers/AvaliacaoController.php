<?php

require_once __DIR__ . '/Http.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Pergunta.php';
require_once __DIR__ . '/../Models/Avaliacao.php';
require_once __DIR__ . '/../Models/Dispositivo.php';

class AvaliacaoController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPerguntas(): array {
        return Pergunta::getAtivas($this->pdo);
    }

    public function salvarAvaliacao(array $respostas, ?string $feedback = null, ?string $id_dispositivo = null): bool {
        return Avaliacao::salvarLote($this->pdo, $respostas, $feedback, $id_dispositivo);
    }
}
function handle_salvar_avaliacao(): void {
    http_json();
    require_method('POST');

    $payload = body_json();
    $respostas = $payload['respostas'] ?? null;
    $feedback = $payload['feedback'] ?? null;
    $device = $payload['device'] ?? ($_GET['device'] ?? null);

    if (!is_array($respostas) || empty($respostas)) { json_error(400, 'Dados invǭlidos'); return; }

    $db = (new Database())->connect();
    $controller = new AvaliacaoController($db);

    $ok = $controller->salvarAvaliacao($respostas, $feedback, $device ? (string)$device : null);
    if ($ok) { json_ok(['status' => 'success']); }
    else { json_error(500, 'Falha ao salvar avaliação'); }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $action = $_GET['action'] ?? '';
    if ($action === 'salvar') { handle_salvar_avaliacao(); }
    elseif ($action === 'perguntas') { handle_listar_perguntas(); }
}

function handle_listar_perguntas(): void {
    http_json();

    $db = (new Database())->connect();
    $pdo = $db;

    try { Pergunta::seedIfEmpty($pdo); } catch (Throwable $e) {}

    $device = isset($_GET['device']) ? trim((string)$_GET['device']) : '';
    $result = [];

    if ($device !== '') {
        $rows = Pergunta::listarPorDispositivo($pdo, $device);
            if (!empty($rows)) {
                foreach ($rows as $r) { 
                    $result[] = ['id' => (int)$r['id'], 'texto' => (string)$r['texto']]; 
                }
                json_ok($result);
                return;
            }
    }

    $controller = new AvaliacaoController($db);
    foreach ($controller->getPerguntas() as $p) {
        $result[] = ['id' => $p->getId(), 'texto' => $p->getTexto()];
    }
    json_ok($result);
}


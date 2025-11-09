<?php

require_once __DIR__ . '/Http.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Dispositivo.php';

class DispositivoController { }

// Handler HTTP: lista dispositivos públicos (opcional ?ativos=1)
function handle_dispositivos_publicos(): void {
    http_json();
    $pdo = (new Database())->connect();
    $ativos = isset($_GET['ativos']) && $_GET['ativos'] == '1';
    $rows = Dispositivo::listar($pdo, $ativos);
    json_ok($rows);
}

// Execução direta (?action=publicos)
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $action = $_GET['action'] ?? '';
    if ($action === 'publicos') { handle_dispositivos_publicos(); }
}

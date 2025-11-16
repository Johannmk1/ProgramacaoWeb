<?php

require_once __DIR__ . '/Http.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Auth.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Setor.php';
require_once __DIR__ . '/../Models/Dispositivo.php';
require_once __DIR__ . '/../Models/Pergunta.php';
require_once __DIR__ . '/../Models/PerguntaSetor.php';
require_once __DIR__ . '/../Views/Admin/AdminTableRenderer.php';

http_json();

$pdo = (new Database())->connect();

$resource = $_GET['resource'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Somente operações administrativas exigem autenticação
if ($resource !== '') { Auth::requireLogin(); }

switch ($resource) {
    case 'usuarios':
        switch ($method) {
            case 'GET':
                if (isset($_GET['format']) && $_GET['format'] === 'html') {
                    header('Content-Type: text/html; charset=utf-8');
                    echo render_admin_table($pdo, 'usuarios');
                    break;
                }
                echo json_encode(Usuario::listar($pdo), JSON_UNESCAPED_UNICODE);
                break;
            case 'POST':
                $d = body_json();
                $username = trim($d['username'] ?? '');
                $password = (string)($d['password'] ?? '');
                $status = array_key_exists('status', $d) ? (bool)$d['status'] : true;
                if ($username === '' || $password === '') { json_error(400, 'Usuario e senha sao obrigatorios'); exit; }
                try {
                    $id = Usuario::criar($pdo, $username, $password, $status);
                    echo json_encode(['status' => 'success', 'id' => $id]);
                } catch (Throwable $e) {
                    json_error(400, 'Usuario ja existe ou dados invalidos');
                }
                break;
            case 'PUT':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $d = body_json();
                if (isset($d['password'])) {
                    $ok = Usuario::alterarSenha($pdo, $id, (string)$d['password']);
                    echo json_encode(['status' => $ok ? 'success' : 'error']);
                    break;
                }
                $ok = Usuario::atualizar(
                    $pdo,
                    $id,
                    isset($d['username']) ? trim($d['username']) : null,
                    isset($d['status']) ? (bool)$d['status'] : null
                );
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            case 'DELETE':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $hard = isset($_GET['hard']) && $_GET['hard'] == '1';
                if ($hard && Usuario::total($pdo) <= 1) {
                    json_error(400, 'É necessário manter pelo menos um usuário cadastrado');
                    break;
                }
                $ok = Usuario::desativar($pdo, $id, $hard);
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            default:
                json_error(405, 'Metodo nao permitido');
        }
        break;

    case 'setores':
        switch ($method) {
            case 'GET':
                if (isset($_GET['format']) && $_GET['format'] === 'html') {
                    header('Content-Type: text/html; charset=utf-8');
                    echo render_admin_table($pdo, 'setores');
                    break;
                }
                $ativos = isset($_GET['ativos']) && $_GET['ativos'] == '1';
                echo json_encode(Setor::listar($pdo, $ativos), JSON_UNESCAPED_UNICODE);
                break;
            case 'POST':
                $d = body_json();
                $nome = trim($d['nome'] ?? '');
                $status = array_key_exists('status', $d) ? (bool)$d['status'] : true;
                if ($nome === '') { json_error(400, 'Nome e obrigatorio'); exit; }
                $id = Setor::criar($pdo, $nome, $status);
                echo json_encode(['status' => 'success', 'id' => $id]);
                break;
            case 'PUT':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $d = body_json();
                $ok = Setor::atualizar(
                    $pdo,
                    $id,
                    isset($d['nome']) ? trim($d['nome']) : null,
                    isset($d['status']) ? (bool)$d['status'] : null
                );
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            case 'DELETE':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $hard = isset($_GET['hard']) && $_GET['hard'] == '1';
                $ok = $hard ? Setor::excluir($pdo, $id) : Setor::ativar($pdo, $id, false);
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            default:
                json_error(405, 'Metodo nao permitido');
        }
        break;

    case 'dispositivos':
        switch ($method) {
            case 'GET':
                if (isset($_GET['format']) && $_GET['format'] === 'html') {
                    header('Content-Type: text/html; charset=utf-8');
                    echo render_admin_table($pdo, 'dispositivos');
                    break;
                }
                $ativos = isset($_GET['ativos']) && $_GET['ativos'] == '1';
                echo json_encode(Dispositivo::listar($pdo, $ativos), JSON_UNESCAPED_UNICODE);
                break;
            case 'POST':
                $d = body_json();
                $nome = trim($d['nome'] ?? '');
                $codigo = trim($d['codigo'] ?? '');
                $id_setor = array_key_exists('id_setor', $d) ? ($d['id_setor'] !== null ? (int)$d['id_setor'] : null) : null;
                $status = array_key_exists('status', $d) ? (bool)$d['status'] : true;
                if ($nome === '' || $codigo === '') { json_error(400, 'Nome e codigo sao obrigatorios'); exit; }
                try {
                    $id = Dispositivo::criar($pdo, $nome, $codigo, $id_setor, $status);
                    echo json_encode(['status' => 'success', 'id' => $id]);
                } catch (Throwable $e) {
                    json_error(400, 'Codigo ja existe ou dados invalidos');
                }
                break;
            case 'PUT':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $d = body_json();
                $fields = [];
                if (isset($d['nome'])) { $fields['nome'] = trim((string)$d['nome']); }
                if (isset($d['codigo'])) { $fields['codigo'] = trim((string)$d['codigo']); }
                if (array_key_exists('id_setor', $d)) { $fields['id_setor'] = ($d['id_setor'] !== null ? (int)$d['id_setor'] : null); }
                if (isset($d['status'])) { $fields['status'] = (bool)$d['status']; }
                $ok = Dispositivo::atualizarCampos($pdo, $id, $fields);
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            case 'DELETE':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $hard = isset($_GET['hard']) && $_GET['hard'] == '1';
                $ok = $hard ? Dispositivo::excluir($pdo, $id) : Dispositivo::ativar($pdo, $id, false);
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            default:
                json_error(405, 'Metodo nao permitido');
        }
        break;

    case 'perguntas':
        switch ($method) {
            case 'GET':
                if (isset($_GET['format']) && $_GET['format'] === 'html') {
                    header('Content-Type: text/html; charset=utf-8');
                    echo render_admin_table($pdo, 'perguntas');
                    break;
                }
                $somenteAtivas = isset($_GET['ativas']) && $_GET['ativas'] == '1';
                echo json_encode(Pergunta::listar($pdo, $somenteAtivas), JSON_UNESCAPED_UNICODE);
                break;
            case 'POST':
                $d = body_json();
                $texto = trim($d['texto'] ?? '');
                $ordem = isset($d['ordem']) ? (int)$d['ordem'] : 0;
                $status = array_key_exists('status', $d) ? (bool)$d['status'] : true;
                $tipo = isset($d['tipo']) ? (string)$d['tipo'] : 'nps';
                if ($texto === '') { json_error(400, 'Texto e obrigatorio'); exit; }
                $id = Pergunta::criar($pdo, $texto, $ordem, $status, $tipo);
                echo json_encode(['status' => $id > 0 ? 'success' : 'error', 'id' => $id]);
                break;
            case 'PUT':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $d = body_json();
                $ok = Pergunta::atualizar(
                    $pdo,
                    $id,
                    isset($d['texto']) ? trim($d['texto']) : null,
                    isset($d['ordem']) ? (int)$d['ordem'] : null,
                    isset($d['status']) ? (bool)$d['status'] : null,
                    isset($d['tipo']) ? (string)$d['tipo'] : null
                );
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            case 'DELETE':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) { json_error(400, 'ID invalido'); exit; }
                $hard = isset($_GET['hard']) && $_GET['hard'] == '1';
                $ok = $hard ? Pergunta::excluir($pdo, $id) : Pergunta::ativar($pdo, $id, false);
                echo json_encode(['status' => $ok ? 'success' : 'error']);
                break;
            default:
                json_error(405, 'Metodo nao permitido');
        }
        break;

    case 'theme':
        $themeFile = realpath(__DIR__ . '/../../public/config/theme.json');
        $defaults = [
            'primaryColor' => '#2563eb',
            'secondaryColor' => '#0ea5e9',
            'tertiaryColor' => '#f7f8fa',
            'cardMaxWidth' => '820px',
        ];
        switch ($method) {
            case 'GET':
                $content = $defaults;
                if ($themeFile && file_exists($themeFile)) {
                    $json = @file_get_contents($themeFile);
                    $data = json_decode($json, true);
                    if (is_array($data)) {
                        $content = array_merge($defaults, $data);
                    }
                }
                echo json_encode($content, JSON_UNESCAPED_UNICODE);
                break;
            case 'PUT':
                if (!$themeFile) { json_error(500, 'Arquivo de tema não encontrado'); break; }
                $d = body_json();
                $theme = [
                    'primaryColor' => sanitize_color($d['primaryColor'] ?? $defaults['primaryColor']),
                    'secondaryColor' => sanitize_color($d['secondaryColor'] ?? $defaults['secondaryColor']),
                    'tertiaryColor' => sanitize_color($d['tertiaryColor'] ?? $defaults['tertiaryColor']),
                    'cardMaxWidth' => sanitize_size($d['cardMaxWidth'] ?? $defaults['cardMaxWidth']),
                ];
                $json = json_encode($theme, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if (@file_put_contents($themeFile, $json) === false) {
                    json_error(500, 'Falha ao salvar tema');
                    break;
                }
                echo json_encode(['status' => 'success', 'theme' => $theme]);
                break;
            default:
                json_error(405, 'Metodo nao permitido');
        }
        break;

    case 'setor_perguntas':
        if ($method === 'GET') {
            $id_setor = isset($_GET['id_setor']) ? (int)$_GET['id_setor'] : 0;
            if ($id_setor <= 0) { json_error(400, 'id_setor e obrigatorio'); exit; }
            $rows = PerguntaSetor::listarPerguntasComVinculo($pdo, $id_setor);
            echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        } elseif ($method === 'POST') {
            $d = body_json();
            $id_setor = isset($d['id_setor']) ? (int)$d['id_setor'] : 0;
            $ids = isset($d['ids_perguntas']) && is_array($d['ids_perguntas']) ? $d['ids_perguntas'] : null;
            if ($id_setor <= 0) { json_error(400, 'id_setor e obrigatorio'); exit; }
            if ($ids === null) { json_error(400, 'ids_perguntas e obrigatorio'); exit; }
            $ok = PerguntaSetor::salvarMapeamento($pdo, $id_setor, $ids);
            if ($ok) echo json_encode(['status' => 'success']); else json_error(500, 'Falha ao salvar mapeamento');
        } else {
            json_error(405, 'Metodo nao permitido');
        }
        break;

    default:
        json_error(404, 'Recurso nao encontrado');
}

function sanitize_color(string $value): string {
    $value = trim($value);
    if (preg_match('/^#?[0-9a-fA-F]{6}$/', $value)) {
        return '#' . ltrim($value, '#');
    }
    return '#2563eb';
}

function sanitize_size(string $value): string {
    $value = trim($value);
    if (preg_match('/^\d{2,4}(px|%)$/', $value)) {
        return $value;
    }
    return '820px';
}

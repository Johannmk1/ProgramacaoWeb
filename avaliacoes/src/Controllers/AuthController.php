<?php

require_once __DIR__ . '/Http.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/Auth.php';

class AuthController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function login(string $username, string $password): bool {
        return Auth::login($this->pdo, $username, $password);
    }

    public function logout(): void {
        Auth::logout();
    }

    public function me(): ?array {
        Auth::start();
        if (!empty($_SESSION['user_id'])) {
            return [
                'id' => (int)$_SESSION['user_id'],
                'username' => (string)($_SESSION['username'] ?? ''),
            ];
        }
        return null;
    }
}

function handle_auth_login(): void {
    http_json();
    require_method('POST');
    $pdo = (new Database())->connect();
    $data = body_json();
    $username = trim($data['username'] ?? '');
    $password = (string)($data['password'] ?? '');
    if ($username === '' || $password === '') { json_error(400, 'Credenciais inválidas'); return; }
    $controller = new AuthController($pdo);
    $ok = $controller->login($username, $password);
    if ($ok) { json_ok(['status' => 'success']); }
    else { json_error(401, 'Usuário ou senha incorretos'); }
}

function handle_auth_logout(): void {
    http_json();
    $pdo = (new Database())->connect();
    $controller = new AuthController($pdo);
    $controller->logout();
    json_ok(['status' => 'success']);
}

function handle_auth_me(): void {
    http_json();
    $pdo = (new Database())->connect();
    $controller = new AuthController($pdo);
    $u = $controller->me();
    if ($u) { json_ok(['status' => 'authenticated', 'user' => $u]); }
    else { json_error(401, 'unauthenticated'); }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $action = $_GET['action'] ?? '';
    if ($action === 'login') handle_auth_login();
    elseif ($action === 'logout') handle_auth_logout();
    elseif ($action === 'me') handle_auth_me();
    else { http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Rota não encontrada']); }
}

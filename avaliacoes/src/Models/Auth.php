<?php

require_once __DIR__ . '/Usuario.php';

class Auth {
    public static function start(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function requireLogin(): void {
        self::start();
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Não autenticado']);
            exit;
        }
    }

    public static function login(PDO $pdo, string $username, string $password): bool {
        self::start();
        $u = Usuario::findByUsername($pdo, $username);
        if (!$u || empty($u['status'])) { return false; }

        $hash = (string)($u['password_hash'] ?? '');
        $info = password_get_info($hash);
        $ok = false;
        if (!empty($info['algo'])) {
            $ok = password_verify($password, $hash);
        } else {
            $ok = $hash !== '' && hash_equals($hash, $password);
            if ($ok) {
                try { Usuario::alterarSenha($pdo, (int)$u['id'], $password); } catch (Throwable $e) { /* ignore */ }
            }
        }
        if (!$ok) { return false; }
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['username'] = (string)($u['username'] ?? $username);
        return true;
    }

    public static function logout(): void {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function currentUserId(): ?int {
        self::start();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function createUser(PDO $pdo, string $username, string $password, bool $status = true): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (username, password_hash, status) VALUES (?, ?, ?)');
        $stmt->execute([$username, $hash, $status]);
        try { return (int)$pdo->lastInsertId('usuarios_id_seq'); } catch (Throwable $e) { return (int)$pdo->lastInsertId(); }
    }
}

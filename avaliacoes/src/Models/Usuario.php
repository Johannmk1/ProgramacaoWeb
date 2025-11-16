<?php

class Usuario {
    public static function listar(PDO $pdo): array {
        $stmt = $pdo->query('SELECT id, username, status, created_at FROM usuarios ORDER BY id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function criar(PDO $pdo, string $username, string $password, bool $status = true): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (username, password_hash, status) VALUES (?, ?, ?)');
        $stmt->execute([$username, $hash, self::boolParam($status)]);
        try { return (int)$pdo->lastInsertId('usuarios_id_seq'); } catch (Throwable $e) { return (int)$pdo->lastInsertId(); }
    }

    public static function atualizar(PDO $pdo, int $id, ?string $username = null, ?bool $status = null): bool {
        $fields = [];
        $params = [];
        if ($username !== null) { $fields[] = 'username = ?'; $params[] = $username; }
        if ($status !== null)   { $fields[] = 'status = ?';   $params[] = self::boolParam($status); }
        if (empty($fields)) { return true; }
        $params[] = $id;
        $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function alterarSenha(PDO $pdo, int $id, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }

    public static function desativar(PDO $pdo, int $id, bool $excluir = false): bool {
        if ($excluir) {
            $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
            return $stmt->execute([$id]);
        }
        $stmt = $pdo->prepare('UPDATE usuarios SET status = FALSE WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function findByUsername(PDO $pdo, string $username): ?array {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, status FROM usuarios WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function total(PDO $pdo): int {
        return (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }
    private static function boolParam(bool $value): string {
        return $value ? 'true' : 'false';
    }
}


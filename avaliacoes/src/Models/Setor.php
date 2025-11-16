<?php

class Setor {
    public static function listar(PDO $pdo, bool $somenteAtivos = false): array {
        $sql = 'SELECT id, nome, status FROM setores' . ($somenteAtivos ? ' WHERE status = TRUE' : '') . ' ORDER BY nome ASC, id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function criar(PDO $pdo, string $nome, bool $status = true): int {
        $stmt = $pdo->prepare('INSERT INTO setores (nome, status) VALUES (?, ?)');
        $stmt->execute([$nome, self::boolParam($status)]);
        return (int)$pdo->lastInsertId('setores_id_seq');
    }

    public static function atualizar(PDO $pdo, int $id, ?string $nome = null, ?bool $status = null): bool {
        $fields = [];
        $params = [];
        if ($nome !== null) { $fields[] = 'nome = ?'; $params[] = $nome; }
        if ($status !== null) { $fields[] = 'status = ?'; $params[] = self::boolParam($status); }
        if (empty($fields)) { return true; }
        $params[] = $id;
        $sql = 'UPDATE setores SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function ativar(PDO $pdo, int $id, bool $ativo): bool {
        $stmt = $pdo->prepare('UPDATE setores SET status = ? WHERE id = ?');
        return $stmt->execute([self::boolParam($ativo), $id]);
    }

    public static function excluir(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare('DELETE FROM setores WHERE id = ?');
        return $stmt->execute([$id]);
    }
    private static function boolParam(bool $value): string {
        return $value ? 'true' : 'false';
    }
}


<?php

class Dispositivo {
    public static function listar(PDO $pdo, bool $somenteAtivos = false, bool $somenteSetorAtivo = false): array {
        $conditions = [];
        if ($somenteAtivos) { $conditions[] = 'd.status = TRUE'; }
        if ($somenteSetorAtivo) { $conditions[] = '(d.id_setor IS NULL OR s.status = TRUE)'; }
        $sql = 'SELECT d.id, d.nome, d.codigo, d.id_setor, d.status, s.nome AS setor_nome
                FROM dispositivos d
                LEFT JOIN setores s ON s.id = d.id_setor';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY d.nome ASC, d.id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function criar(PDO $pdo, string $nome, string $codigo, ?int $id_setor = null, bool $status = true): int {
        $stmt = $pdo->prepare('INSERT INTO dispositivos (nome, codigo, id_setor, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nome, $codigo, $id_setor, self::boolParam($status)]);
        return (int)$pdo->lastInsertId('dispositivos_id_seq');
    }

    public static function atualizar(PDO $pdo, int $id, ?string $nome = null, ?string $codigo = null, ?int $id_setor = null, ?bool $status = null): bool {
        // Mantido por compatibilidade legada: atualiza todos os campos passados explicitamente.
        $fields = [];
        $params = [];
        if ($nome !== null) { $fields[] = 'nome = ?'; $params[] = $nome; }
        if ($codigo !== null) { $fields[] = 'codigo = ?'; $params[] = $codigo; }
        if ($status !== null) { $fields[] = 'status = ?'; $params[] = self::boolParam($status); }
        // Atenção: este método não distingue "não enviado" de "enviado como null" para id_setor.
        // Prefira usar atualizarCampos() abaixo para updates seletivos.
        if (func_num_args() >= 4) { $fields[] = 'id_setor = ?'; $params[] = $id_setor; }
        if (empty($fields)) { return true; }
        $params[] = $id;
        $sql = 'UPDATE dispositivos SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function atualizarCampos(PDO $pdo, int $id, array $campos): bool {
        $map = [];
        $params = [];
        foreach (['nome','codigo','id_setor','status'] as $k) {
            if (array_key_exists($k, $campos)) {
                $map[] = "$k = ?";
                $params[] = ($k === 'status') ? self::boolParam((bool)$campos[$k]) : $campos[$k];
            }
        }
        if (empty($map)) { return true; }
        $params[] = $id;
        $sql = 'UPDATE dispositivos SET ' . implode(', ', $map) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function ativar(PDO $pdo, int $id, bool $ativo): bool {
        $stmt = $pdo->prepare('UPDATE dispositivos SET status = ? WHERE id = ?');
        return $stmt->execute([self::boolParam($ativo), $id]);
    }

    public static function excluir(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare('DELETE FROM dispositivos WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function findByCodigo(PDO $pdo, string $codigo): ?array {
        $sql = 'SELECT d.id, d.nome, d.codigo, d.id_setor, d.status, s.nome AS setor_nome
                FROM dispositivos d
                LEFT JOIN setores s ON s.id = d.id_setor
                WHERE d.codigo = ? LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    private static function boolParam(bool $value): string {
        return $value ? 'true' : 'false';
    }
}

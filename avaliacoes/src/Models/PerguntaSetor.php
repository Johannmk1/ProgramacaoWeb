<?php

class PerguntaSetor {
    public static function listarPerguntasComVinculo(PDO $pdo, int $id_setor): array {
        $sql = "SELECT p.id, p.texto, p.tipo, p.status, p.ordem,
                    CASE WHEN ps.id_pergunta IS NULL THEN FALSE ELSE TRUE END AS vinculada
                FROM perguntas p
                LEFT JOIN perguntas_setor ps ON ps.id_pergunta = p.id AND ps.id_setor = ?
                ORDER BY p.ordem ASC, p.id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_setor]);
        return $stmt->fetchAll();
    }

    public static function listarIdsPorSetor(PDO $pdo, int $id_setor): array {
        $stmt = $pdo->prepare('SELECT id_pergunta FROM perguntas_setor WHERE id_setor = ? ORDER BY id_pergunta ASC');
        $stmt->execute([$id_setor]);
        return array_map(fn($r) => (int)$r['id_pergunta'], $stmt->fetchAll());
    }

    public static function salvarMapeamento(PDO $pdo, int $id_setor, array $ids_perguntas): bool {
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM perguntas_setor WHERE id_setor = ?')->execute([$id_setor]);
            if (!empty($ids_perguntas)) {
                $ins = $pdo->prepare('INSERT INTO perguntas_setor (id_setor, id_pergunta) VALUES (?, ?)');
                foreach ($ids_perguntas as $pid) {
                    $ins->execute([$id_setor, (int)$pid]);
                }
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
}


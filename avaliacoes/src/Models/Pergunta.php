<?php

class Pergunta {
    private $id;
    private $texto;
    private $status;
    private $ordem;
    private $tipo;

    public function __construct($id, $texto, $status = true, $ordem = 0, string $tipo = 'nps') {
        $this->id = (int)$id;
        $this->texto = $texto;
        $this->status = (bool)$status;
        $this->ordem = (int)$ordem;
        $this->tipo = self::normalizeTipo($tipo);
    }

    public function getId() { return $this->id; }
    public function getTexto() { return $this->texto; }
    public function isAtiva() { return $this->status; }
    public function getOrdem() { return $this->ordem; }
    public function getTipo(): string { return $this->tipo; }

    public static function getAtivas(PDO $pdo) {
        $sql = 'SELECT id, texto, tipo, status, ordem FROM perguntas WHERE status = true ORDER BY ordem ASC, id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $itens = [];
        while ($r = $stmt->fetch()) {
            $itens[] = new self($r['id'], $r['texto'], $r['status'], $r['ordem'], $r['tipo'] ?? 'nps');
        }
        return $itens;
    }

    public static function listar(PDO $pdo, bool $somenteAtivas = false): array {
        $sql = 'SELECT id, texto, tipo, status, ordem FROM perguntas' . ($somenteAtivas ? ' WHERE status = TRUE' : '') . ' ORDER BY ordem ASC, id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function criar(PDO $pdo, string $texto, int $ordem = 0, bool $status = true, string $tipo = 'nps'): int {
        $stmt = $pdo->prepare('INSERT INTO perguntas (texto, tipo, status, ordem) VALUES (?, ?, ?, ?)');
        $stmt->execute([$texto, self::normalizeTipo($tipo), self::boolParam($status), $ordem]);
        try { return (int)$pdo->lastInsertId('perguntas_id_seq'); } catch (Throwable $e) { return (int)$pdo->lastInsertId(); }
    }

    public static function atualizar(PDO $pdo, int $id, ?string $texto = null, ?int $ordem = null, ?bool $status = null, ?string $tipo = null): bool {
        $fields = [];
        $params = [];
        if ($texto !== null) { $fields[] = 'texto = ?'; $params[] = $texto; }
        if ($ordem !== null) { $fields[] = 'ordem = ?'; $params[] = $ordem; }
        if ($status !== null) { $fields[] = 'status = ?'; $params[] = self::boolParam($status); }
        if ($tipo !== null) { $fields[] = 'tipo = ?'; $params[] = self::normalizeTipo($tipo); }
        if (empty($fields)) { return true; }
        $params[] = $id;
        $sql = 'UPDATE perguntas SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function excluir(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare('DELETE FROM perguntas WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function ativar(PDO $pdo, int $id, bool $ativo): bool {
        $stmt = $pdo->prepare('UPDATE perguntas SET status = ? WHERE id = ?');
        return $stmt->execute([$ativo, $id]);
    }

    public static function listarPorSetor(PDO $pdo, int $id_setor): array {
        $sql = "SELECT p.id, p.texto, p.tipo
                FROM perguntas p
                INNER JOIN perguntas_setor ps ON ps.id_pergunta = p.id AND ps.id_setor = ?
                WHERE p.status = TRUE
                ORDER BY p.ordem ASC, p.id ASC";
        $q = $pdo->prepare($sql);
        $q->execute([$id_setor]);
        return $q->fetchAll();
    }

    public static function listarPorDispositivo(PDO $pdo, string $codigoDispositivo): array {
        $sqlSetor = "SELECT d.id_setor
                     FROM dispositivos d
                     INNER JOIN setores s ON s.id = d.id_setor AND s.status = TRUE
                     WHERE d.codigo = ? AND d.status = TRUE
                     LIMIT 1";
        $st = $pdo->prepare($sqlSetor);
        $st->execute([$codigoDispositivo]);
        $id_setor = $st->fetchColumn();
        if ($id_setor) {
            return self::listarPorSetor($pdo, (int)$id_setor);
        }
        $stmt = $pdo->prepare('SELECT id, texto, tipo, status, ordem FROM perguntas WHERE status = TRUE ORDER BY ordem ASC, id ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function seedIfEmpty(PDO $pdo): void {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM perguntas')->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare('INSERT INTO perguntas (texto, tipo, status, ordem) VALUES (?, ?, TRUE, 1)');
            $stmt->execute(['Como você avalia nosso atendimento hoje?', 'nps']);
        }
    }

    private static function boolParam(bool $value): string {
        return $value ? 'true' : 'false';
    }

    private static function normalizeTipo(string $tipo): string {
        $tipo = strtolower(trim($tipo));
        return in_array($tipo, ['texto', 'nps'], true) ? $tipo : 'nps';
    }
}

<?php

class Pergunta {
    private $id;
    private $texto;
    private $status;
    private $ordem;

    public function __construct($id, $texto, $status = true, $ordem = 0) {
        $this->id = (int)$id;
        $this->texto = $texto;
        $this->status = (bool)$status;
        $this->ordem = (int)$ordem;
    }

    public function getId() { return $this->id; }
    public function getTexto() { return $this->texto; }
    public function isAtiva() { return $this->status; }
    public function getOrdem() { return $this->ordem; }

    public static function getAtivas(PDO $pdo) {
        $sql = 'SELECT id, texto, status, ordem FROM perguntas WHERE status = true ORDER BY ordem ASC, id ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $itens = [];
        while ($r = $stmt->fetch()) {
            $itens[] = new self($r['id'], $r['texto'], $r['status'], $r['ordem']);
        }
        return $itens;
    }

    public static function seedIfEmpty(PDO $pdo): void {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM perguntas')->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare('INSERT INTO perguntas (texto, status, ordem) VALUES (?, TRUE, 1)');
            $stmt->execute(['Como você avalia nosso atendimento hoje?']);
        }
    }
}

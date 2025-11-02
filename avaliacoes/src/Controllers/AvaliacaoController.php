<?php

require_once __DIR__ . '/../Models/Pergunta.php';
require_once __DIR__ . '/../Models/Avaliacao.php';

class AvaliacaoController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPerguntas(): array {
        return Pergunta::getAtivas($this->pdo);
    }

    public function salvarAvaliacao(array $respostas, ?string $feedback = null, ?string $id_dispositivo = null): bool {
        $ok = true;
        foreach ($respostas as $idPergunta => $valor) {
            $ok = $ok && Avaliacao::salvar($this->pdo, (int)$idPergunta, (int)$valor, $id_dispositivo, $feedback);
        }
        return $ok;
    }
}


<?php

class Avaliacao {
    public static function salvar(PDO $pdo, int $id_pergunta, int $resposta, ?string $id_dispositivo = null, ?string $feedback = null): bool {
        $sql = 'INSERT INTO avaliacoes (id_pergunta, id_dispositivo, resposta, feedback, data_hora) VALUES (?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $dataHora = date('Y-m-d H:i:s');
        return $stmt->execute([$id_pergunta, $id_dispositivo, $resposta, $feedback, $dataHora]);
    }
}


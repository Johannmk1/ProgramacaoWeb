<?php

class Avaliacao {
    public static function salvar(PDO $pdo, int $id_pergunta, int $resposta, ?string $id_dispositivo = null, ?string $feedback = null): bool {
        $sql = 'INSERT INTO avaliacoes (id_pergunta, id_dispositivo, resposta, feedback, data_hora) VALUES (?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $dataHora = date('Y-m-d H:i:s');
        return $stmt->execute([$id_pergunta, $id_dispositivo, $resposta, $feedback, $dataHora]);
    }

    public static function salvarLote(PDO $pdo, array $respostas, ?string $feedback = null, ?string $id_dispositivo = null): bool {
        if (empty($respostas)) { return false; }
        $sql = 'INSERT INTO avaliacoes (id_pergunta, id_dispositivo, resposta, feedback, data_hora) VALUES (?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $agora = date('Y-m-d H:i:s');
        $pdo->beginTransaction();
        try {
            foreach ($respostas as $idPergunta => $valor) {
                $ok = $stmt->execute([(int)$idPergunta, $id_dispositivo, (int)$valor, $feedback, $agora]);
                if (!$ok) { throw new Exception('Falha ao inserir avaliação'); }
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }
}

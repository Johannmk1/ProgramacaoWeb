<?php

class Avaliacao {
    public static function salvar(PDO $pdo, int $id_pergunta, $resposta, ?string $id_dispositivo = null): bool {
        $tipos = self::obterTiposPerguntas($pdo, [$id_pergunta]);
        $tipo = $tipos[$id_pergunta] ?? 'nps';
        [$valorNps, $valorTexto] = self::resolverValorResposta($tipo, $resposta);
        $sql = 'INSERT INTO avaliacoes (id_pergunta, id_dispositivo, resposta, resposta_texto, data_hora) VALUES (?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $dataHora = date('Y-m-d H:i:s');
        return $stmt->execute([$id_pergunta, $id_dispositivo, $valorNps, $valorTexto, $dataHora]);
    }

    public static function salvarLote(PDO $pdo, array $respostas, ?string $id_dispositivo = null): bool {
        if (empty($respostas)) { return false; }
        $ids = array_map('intval', array_keys($respostas));
        $tipos = self::obterTiposPerguntas($pdo, $ids);
        $sql = 'INSERT INTO avaliacoes (id_pergunta, id_dispositivo, resposta, resposta_texto, data_hora) VALUES (?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $agora = date('Y-m-d H:i:s');
        $pdo->beginTransaction();
        try {
            foreach ($respostas as $idPergunta => $valor) {
                $tipo = $tipos[(int)$idPergunta] ?? 'nps';
                [$valorNps, $valorTexto] = self::resolverValorResposta($tipo, $valor);
                $ok = $stmt->execute([(int)$idPergunta, $id_dispositivo, $valorNps, $valorTexto, $agora]);
                if (!$ok) { throw new Exception('Falha ao inserir avaliação'); }
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }

    private static function obterTiposPerguntas(PDO $pdo, array $ids): array {
        if (empty($ids)) { return []; }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, tipo FROM perguntas WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $tipos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tipos[(int)$row['id']] = strtolower($row['tipo'] ?? 'nps');
        }
        return $tipos;
    }

    private static function resolverValorResposta(string $tipo, $valor): array {
        if ($tipo === 'texto') {
            $texto = trim((string)$valor);
            return [null, $texto === '' ? null : $texto];
        }
        $numero = is_numeric($valor) ? (int)$valor : null;
        if ($numero !== null) {
            $numero = max(0, min(10, $numero));
        }
        return [$numero, null];
    }
}


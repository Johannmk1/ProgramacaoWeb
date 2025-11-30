<?php

require_once __DIR__ . '/Setor.php';
require_once __DIR__ . '/Dispositivo.php';
require_once __DIR__ . '/Pergunta.php';

class RelatorioBI {
    public static function gerar(PDO $pdo, array $inputFiltros = []): array {
        $filtros = self::normalizarFiltros($inputFiltros);
        $base = self::consultarBase($pdo, $filtros);
        $comentarios = self::listarComentarios($pdo, $filtros, $filtros['comentarios_pagina'], $filtros['comentarios_limite']);

        return [
            'filters' => [
                'inicio' => $filtros['inicio_date'],
                'fim' => $filtros['fim_date'],
                'setor' => $filtros['setor'],
                'dispositivo' => $filtros['dispositivo'],
                'pergunta' => $filtros['pergunta'],
            ],
            'kpis' => [
                'nps' => $base['nps'],
                'total' => $base['total'],
                'tempoMedio' => $base['tempo_medio'],
                'setorDestaque' => $base['setor_destaque'],
            ],
            'volume' => [
                'labels' => ['Promotores', 'Neutros', 'Detratores'],
                'valores' => [$base['promotores'], $base['neutros'], $base['detratores']],
            ],
            'npsSeries' => self::serieTemporal($pdo, $filtros),
            'topPerguntas' => self::topPerguntas($pdo, $filtros),
            'setores' => self::mapSetores(Setor::listar($pdo, true)),
            'dispositivos' => self::mapDispositivos(Dispositivo::listar($pdo, true, true)),
            'perguntasFiltro' => self::perguntasParaFiltro($pdo, $filtros),
            'chartSetores' => self::graficoSetores($pdo, $filtros),
            'chartPerguntas' => self::graficoPerguntas($pdo, $filtros),
            'comentarios' => $comentarios,
        ];
    }

    private static function consultarBase(PDO $pdo, array $filtros): array {
        $params = [];
        $where = self::buildWhere($filtros, $params);
        $sql = "SELECT
                    COUNT(*) AS total_registros,
                    COUNT(a.resposta) AS total_nps,
                    SUM(CASE WHEN a.resposta >= 9 THEN 1 ELSE 0 END) AS promotores,
                    SUM(CASE WHEN a.resposta BETWEEN 7 AND 8 THEN 1 ELSE 0 END) AS neutros,
                    SUM(CASE WHEN a.resposta IS NOT NULL AND a.resposta <= 6 THEN 1 ELSE 0 END) AS detratores,
                    MIN(a.data_hora) AS primeiro_registro,
                    MAX(a.data_hora) AS ultimo_registro
                FROM avaliacoes a
                LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                WHERE {$where}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($row['total_registros'] ?? 0);
        $totalNps = (int)($row['total_nps'] ?? 0);
        $promotores = (int)($row['promotores'] ?? 0);
        $neutros = (int)($row['neutros'] ?? 0);
        $detratores = (int)($row['detratores'] ?? 0);
        $tempoMedio = self::calcularTempoMedio($row['primeiro_registro'] ?? null, $row['ultimo_registro'] ?? null, $total);

        return [
            'total' => $total,
            'promotores' => $promotores,
            'neutros' => $neutros,
            'detratores' => $detratores,
            'nps' => self::calcularNps($promotores, $detratores, $totalNps),
            'tempo_medio' => $tempoMedio,
            'setor_destaque' => self::obterSetorDestaque($pdo, $filtros),
        ];
    }

    private static function serieTemporal(PDO $pdo, array $filtros): array {
        $params = [];
        $where = self::buildWhere($filtros, $params);
        $sql = "SELECT
                    DATE(a.data_hora) AS dia,
                    SUM(CASE WHEN a.resposta >= 9 THEN 1 ELSE 0 END) AS promotores,
                    SUM(CASE WHEN a.resposta IS NOT NULL AND a.resposta <= 6 THEN 1 ELSE 0 END) AS detratores,
                    COUNT(a.resposta) AS total
                FROM avaliacoes a
                LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                WHERE {$where} AND a.resposta IS NOT NULL
                GROUP BY dia
                ORDER BY dia ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[$r['dia']] = [
                'promotores' => (int)$r['promotores'],
                'detratores' => (int)$r['detratores'],
                'total' => (int)$r['total'],
            ];
        }

        $labels = [];
        $valores = [];
        $cursor = new DateTimeImmutable($filtros['inicio_date']);
        $fim = new DateTimeImmutable($filtros['fim_date']);
        while ($cursor <= $fim) {
            $key = $cursor->format('Y-m-d');
            $dados = $rows[$key] ?? ['promotores' => 0, 'detratores' => 0, 'total' => 0];
            $valor = self::calcularNps($dados['promotores'], $dados['detratores'], $dados['total']);
            $labels[] = $cursor->format('d/m');
            $valores[] = $valor;
            $cursor = $cursor->modify('+1 day');
        }

        return ['labels' => $labels, 'valores' => $valores];
    }

    private static function topPerguntas(PDO $pdo, array $filtros): array {
        $params = [];
        $where = self::buildWhere($filtros, $params);
        $sql = "SELECT
                    p.texto,
                    AVG(a.resposta) AS media,
                    COUNT(a.resposta) AS respostas
                FROM avaliacoes a
                INNER JOIN perguntas p ON p.id = a.id_pergunta
                LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                WHERE {$where} AND a.resposta IS NOT NULL AND p.tipo = 'nps'
                GROUP BY p.id, p.texto
                HAVING COUNT(a.resposta) > 0
                ORDER BY respostas DESC, media DESC
                LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'pergunta' => (string)$row['texto'],
                'media' => round((float)$row['media'], 1),
                'respostas' => (int)$row['respostas'],
            ];
        }
        return $result;
    }

    private static function obterSetorDestaque(PDO $pdo, array $filtros): string {
        $params = [];
        $where = self::buildWhere($filtros, $params);
        $sql = "SELECT
                    COALESCE(s.nome, 'Sem setor') AS nome,
                    SUM(CASE WHEN a.resposta >= 9 THEN 1 ELSE 0 END) AS promotores,
                    SUM(CASE WHEN a.resposta IS NOT NULL AND a.resposta <= 6 THEN 1 ELSE 0 END) AS detratores,
                    COUNT(a.resposta) AS total
                FROM avaliacoes a
                LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                LEFT JOIN setores s ON s.id = d.id_setor
                WHERE {$where} AND a.resposta IS NOT NULL
                GROUP BY s.id, s.nome
                HAVING COUNT(a.resposta) > 0
                ORDER BY ( (SUM(CASE WHEN a.resposta >= 9 THEN 1 ELSE 0 END) - SUM(CASE WHEN a.resposta IS NOT NULL AND a.resposta <= 6 THEN 1 ELSE 0 END)) * 100.0 / NULLIF(COUNT(a.resposta), 0) ) DESC,
                         COUNT(a.resposta) DESC
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && !empty($row['nome']) ? (string)$row['nome'] : '--';
    }

    private static function graficoSetores(PDO $pdo, array $filtros): array {
        $params = [];
        $where = self::buildWhere($filtros, $params);
        $sql = "SELECT
                    COALESCE(s.nome, 'Sem setor') AS nome,
                    SUM(CASE WHEN a.resposta >= 9 THEN 1 ELSE 0 END) AS promotores,
                    SUM(CASE WHEN a.resposta IS NOT NULL AND a.resposta <= 6 THEN 1 ELSE 0 END) AS detratores,
                    COUNT(a.resposta) AS total
                FROM avaliacoes a
                LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                LEFT JOIN setores s ON s.id = d.id_setor
                WHERE {$where} AND a.resposta IS NOT NULL
                GROUP BY s.id, s.nome
                HAVING COUNT(a.resposta) > 0
                ORDER BY total DESC, nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $promotores = (int)($row['promotores'] ?? 0);
            $detratores = (int)($row['detratores'] ?? 0);
            $total = (int)($row['total'] ?? 0);
            $result[] = [
                'nome' => (string)$row['nome'],
                'nps' => self::calcularNps($promotores, $detratores, $total),
                'total' => $total,
                'promotores' => $promotores,
                'detratores' => $detratores,
            ];
        }
        return $result;
    }

    private static function graficoPerguntas(PDO $pdo, array $filtros): array {
        $params = [];
        $where = self::buildWhere($filtros, $params);
        $sql = "SELECT
                    p.texto,
                    AVG(a.resposta) AS media,
                    COUNT(a.resposta) AS respostas
                FROM avaliacoes a
                INNER JOIN perguntas p ON p.id = a.id_pergunta
                LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                WHERE {$where} AND a.resposta IS NOT NULL AND p.tipo = 'nps'
                GROUP BY p.id, p.texto
                HAVING COUNT(a.resposta) > 0
                ORDER BY respostas DESC, media DESC
                LIMIT 8";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'pergunta' => (string)$row['texto'],
                'media' => round((float)$row['media'], 2),
                'respostas' => (int)$row['respostas'],
            ];
        }
        return $result;
    }

    private static function perguntasParaFiltro(PDO $pdo, array $filtros): array {
        if (!empty($filtros['setor'])) {
            $lista = Pergunta::listarPorSetor($pdo, (int)$filtros['setor']);
        } else {
            $lista = Pergunta::listar($pdo, true);
        }
        if (!is_array($lista)) { return []; }
        return array_map(static fn($row) => [
            'id' => (int)$row['id'],
            'texto' => (string)$row['texto'],
        ], $lista);
    }

    private static function listarComentarios(PDO $pdo, array $filtros, int $pagina, int $limite): array {
        $pagina = max($pagina, 1);
        $limite = max($limite, 1);
        $offset = ($pagina - 1) * $limite;

        $paramsCount = [];
        $whereBase = self::buildWhere($filtros, $paramsCount);
        $whereTexto = $whereBase . " AND a.resposta_texto IS NOT NULL AND TRIM(a.resposta_texto) <> ''";

        $sqlCount = "SELECT COUNT(*) FROM avaliacoes a
                     LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                     WHERE {$whereTexto}";
        $stmtCount = $pdo->prepare($sqlCount);
        self::bindWhereParams($stmtCount, $paramsCount);
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();
        $totalPages = $limite > 0 ? (int)ceil($total / $limite) : 1;
        if ($totalPages < 1 && $total === 0) { $totalPages = 1; }

        $paramsData = [];
        $whereData = self::buildWhere($filtros, $paramsData);
        $whereData .= " AND a.resposta_texto IS NOT NULL AND TRIM(a.resposta_texto) <> ''";

        $sql = "SELECT
                    a.resposta_texto,
                    a.data_hora,
                    p.texto AS pergunta,
                    d.nome AS dispositivo_nome,
                    d.codigo AS dispositivo_codigo,
                    s.nome AS setor_nome
                FROM avaliacoes a
                LEFT JOIN perguntas p ON p.id = a.id_pergunta
                LEFT JOIN dispositivos d ON d.codigo = a.id_dispositivo
                LEFT JOIN setores s ON s.id = d.id_setor
                WHERE {$whereData}
                ORDER BY a.data_hora DESC
                LIMIT :limite OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        self::bindWhereParams($stmt, $paramsData);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $texto = trim((string)($row['resposta_texto'] ?? ''));
            if ($texto === '') { continue; }
            $items[] = [
                'texto' => $texto,
                'data' => (string)($row['data_hora'] ?? ''),
                'pergunta' => (string)($row['pergunta'] ?? ''),
                'setor' => $row['setor_nome'] ?? null,
                'dispositivo' => $row['dispositivo_nome'] ?? null,
                'dispositivoCodigo' => $row['dispositivo_codigo'] ?? null,
            ];
        }

        $hasNext = $total > 0 && $pagina < $totalPages;
        $hasPrev = $pagina > 1;

        return [
            'items' => $items,
            'total' => $total,
            'page' => $pagina,
            'perPage' => $limite,
            'totalPages' => $totalPages,
            'hasNext' => $hasNext,
            'hasPrev' => $hasPrev,
            'hasMore' => $hasNext,
        ];
    }

    private static function normalizarFiltros(array $input): array {
        $hoje = new DateTimeImmutable('today');
        $inicio = self::sanitizeDate($input['inicio'] ?? null);
        $fim = self::sanitizeDate($input['fim'] ?? null);

        if ($inicio && !$fim) {
            $fim = $inicio->modify('+29 days');
        } elseif (!$inicio && $fim) {
            $inicio = $fim->modify('-29 days');
        } elseif (!$inicio && !$fim) {
            $fim = $hoje;
            $inicio = $hoje->modify('-29 days');
        }

        if (!$inicio) { $inicio = $hoje->modify('-29 days'); }
        if (!$fim) { $fim = $hoje; }

        if ($inicio > $fim) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        return [
            'inicio_date' => $inicio->format('Y-m-d'),
            'fim_date' => $fim->format('Y-m-d'),
            'inicio_sql' => $inicio->format('Y-m-d 00:00:00'),
            'fim_sql' => $fim->format('Y-m-d 23:59:59'),
            'setor' => self::sanitizeSetor($input['setor'] ?? null),
            'dispositivo' => self::sanitizeDispositivo($input['dispositivo'] ?? null),
            'pergunta' => self::sanitizePergunta($input['pergunta'] ?? null),
            'comentarios_pagina' => self::sanitizePage($input['page_textos'] ?? 1),
            'comentarios_limite' => self::sanitizePerPage($input['per_page_textos'] ?? 5),
        ];
    }

    private static function sanitizeDate(?string $value): ?DateTimeImmutable {
        if (!$value) { return null; }
        $trimmed = substr((string)$value, 0, 10);
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $trimmed);
        return $dt ?: null;
    }

    private static function sanitizeSetor($value): ?int {
        $int = (int)$value;
        return $int > 0 ? $int : null;
    }

    private static function sanitizeDispositivo($value): ?string {
        if ($value === null) { return null; }
        $str = trim((string)$value);
        if ($str === '') { return null; }
        return substr($str, 0, 64);
    }

    private static function sanitizePergunta($value): ?int {
        $int = (int)$value;
        return $int > 0 ? $int : null;
    }

    private static function sanitizePage($value): int {
        $num = (int)$value;
        return $num > 0 ? $num : 1;
    }

    private static function sanitizePerPage($value): int {
        $num = (int)$value;
        if ($num < 1) { return 5; }
        if ($num > 20) { return 20; }
        return $num;
    }

    private static function buildWhere(array $filtros, array &$params): string {
        $conditions = [];
        if (!empty($filtros['inicio_sql'])) {
            $conditions[] = 'a.data_hora >= :inicio';
            $params[':inicio'] = $filtros['inicio_sql'];
        }
        if (!empty($filtros['fim_sql'])) {
            $conditions[] = 'a.data_hora <= :fim';
            $params[':fim'] = $filtros['fim_sql'];
        }
        if (!empty($filtros['setor'])) {
            $conditions[] = 'd.id_setor = :setor';
            $params[':setor'] = $filtros['setor'];
        }
        if (!empty($filtros['dispositivo'])) {
            $conditions[] = 'a.id_dispositivo = :dispositivo';
            $params[':dispositivo'] = $filtros['dispositivo'];
        }
        if (!empty($filtros['pergunta'])) {
            $conditions[] = 'a.id_pergunta = :pergunta';
            $params[':pergunta'] = (int)$filtros['pergunta'];
        }
        return $conditions ? implode(' AND ', $conditions) : '1=1';
    }

    private static function calcularNps(int $promotores, int $detratores, int $total): float {
        if ($total <= 0) { return 0.0; }
        $valor = (($promotores - $detratores) / $total) * 100;
        return round($valor, 1);
    }

    private static function calcularTempoMedio(?string $inicio, ?string $fim, int $totalRegistros): int {
        if (!$inicio || !$fim || $totalRegistros <= 1) { return 0; }
        $start = strtotime($inicio);
        $end = strtotime($fim);
        if ($start === false || $end === false || $end <= $start) { return 0; }
        $intervalo = $end - $start;
        return (int)round($intervalo / max($totalRegistros - 1, 1));
    }

    private static function mapSetores(array $rows): array {
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => (int)$row['id'],
                'nome' => (string)$row['nome'],
            ];
        }
        return $result;
    }

    private static function mapDispositivos(array $rows): array {
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => (int)$row['id'],
                'nome' => (string)$row['nome'],
                'codigo' => (string)$row['codigo'],
                'setor_nome' => $row['setor_nome'] ?? null,
            ];
        }
        return $result;
    }

    private static function bindWhereParams(\PDOStatement $stmt, array $params): void {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    }
}

<?php
        header('Content-Type: application/json; charset=utf-8');

        require_once __DIR__ . '/../src/Config/Database.php';

        $pdo = (new Database())->connect();
        try { $pdo->exec("SET client_encoding TO 'UTF8'"); } catch (Throwable $e) {}

        $ativos = isset($_GET['ativos']) && $_GET['ativos'] == '1';
        $sql = 'SELECT d.id, d.nome, d.codigo, d.id_setor, d.status, s.nome AS setor_nome
                FROM dispositivos d LEFT JOIN setores s ON s.id = d.id_setor';
        if ($ativos) { $sql .= ' WHERE d.status = TRUE'; }
        $sql .= ' ORDER BY d.nome ASC, d.id ASC';
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);


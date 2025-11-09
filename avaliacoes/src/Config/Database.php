<?php

class Database {
    private $conn;

    public function connect() {
        if ($this->conn) { return $this->conn; }

        $driver = getenv('DB_DRIVER') ?: 'pgsql';
        $host   = getenv('DB_HOST') ?: 'localhost';
        $name   = getenv('DB_NAME') ?: 'SistemaAvaliacao';
        $user   = getenv('DB_USER') ?: 'postgres';
        $pass   = getenv('DB_PASS') ?: 'unidavi';
        $port   = getenv('DB_PORT') ?: ($driver === 'pgsql' ? '5432' : '3306');

        try {
            if ($driver === 'pgsql') {
                $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
            } else {
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            }

            $this->conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            try {
                if ($driver === 'pgsql') {
                    $this->conn->exec("SET client_encoding TO 'UTF8'");
                } else {
                    $this->conn->exec("SET NAMES 'utf8mb4'");
                }
            } catch (Throwable $e) {}
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['status' => 'error', 'message' => 'Erro de conexão ao banco.']));
        }

        return $this->conn;
    }
}

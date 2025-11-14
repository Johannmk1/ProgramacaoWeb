<?php

class Database {
    private $conn;

    public function connect() {
        if ($this->conn) { return $this->conn; }

        $host   = getenv('DB_HOST') ?: 'localhost';
        $name   = getenv('DB_NAME') ?: 'SistemaAvaliacao';
        $user   = getenv('DB_USER') ?: 'postgres';
        $pass   = getenv('DB_PASS') ?: 'unidavi';
        $port   = getenv('DB_PORT') ?: '5432';

        try {
            $dsn = "pgsql:host={$host};port={$port};dbname={$name}";

            $this->conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            try {
                $this->conn->exec("SET client_encoding TO 'UTF8'");
            } catch (Throwable $e) {}
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['status' => 'error', 'message' => 'Erro de conexão ao banco.']));
        }

        return $this->conn;
    }
}

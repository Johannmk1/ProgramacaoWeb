<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_USER', 'postgres');
define('DB_PASS', 'unidavi');
define('DB_NAME', 'local');

function conectarBanco() {
    $connectionString = "host=" . DB_HOST .
                        " port=" . DB_PORT .
                        " dbname=" . DB_NAME .
                        " user=" . DB_USER .
                        " password=" . DB_PASS;

    $conn = pg_connect($connectionString);

    if (!$conn) {
        die("Erro ao conectar ao banco de dados.");
    }
    return $conn;
}

function inserirPessoa($conn, $dados) {
    $sql = "INSERT INTO TBPESSOA (PESNOME, PESSOBRENOME, PESEMAIL, PESPASSWORD, PESCIDADE, PESESTADO)
            VALUES ($1, $2, $3, $4, $5, $6)";
    return pg_query_params($conn, $sql, $dados);
}

function listarPessoas($conn) {
    $sql = "SELECT PESNOME, PESSOBRENOME, PESEMAIL, PESPASSWORD, PESCIDADE, PESESTADO FROM TBPESSOA";
    return pg_query($conn, $sql);
}

function salvarTxt($aDados) {
    $linha = implode(';', $aDados) . PHP_EOL;
    file_put_contents('pessoas.txt', $linha, FILE_APPEND);
}

function salvarJson($aDados) {
    $arquivo = 'cadastro_pessoa.json';
    $dados = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];
    $dados[] = $aDados;
    file_put_contents($arquivo, json_encode($dados));
}
?>

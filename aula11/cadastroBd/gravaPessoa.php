<?php
    if (isset($_POST)) {
        $pessoaJson = json_encode($_POST) . PHP_EOL;
        file_put_contents('cadastro_pessoa.json', $pessoaJson, FILE_APPEND);
    }
?>
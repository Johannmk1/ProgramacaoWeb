<?php 
    require_once "pessoa.php";

    $pessoaJohann = new pessoa();

    $pessoaJohann->nome = "Johann";
    $pessoaJohann->sobrenome = "Malkowski";

    // $pessoaJohann->getIdade();
    echo $pessoaJohann->getNomeCompleto()."<br>";
    echo $pessoaJohann->getdataInstancia()."<br>";

?>
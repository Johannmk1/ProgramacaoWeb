<?php 
    require_once "model/pessoa.php";

    $pessoaJohann = new pessoa();
    $pessoaJohann->setTipo(1);
    $pessoaJohann->setNome("Johann");
    $pessoaJohann->setSobrenome("Malkowski");
    $pessoaJohann->setDataNascimento(new datetime("2006-05-27"));
    $pessoaJohann->setCpfCnpj("156.856.485-15");
    $pessoaJohann->setTelefone("(47) 99919-5754)");
    $pessoaJohann->setEndereco("Rua Gustavo Hasse");

    
    echo $pessoaJohann->getNomeCompleto()."<br>";
    echo $pessoaJohann->getIdade()."<br>";
    echo $pessoaJohann->getDataNascimento()->format('d/m/Y')."<br>";
    echo $pessoaJohann->getCpfCnpj()."<br>";
    echo $pessoaJohann->getTelefone()."<br>";
    echo $pessoaJohann->getEndereco()."<br>";

?>
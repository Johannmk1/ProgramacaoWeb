<?php 
    require_once "model/calculadora.php";
    require_once "model/computador.php";

    $calc = new calculadora();
    $calc->setnumero1(4);
    $calc->setnumero2(2);
 
    echo $calc->calculaSubtracao()."<br>";
    echo $calc->calculaAdicao()."<br>";
    echo $calc->calculaDivisao()."<br>";
    echo $calc->calculaMultiplicacao()."<br><br><br>";

    $pc = new computador();

    echo $pc->getStatus()."<br>";
    echo $pc->ligar();
    echo $pc->getStatus()."<br>";
    echo $pc->desligar();
    echo $pc->getStatus()."<br>";
?>
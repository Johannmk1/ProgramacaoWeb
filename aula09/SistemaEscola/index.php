<?php
    require_once("funcoes.php");
    $notas = array (10,8,9,10,9.50);
    $faltas = array(1,1,1,1,0,1,1,0,0,1,1,1,1,1,0); 

    try {
        echo "Média: " . calcMedia($notas) . "<br>";
        echo "Aprovado por nota: " . (aprovadoNotas($notas) ? "Sim" : "Não") . "<br>";
        echo "Frequência: " . calcFrequencia($faltas) . "%<br>";
        echo "Aprovado por frequência: " . (aprovadoFrequencia($faltas) ? "Sim" : "Não") . "<br>";
    } catch (\Throwable $excecao) {
        echo $excecao->getMessage();   
    } 
?>


   
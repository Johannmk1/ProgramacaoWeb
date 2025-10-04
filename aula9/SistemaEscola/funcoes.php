<?php
 function calcMedia($n) {
    if (!is_array($n) || empty($n)) {
        throw new Exception("Notas inválidas: deve ser um array não vazio.");
    }
    return array_sum($n) / count($n); 
}

function calcFrequencia($a){
    if (!is_array($a) || empty($a)) {
        throw new Exception("Frequência inválida: deve ser um array não vazio.");
    }   
    return ((array_count_values($a)[1] / count($a))) * 100; 
}

function aprovadoNotas($n){
    return calcMedia($n) >= 7; 
}

function aprovadofrequencia($a){
    return calcFrequencia($a) > 70;
}
?>
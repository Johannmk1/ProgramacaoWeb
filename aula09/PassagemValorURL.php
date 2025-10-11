<?php
    $valor = $_GET['valor'];
    $desconto = $_GET['desconto'];

    $valorComDesconto = $valor - $desconto;

    Echo "Valor: $valor <br>";
    Echo "Desconto: $desconto <br>";
    Echo "Valor com desconto: $valorComDesconto <br>";
?>
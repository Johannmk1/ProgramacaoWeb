<?php
    $salario1 = 1000;
    $salario2 = 2000;

    $salario2 = $salario1;

    $salario2++;

    $salario1 *= 1.1;

    echo "Salário1:  $salario1, Salário2: $salario2";

    echo"<br>";
    echo"<br>";

    if ($salario1 > $salario2) {    
        echo "O valor da variável é MAIOR que da variável2";
    } else if ($salario1 < $salario2) {
        echo "O valor da variável é MENOR que da variável2";
    } else {
        echo "Os valores são iguais";
    }

    echo"<br>";
    echo"<br>";
    
    $status = array("Ótimo", "Muito Bom", "Bom");
    foreach ($status as $valor) {
    echo "$valor <br>";
    }

    echo"<br>";
    echo"<br>";

    for ($i = 0; $i < 100; ++$i) {
        $salario1++;
        
        if ($salario1 == 49) {
            break;
        }
    }

    if ($salario1 < $salario2) {
        echo "Salário1: ".$salario1;
    }

    $idade = array("João"=>"35", "Maria"=>"37", "José"=>"43");
    foreach($idade as $chave => $valor) {
    echo "Chave=" . $chave . ", Valor=" . $valor;
    echo "<br>";
    }

    echo"<br>";
    echo"<br>";
?>
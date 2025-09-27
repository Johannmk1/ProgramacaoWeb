<?php
    $displasias = array("Estrutura de dados II", "Banco de Dados II", 
                        "Administração", "Engenharia de Software", "Programação Web");
    $professores = array("Bastos", "Marco", "Marciel", "Jiulin", "Clebinho");

    for ($i = 0; $i < count($displasias); $i++) {
        echo "Displasias: ". $displasias[$i] .", Professores: ".$professores[$i]."<BR>";
    }
?>
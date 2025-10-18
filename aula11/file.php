<?php
    define('arquivo', 'dados.txt');
    define('arquivo2', 'dados2.txt');

    if (file_exists(arquivo)) {
        echo "O arquivo esxiste.<br>";

        $conteudo = file_get_contents(arquivo);
        echo "Conteudo do arquico: <br>";
        echo nl2br($conteudo);

        $conteudoNovo = serialize($Conteudo);
        file_put_contents(arquivo2, $conteudoNovo);
        echo "<br> Conteúdo escrito no novo arquivo 'dados2.txt'.";
    }
?>
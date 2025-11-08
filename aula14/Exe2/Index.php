<?php 
    require_once "model/time.php";

    $t = new time();
    $t->setNome('Foda');
    $t->setAnoFundacao(1892);
    $t->AddJogadores('Johann','Lateral',2000);
    $t->AddJogadores('Bruno','Goleiro', 2012);

    $t->getJogadores();

?>
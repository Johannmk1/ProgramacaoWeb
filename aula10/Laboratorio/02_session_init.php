<?php

    session_status();
    if(!isset($_SESSION['usuario'])) {
        $_SESSION['usuario'] = 'Visitante';       
    }
    echo 'Ola, ' . $_SESSION['usuario'] .'! Você está logado.<br>'; 
    echo '<a href="02_session_continua.php"> Clique aqui para login </a>'; 
?>
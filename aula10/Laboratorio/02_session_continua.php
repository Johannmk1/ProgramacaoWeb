<?php
    session_start();
    echo "Olá, " . $_SESSION['usuario'] . "Você está logado."; 
    session_destroy();
?>
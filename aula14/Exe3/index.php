<?php
    $session = new Session();
    if($session->iniciaSessao()){
        echo "Sessão iniciada com sucesso";

        if(!$session->getUsuarioSessao()) {

        }
        $usuario = new Usuario();
        $usuario->setNome();
        $usuario->setLogin();
        $usuario->setPass();
        $session->
    } else {
        echo"Falha ao iniciar a Sessão";
        $session->finalizaSessao();
    }
?>
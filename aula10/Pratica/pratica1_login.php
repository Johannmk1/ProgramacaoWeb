<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    $_SESSION['usuario'] = $_POST['login'];
    $_SESSION['senha'] = $_POST['senha'];
    $_SESSION['inicio_sessao'] = date('Y-m-d H:i:s');
    echo 'Sessão iniciada e usuário registrado.';
} else {
    $inicio = new DateTime($_SESSION['inicio_sessao']);
    $agora = new DateTime();

    $diferenca = $inicio->diff($agora);

    if ($diferenca->format('%H:%I:%S') < ' 00:02:00') {
        if (!isset($_COOKIE['usuario'])) {
            setcookie("usuario",$_SESSION['usuario'],time()+(60*5),"/");
            setcookie("tempoLogado",$diferenca->format('%H:%I:%S'),time()+(60*5),"/");
        }

        echo "Usuário já logado: " . $_SESSION['usuario'] . "<br>";
        echo "Primeira Requisição: " . $inicio->format('d/m/Y H:i:s') . "<br>";
        echo "Última Requisição: " . $agora->format('d/m/Y H:i:s') . "<br>";
        echo "Tempo de Sessão: " . $diferenca->format('%H:%I:%S') . "<br>";
    } else {
        echo "A sessão foi pro caralho" . "<br>";
        session_destroy();     
    }
}

echo '<a href="pratica1_login.php">Recarregar</a>';
?>

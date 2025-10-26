<link rel="stylesheet" href="style.css">

<?php
include 'funcoes.php';

$conn = conectarBanco();

$nome      = $_POST['nome']      ?? '';
$sobrenome = $_POST['sobrenome'] ?? '';
$email     = $_POST['email']     ?? '';
$senha     = $_POST['senha']     ?? '';
$cidade    = $_POST['cidade']    ?? '';
$estado    = $_POST['estado']    ?? '';

$aDados = [$nome, $sobrenome, $email, $senha, $cidade, $estado];

if (inserirPessoa($conn, $aDados)) {
    echo "Dados inseridos com sucesso!";
} else {
    echo "Erro ao inserir os dados.";
}

echo '<br><br><a href="cadastro.html">Voltar</a>';
?>

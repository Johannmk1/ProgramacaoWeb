<link rel="stylesheet" href="style.css">

<?php
include 'funcoes.php';

$acao = $_POST['acao'] ?? '';
$nome = $_POST['nome'] ?? '';
$sobrenome = $_POST['sobrenome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$cidade = $_POST['cidade'] ?? '';
$estado = $_POST['estado'] ?? '';

$aDados = [$nome, $sobrenome, $email, $senha, $cidade, $estado];

if ($acao === "Salvar no Banco") {
    $conn = conectarBanco();
    if (inserirPessoa($conn, $aDados)) {
        echo "Dados salvos no banco com sucesso!";
    } else {
        echo "Erro ao salvar no banco.";
    }
    pg_close($conn);

} elseif ($acao === "Salvar em Arquivo") {
    salvarTxt($aDados);
    salvarJson($aDados);
    echo "Dados salvos com sucesso!";
}

echo '<br><br><a href="cadastro.html">Voltar</a>';
?>

<?php
include 'funcoes.php';

$conn = conectarBanco();
$result = listarPessoas($conn);

echo "<h2>Lista de Pessoas Cadastradas</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr>
        <th>Nome</th>
        <th>Sobrenome</th>
        <th>E-mail</th>
        <th>Senha</th>
        <th>Cidade</th>
        <th>Estado</th>
      </tr>";

while ($row = pg_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['pesnome']}</td>
            <td>{$row['pessobrenome']}</td>
            <td>{$row['pesemail']}</td>
            <td>{$row['pespassword']}</td>
            <td>{$row['pescidade']}</td>
            <td>{$row['pesestado']}</td>
          </tr>";
}

echo "</table>";
echo "<br><br><a href='cadastro.html'>Voltar</a>";
?>

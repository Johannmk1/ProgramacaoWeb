<?php
include 'funcoes.php';
$conn = conectarBanco();

$busca = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_STRING);

if (!empty($busca)) {
    $sql = "SELECT * FROM TBPESSOA WHERE PESNOME ILIKE $1";
    $params = ['%' . $busca . '%'];
    $result = pg_query_params($conn, $sql, $params);
} else {
    $result = listarPessoas($conn);
}
?>

<form method="get">
    <label for="busca">Buscar por nome:</label>
    <input type="text" name="busca" id="busca" value="<?= htmlspecialchars($busca ?? '') ?>">
    <button type="submit">Buscar</button>
    <link rel="stylesheet" href="style.css">
</form>

<h2>Lista de Pessoas Cadastradas</h2>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Nome</th>
        <th>Sobrenome</th>
        <th>E-mail</th>
        <th>Senha</th>
        <th>Cidade</th>
        <th>Estado</th>
    </tr>
    <?php while ($row = pg_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['pesnome']) ?></td>
            <td><?= htmlspecialchars($row['pessobrenome']) ?></td>
            <td><?= htmlspecialchars($row['pesemail']) ?></td>
            <td><?= htmlspecialchars($row['pespassword']) ?></td>
            <td><?= htmlspecialchars($row['pescidade']) ?></td>
            <td><?= htmlspecialchars($row['pesestado']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<br><br>
<a href="cadastro.html">Voltar</a>

<?php pg_close($conn); ?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 8</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 8</h2>

    <form method="post">
      <label>
        Preço à vista (R$):
        <input type="number" name="preco" step="0.01" min="0" 
               value="<?= $_POST['preco'] ?? '8654.00' ?>" required>
      </label>
      <label>
        Quantidade de parcelas:
        <select id="qtdParce" name="qtdParce" required>
          <option value="">Selecione</option>
          <option value="24" <?= (isset($_POST['qtdParce']) && $_POST['qtdParce'] == 24) ? 'selected' : '' ?>>24 vezes</option>
          <option value="36" <?= (isset($_POST['qtdParce']) && $_POST['qtdParce'] == 36) ? 'selected' : '' ?>>36 vezes</option>
          <option value="48" <?= (isset($_POST['qtdParce']) && $_POST['qtdParce'] == 48) ? 'selected' : '' ?>>48 vezes</option>
          <option value="60" <?= (isset($_POST['qtdParce']) && $_POST['qtdParce'] == 60) ? 'selected' : '' ?>>60 vezes</option>
        </select>
      </label>

      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
  <div class="output">
    <?php
    $preco = isset($_POST['preco']) ? floatval($_POST['preco']) : 0;
    $qtdParce = isset($_POST['qtdParce']) ? intval($_POST['qtdParce']) : 0;

    if ($preco > 0 && $qtdParce > 0) {
        switch ($qtdParce) {
            case 24: $taxa = 1.5; break;
            case 36: $taxa = 2.0; break;
            case 48: $taxa = 2.5; break;
            case 60: $taxa = 3.0; break;
            default: $taxa = 0; break;
        }

        $juros = $preco * ($taxa / 100) * ($qtdParce / 12);
        $total = $preco + $juros;
        $valorParcela = $total / $qtdParce;

        echo "<h3>Resumo do financiamento:</h3>";
        echo "<p>Preço à vista: <b>R$ " . number_format($preco, 2, ',', '.') . "</b></p>";
        echo "<p>Quantidade de parcelas: <b>{$qtdParce}</b></p>";
        echo "<p>Taxa de juros simples: <b>{$taxa}% ao mês</b></p>";
        echo "<p>Valor total financiado: <b>R$ " . number_format($total, 2, ',', '.') . "</b></p>";
        echo "<p>Valor de cada parcela: <b style='color:blue;'>R$ " . number_format($valorParcela, 2, ',', '.') . "</b></p>";
        echo "<hr>";
        echo "<p style='color:red;'><b>Juros totais pagos: R$ " . number_format($juros, 2, ',', '.') . "</b></p>";

    } else {
        echo "<p style='color:red;'>Preencha todos os campos corretamente.</p>";
    }
    ?>
  </div>
<?php endif; ?>

    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

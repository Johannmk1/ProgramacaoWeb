<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 7</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 7</h2>

    <form method="post">
      <label>
        Preço à vista (R$):
        <input type="number" name="preco" step="0.01" min="0" value="<?= $_POST['preco'] ?? '' ?>" required>
      </label>
      <label>
        Valor da parcela (R$):
        <input type="number" name="vParce" step="0.01" min="0" value="<?= $_POST['vParce'] ?? '' ?>" required>
      </label>
      <label>
        Quantidade de parcelas:
        <input type="number" name="qtdParce" step="1" min="1" value="<?= $_POST['qtdParce'] ?? '' ?>" required>
      </label>

      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="output">
        <?php
        $preco = isset($_POST['preco']) ? floatval($_POST['preco']) : 0;
        $vParce = isset($_POST['vParce']) ? floatval($_POST['vParce']) : 0;
        $qtdParce = isset($_POST['qtdParce']) ? intval($_POST['qtdParce']) : 0;

        $totalParcelado = $vParce * $qtdParce;
        $vJuros = $totalParcelado - $preco;

        echo "<p>O valor total parcelado é <b>R$ " . number_format($totalParcelado, 2, ',', '.') . "</b>.</p>";

        if ($vJuros > 0) {
            echo "<p>O valor dos juros sobre a compra de <b>R$ " . number_format($preco, 2, ',', '.') . "</b> com 
            <b>{$qtdParce}</b> parcelas de <b>R$ " . number_format($vParce, 2, ',', '.') . "</b> é de 
            <b style='color:red;'>R$ " . number_format($vJuros, 2, ',', '.') . "</b>.</p>";
        } elseif ($vJuros < 0) {
            echo "<p style='color:blue; font-weight:bold;'>Desconto detectado! O valor total parcelado é menor que o preço à vista em 
            <b>R$ " . number_format(abs($vJuros), 2, ',', '.') . "</b>.</p>";
        } else {
            echo "<p style='color:green; font-weight:bold;'>Sem juros! O preço parcelado é igual ao preço à vista.</p>";
        }
      
        ?>
    </div>
<?php endif; ?>

    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

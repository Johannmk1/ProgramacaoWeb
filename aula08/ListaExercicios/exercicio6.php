<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 6</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 6</h2>
    <form method="post" class="form-feira">

      <div class="linha-produto">
        <span>Maçã</span>
        <label>Preço (R$): 
          <input type="number" name="preco_maca" step="0.01" value="<?= $_POST['preco_maca'] ?? '' ?>">
        </label>
        <label>Qtd (Kg): 
          <input type="number" name="qtd_maca" step="0.01" value="<?= $_POST['qtd_maca'] ?? '' ?>">
        </label>
      </div>

      <div class="linha-produto">
        <span>Melancia</span>
        <label>Preço (R$): 
          <input type="number" name="preco_melancia" step="0.01" value="<?= $_POST['preco_melancia'] ?? '' ?>">
        </label>
        <label>Qtd (Kg): 
          <input type="number" name="qtd_melancia" step="0.01" value="<?= $_POST['qtd_melancia'] ?? '' ?>">
        </label>
      </div>

      <div class="linha-produto">
        <span>Laranja</span>
        <label>Preço (R$): 
          <input type="number" name="preco_laranja" step="0.01" value="<?= $_POST['preco_laranja'] ?? '' ?>">
        </label>
        <label>Qtd (Kg): 
          <input type="number" name="qtd_laranja" step="0.01" value="<?= $_POST['qtd_laranja'] ?? '' ?>">
        </label>
      </div>

      <div class="linha-produto">
        <span>Repolho</span>
        <label>Preço (R$): 
          <input type="number" name="preco_repolho" step="0.01" value="<?= $_POST['preco_repolho'] ?? '' ?>">
        </label>
        <label>Qtd (Kg): 
          <input type="number" name="qtd_repolho" step="0.01" value="<?= $_POST['qtd_repolho'] ?? '' ?>">
        </label>
      </div>

      <div class="linha-produto">
        <span>Cenoura</span>
        <label>Preço (R$): 
          <input type="number" name="preco_cenoura" step="0.01" value="<?= $_POST['preco_cenoura'] ?? '' ?>">
        </label>
        <label>Qtd (Kg): 
          <input type="number" name="qtd_cenoura" step="0.01" value="<?= $_POST['qtd_cenoura'] ?? '' ?>">
        </label>
      </div>

      <div class="linha-produto">
        <span>Batatinha</span>
        <label>Preço (R$): 
          <input type="number" name="preco_batatinha" step="0.01" value="<?= $_POST['preco_batatinha'] ?? '' ?>">
        </label>
        <label>Qtd (Kg): 
          <input type="number" name="qtd_batatinha" step="0.01" value="<?= $_POST['qtd_batatinha'] ?? '' ?>">
        </label>
      </div>

      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="output">
        <?php
        function valorSeguro($campo) {
            return isset($_POST[$campo]) && is_numeric($_POST[$campo]) ? floatval($_POST[$campo]) : 0;
        }

        $preco_maca = valorSeguro('preco_maca');
        $qtd_maca = valorSeguro('qtd_maca');
        $preco_melancia = valorSeguro('preco_melancia');
        $qtd_melancia = valorSeguro('qtd_melancia');
        $preco_laranja = valorSeguro('preco_laranja');
        $qtd_laranja = valorSeguro('qtd_laranja');
        $preco_repolho = valorSeguro('preco_repolho');
        $qtd_repolho = valorSeguro('qtd_repolho');
        $preco_cenoura = valorSeguro('preco_cenoura');
        $qtd_cenoura = valorSeguro('qtd_cenoura');
        $preco_batatinha = valorSeguro('preco_batatinha');
        $qtd_batatinha = valorSeguro('qtd_batatinha');

        $maca = $preco_maca * $qtd_maca;
        $melancia = $preco_melancia * $qtd_melancia;
        $laranja = $preco_laranja * $qtd_laranja;
        $repolho = $preco_repolho * $qtd_repolho;
        $cenoura = $preco_cenoura * $qtd_cenoura;
        $batatinha = $preco_batatinha * $qtd_batatinha;

        $total = $maca + $melancia + $laranja + $repolho + $cenoura + $batatinha;
        $dinheiro = 50.00;

        echo "<h3>Resumo da compra:</h3>";
        echo "<p>Maçã: R$ " . number_format($maca, 2, ',', '.') . "</p>";
        echo "<p>Melancia: R$ " . number_format($melancia, 2, ',', '.') . "</p>";
        echo "<p>Laranja: R$ " . number_format($laranja, 2, ',', '.') . "</p>";
        echo "<p>Repolho: R$ " . number_format($repolho, 2, ',', '.') . "</p>";
        echo "<p>Cenoura: R$ " . number_format($cenoura, 2, ',', '.') . "</p>";
        echo "<p>Batatinha: R$ " . number_format($batatinha, 2, ',', '.') . "</p>";
        echo "<hr>";
        echo "<p><strong>Total gasto: R$ " . number_format($total, 2, ',', '.') . "</strong></p>";

        if ($total > $dinheiro) {
            $falta = $total - $dinheiro;
            echo "<p style='color: red; font-weight: bold;'>O dinheiro não foi suficiente. Faltaram R$ " . number_format($falta, 2, ',', '.') . ".</p>";
        } elseif ($total < $dinheiro) {
            $sobra = $dinheiro - $total;
            echo "<p style='color: blue; font-weight: bold;'>Ainda pode gastar R$ " . number_format($sobra, 2, ',', '.') . ".</p>";
        } else {
            echo "<p style='color: green; font-weight: bold;'>O saldo de R$ 50,00 foi esgotado exatamente!</p>";
        }
        ?>
    </div>
<?php endif; ?>

    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

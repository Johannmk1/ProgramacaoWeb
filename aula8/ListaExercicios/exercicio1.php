<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 1</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 1</h2>
    <form method="post">
      <label>Valor A:
        <input type="number" name="a" required>
      </label>
      <label>Valor B:
        <input type="number" step="any" name="b" required>
      </label>
      <label>Valor C:
        <input type="number" step="any" name="c" required>
      </label>
      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="output">
        <?php
        $a = floatval($_POST['a']);
        $b = floatval($_POST['b']);
        $c = floatval($_POST['c']);

        $resultado = $a + $b + $c;

        if ($a > 10) {
            $cor = "blue";
        } elseif ($b < $c) {
            $cor = "green";
        } elseif ($c < $a && $c < $b) {
            $cor = "red";
        } else {
            $cor = "black";
        }

        echo "<p style='color: {$cor}; font-weight: bold;'>Resultado: {$resultado}</p>";
        ?>
    </div>
<?php endif; ?>


    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

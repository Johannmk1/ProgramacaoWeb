<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 2</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 2</h2>
    <form method="post">
      <label>Valor:
        <input type="number" name="valor" required>
      </label>
      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="output">
        <?php
        $valor = floatval($_POST['valor']);

        if ($valor % 2 == 0) {
            echo "<p style='color: green; font-weight: bold;'>Valor divisível por 2</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>O valor não é divisível por 2</p>";
        }
        ?>
    </div>
<?php endif; ?>


    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

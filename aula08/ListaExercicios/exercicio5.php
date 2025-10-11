<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 5</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 5</h2>
    <form method="post">
      <label>Altura do triângulo retângulo em metros:
        <input type="number" name="altura" required>
      </label>
      <label>Base do triângulo retângulo em metros:
        <input type="number" step="any" name="base" required>
      </label>
      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="output">
        <?php
        $altura = floatval($_POST['altura']);
        $base = floatval($_POST['base']);

        $resultado = ($base * $altura) / 2;

        echo "<a font-weight: bold;'>A área do triângulo retângulo de altura <B>{$altura}</B> e base <B>{$base}</B> metros é <B>{$resultado}</B> metros quadrados.</a>";
        ?>
    </div>
<?php endif; ?>


    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

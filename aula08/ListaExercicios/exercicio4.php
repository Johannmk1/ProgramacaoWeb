<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 4</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 4</h2>
    <form method="post">
      <label>Lado do retângulo em metros:
        <input type="number" name="lado" required>
      </label>
      <label>Base do retângulo em metros:
        <input type="number" step="any" name="base" required>
      </label>
      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="output">
        <?php
        $lado = floatval($_POST['lado']);
        $base = floatval($_POST['base']);

        $resultado = $base * $lado;

        if ($resultado > 10) {
            echo "<h1 style='color: green; font-weight: bold;'>A área do retângulo de lados {$lado} e base {$base} metros é {$resultado} metros quadrados.</h1>";
        } else {
            echo "<h3 style='color: blue; font-weight: bold;'>A área do retângulo de lados {$lado} e base {$base} metros é {$resultado} metros quadrados.</h3>";
        } 

        ?>
    </div>
<?php endif; ?>


    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

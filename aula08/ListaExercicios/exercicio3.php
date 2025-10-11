<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Exercício 3</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Exercício 3</h2>
    <form method="post">
      <label>Lado do quadrado em metros:
        <input type="number" name="lado" required>
      </label>
      <button type="submit">Calcular</button>
    </form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="output">
        <?php
        $lado = floatval($_POST['lado']);

        $area = $lado * $lado;

        echo "<p font-weight: bold;'>A área do quadrado de lado {$lado} metros é {$area} metros quadrados</p>";
        ?>
    </div>
<?php endif; ?>


    <p><a href="index.php">&larr; Voltar</a></p>
  </div>
</body>
</html>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Aula 9 — Exercícios PHP</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h1>Aula 9 — Folha de Exercícios PHP</h1>
    <p>Selecione o exercício:</p>
    <nav>
      <?php for ($i=1; $i<=10; $i++): ?>
        <a href="exercicio<?= $i ?>.php">Exercício <?= $i ?></a>
      <?php endfor; ?>
    </nav>
  </div>
</body>
</html>

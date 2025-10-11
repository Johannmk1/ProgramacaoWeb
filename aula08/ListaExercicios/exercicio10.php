<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Exercício 10</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
  <h2>Exercício 10</h2>

  <div class="output">
    <?php
    $pastas = [
        "bsn" => [
            "3a Fase" => [
                "desenvWeb", "bancoDados 1", "engSoft 1"
            ],
            "4a Fase" => [
                "Intro Web", "bancoDados 2", "engSoft 2"
            ]
        ]
    ];

    function listarPasta($item) {
        echo "<ul class='arvore'>";
        foreach ($item as $chave => $valor) {
            if (is_array($valor)) {
                echo "<li><b>$chave</b>";
                listarPasta($valor);
                echo "</li>";
            } else {
                echo "<li>$valor</li>";
            }
        }
        echo "</ul>";
    }

    listarPasta($pastas);
    ?>
  </div>

  <p><a href="index.php">&larr; Voltar</a></p>
</div>
</body>
</html>

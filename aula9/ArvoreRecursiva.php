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

function listarPasta($item, int $nivel = 0) {
    if (is_array($item)) {
        echo str_repeat(" ", $nivel) . "<ul>";
        foreach ($item as $chave => $valor) {
            echo str_repeat(" ", $nivel + 2) . "<li><b>$chave</b>";
            listarPasta($valor, $nivel + 4);
            echo "</li>";
        }
        echo str_repeat(" ", $nivel) . "</ul>";
    } else {
        echo str_repeat(" ", $nivel + 2) . "<li>$item</li>";
    }
}

listarPasta($pastas);
?>

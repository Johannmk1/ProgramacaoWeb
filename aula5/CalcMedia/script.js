let linhaAdicionada = false;
let colunaAdicionada = false;

function aplicarCorMedia(celula, valor) {
    celula.classList.remove("result-alta", "result-media", "result-baixa");
    if (valor === "" || isNaN(valor)) return;
    if (valor >= 8) celula.classList.add("result-alta");
    else if (valor >= 6) celula.classList.add("result-media");
    else celula.classList.add("result-baixa");
}

function calcularMediaColuna(j) {
    const tabela = document.getElementById("tabelaNotas");
    let soma = 0, contador = 0;

    for (let i = 2; i < tabela.rows.length; i++) {
        let linha = tabela.rows[i];
        if (linha.id === "linhaTotal") continue; // ignora total
        if (linha.cells[j]) {
            let valor = parseFloat(linha.cells[j].innerText.replace(',', '.'));
            if (!isNaN(valor)) {
                soma += valor;
                contador++;
            }
        }
    }
    return contador > 0 ? (soma / contador).toFixed(2) : "";
}

function calcularMediaLinha(i) {
    const tabela = document.getElementById("tabelaNotas");
    let soma = 0, contador = 0;
    let linha = tabela.rows[i];

    for (let j = 1; j <= 9; j++) {
        if (linha.cells[j]) {
            let valor = parseFloat(linha.cells[j].innerText.replace(',', '.'));
            if (!isNaN(valor)) {
                soma += valor;
                contador++;
            }
        }
    }

    return contador > 0 ? (soma / contador).toFixed(2) : "";
}

// por notas
function toggleLinhaTotal() {
    const tabela = document.getElementById("tabelaNotas");

    if (!linhaAdicionada) {
        let novaLinha = tabela.insertRow(-1);
        novaLinha.id = "linhaTotal";
        novaLinha.classList.add("total");

        let celula = novaLinha.insertCell(0);
        celula.innerText = "Média das Notas";

        for (let j = 1; j <= 9; j++) {
            let media = calcularMediaColuna(j);
            let novaCelula = novaLinha.insertCell(j);
            novaCelula.innerText = media;
            aplicarCorMedia(novaCelula, parseFloat(media));
        }

        document.getElementById("btnLinha").innerText = "Remover linha: Média das Notas";
        linhaAdicionada = true;
    } else {
        document.getElementById("linhaTotal").remove();
        document.getElementById("btnLinha").innerText = "Adicionar linha: Média das Notas";
        linhaAdicionada = false;
    }
}

// por aluno
function toggleColunaTotal() {
    const tabela = document.getElementById("tabelaNotas");

    if (!colunaAdicionada) {
        let th = document.createElement("th");
        th.id = "colunaTotalCabecalho";
        th.innerText = "Média por Aluno";
        tabela.rows[1].appendChild(th);

        for (let i = 2; i < tabela.rows.length; i++) {
            if (tabela.rows[i].id === "linhaTotal") continue; // ignora total
            let media = calcularMediaLinha(i);
            let celula = tabela.rows[i].insertCell(-1);
            celula.innerText = media;
            celula.classList.add("total");
            celula.id = `colunaTotal${i}`;
            aplicarCorMedia(celula, parseFloat(media));
        }

        document.getElementById("btnColuna").innerText = "Remover coluna: Média por Aluno";
        colunaAdicionada = true;
    } else {
        tabela.rows[1].removeChild(document.getElementById("colunaTotalCabecalho"));
        for (let i = 2; i < tabela.rows.length; i++) {
            const celula = document.getElementById(`colunaTotal${i}`);
            if (celula) celula.remove();
        }
        document.getElementById("btnColuna").innerText = "Adicionar coluna: Média por Aluno";
        colunaAdicionada = false;
    }
}

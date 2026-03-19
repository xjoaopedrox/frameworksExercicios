let tabela = document.getElementsByTagName("tabela");

for (let i = 0; i < tabela.length; i++) {
    let tab = tabela[i];
    let linhas = tab.getAttribute("linha");
    let colunas = tab.getAttribute("coluna");

    if (!linhas || !colunas) {
        console.error("Erro: Atributos linha/coluna ausentes.");
        continue;
    }

    let novaTabela = document.createElement("table");
    let colspanAttr = tab.getElementsByTagName("expand"); 
    let dadosTag = tab.getElementsByTagName("dados")[0];
    let dados = [];
    let matriz = [];

    for (let w = 0; w < colspanAttr.length; w++) {
        let l_inicio = parseInt(colspanAttr[w].getAttribute("linha"));
        let c_inicio = parseInt(colspanAttr[w].getAttribute("coluna"));
        let tam = parseInt(colspanAttr[w].getAttribute("tamanho"));
        let tipo = colspanAttr[w].getAttribute("tipo");

        if (tipo == "coluna" && (c_inicio + tam > colunas)) {
            console.warn("Expand horizontal ignorado: ultrapassa o limite.");
            continue; 
        }
        if (tipo == "linha" && (l_inicio + tam > linhas)) {
            console.warn("Expand vertical ignorado: ultrapassa o limite.");
            continue;
        }
        matriz.push([l_inicio, c_inicio, tam, tipo]);
    }

    if (dadosTag) {
        let texto = dadosTag.textContent.trim();
        let linhaDados = texto.split("\n");
        for (let linha of linhaDados) {
            let colunasDados = linha.split("|");
            dados.push(colunasDados.map(c => c.trim()));
        }
    }

    let totalVagas = linhas * colunas;
    let totalDados = 0;
    for (let linha of dados) {
        totalDados += linha.length;
    }

    let podeCriar = (totalDados <= totalVagas);

    if (podeCriar) {
        let bordaAttr = tab.getAttribute("borda") || "1px solid black";
        let vetBorda = bordaAttr.split(" ");
        novaTabela.style.setProperty('--cor-borda', vetBorda[2]);
        novaTabela.style.setProperty('--tipo-borda', vetBorda[1]);
        novaTabela.style.setProperty('--tamanho-borda', vetBorda[0]);

        for (let x = 0; x < linhas; x++) {
            let tr = document.createElement("tr");
            for (let y = 0; y < colunas; y++) {
                let td = document.createElement("td");
                
                if (dados[x] && dados[x][y]) {
                    td.innerText = dados[x][y];
                }

                let span = 1;
                let oTipo = "";
                for (let k = 0; k < matriz.length; k++) {
                    if (matriz[k][0] == x && matriz[k][1] == y) {
                        span = matriz[k][2];
                        oTipo = matriz[k][3];
                        break;
                    }
                }

                if (span > 1) {
                    if (oTipo == "coluna") {
                        td.setAttribute("colspan", span);
                        y += span - 1; 
                    } else if (oTipo == "linha") {
                        td.setAttribute("rowspan", span);
                    }
                }
                tr.appendChild(td);
            }
            novaTabela.appendChild(tr);
        }
        tab.appendChild(novaTabela);
    } else {
        console.error("Erro: muitos dados, pouco espaço.");
    }


console.log(matriz)
}
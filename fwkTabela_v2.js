let tabela = document.getElementsByTagName("tabela");
for (let i = 0; i < tabela.length; i++) {
    let tab = tabela[i];
    let linhas = tab.getAttribute("linha");
    let colunas = tab.getAttribute("coluna");

    let novaTabela = document.createElement("table");

    let colspanAttr = document.getElementsByTagName("expand");
    let matriz = [];
    for (let w = 0; w < colspanAttr.length; w++) {
        matriz.push([
            colspanAttr[w].getAttribute("linha"),
            colspanAttr[w].getAttribute("coluna"),
            colspanAttr[w].getAttribute("tamanho"),
            colspanAttr[w].getAttribute("tipo")
        ]);
    }

    let bordaAttr = tab.getAttribute("borda");
    let vetBorda = bordaAttr.split(" ");
    novaTabela.style.setProperty('--cor-borda', vetBorda[2]);
    novaTabela.style.setProperty('--tipo-borda', vetBorda[1]);
    novaTabela.style.setProperty('--tamanho-borda', vetBorda[0]);

for (let x = 0; x < linhas; x++) {
    let tr = document.createElement("tr"); // 1. Cria a linha
    
    for (let y = 0; y < colunas; y++) {
        let td = document.createElement("td"); // 2. Cria a célula
        let span = 1;
        let oTipo = ""; // Resetamos o tipo para cada célula

        // 3. Busca na matriz se ESSA posição (x,y) tem um expand
        for (let k = 0; k < matriz.length; k++) {
            if (matriz[k][0] == x && matriz[k][1] == y) {
                span = matriz[k][2];
                oTipo = matriz[k][3]; // <--- Pega o tipo que guardamos na matriz!
                break;
            }
        }

        // 4. Aplica a expansão baseada no TIPO
        if (span > 1) {
            if (oTipo == "coluna") {
                td.setAttribute("colspan", span);
                y += span - 1; // Pula as colunas vizinhas
            } else if (oTipo == "linha") {
                td.setAttribute("rowspan", span);
                // Aqui não pulamos o Y, lembra?
            }
        }
        
        tr.appendChild(td); // 5. Coloca a célula na linha (ainda dentro do loop 'y')
    }
    novaTabela.appendChild(tr); // 6. Coloca a linha na tabela (dentro do loop 'x')
}
}
tab.appendChild(novaTabela);


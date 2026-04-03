document.addEventListener("DOMContentLoaded", function () {

    let tabela = document.getElementsByTagName("tabela");

    for (let i = 0; i < tabela.length; i++) {
        let tab = tabela[i];

        let linhas = tab.getAttribute("linha");
        let colunas = tab.getAttribute("coluna");

        let novaTabela = document.createElement("table");

        let colspanAttr = tab.getElementsByTagName("expand"); // corrigido (era global)
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

        // proteção contra erro
        if (bordaAttr) {
            let vetBorda = bordaAttr.split(" ");
            novaTabela.style.setProperty('--cor-borda', vetBorda[2]);
            novaTabela.style.setProperty('--tipo-borda', vetBorda[1]);
            novaTabela.style.setProperty('--tamanho-borda', vetBorda[0]);
        }

        for (let x = 0; x < linhas; x++) {
            let tr = document.createElement("tr");

            for (let y = 0; y < colunas; y++) {
                let td = document.createElement("td");

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

        tab.appendChild(novaTabela); //  corrigido (posição certa)
    }

});

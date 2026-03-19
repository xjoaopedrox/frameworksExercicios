let tabela = document.getElementsByTagName("tabela");
for(let i=0;i<tabela.length;i++){
    let tab = tabela[i];
    let linhas= tab.getAttribute("linha");
    let colunas= tab.getAttribute("coluna");

    let novaTabela= document.createElement("table");
    let colspanAttr=tab.getAttribute("colspan");
    let matriz = colspanAttr.split(";");
    matriz=matriz.map(l => l.trim());
    matriz=matriz.map(l => l.split(" ")) ;

    let bordaAttr =  tab.getAttribute("borda");
    let vetBorda = bordaAttr.split(" ");
    novaTabela.style.setProperty('--cor-borda', vetBorda[2]);
    novaTabela.style.setProperty('--tipo-borda', vetBorda[1]);
    novaTabela.style.setProperty('--tamanho-borda', vetBorda[0]);

    for(let x=0;x<linhas;x++){
        let tr=document.createElement("tr");
        for(let y=0;y<colunas;y++){
            let td=document.createElement("td");
            let span=1;
            for(let k =0; k<matriz.length;k++){
                if(matriz[k][0] == x && matriz[k][1]==y){
                    span=matriz[k][2];
                    break;
                }
            }
            if(span>1){
                td.setAttribute("colspan",span);
            } y         +=span-1;

            tr.appendChild(td);
        }
        novaTabela.appendChild(tr);
    }
    tab.appendChild(novaTabela);

}
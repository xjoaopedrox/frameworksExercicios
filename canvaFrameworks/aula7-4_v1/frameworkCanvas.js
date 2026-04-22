const body = document.getElementsByTagName("body")[0];
const canvas = document.createElement("canvas");
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
body.appendChild(canvas);

const ctx = canvas.getContext("2d");
const arco = document.getElementsByTagName("arco");
const retangulo = document.getElementsByTagName("retangulo");

const objRet = {
    x: null, y: null, largura: null, altura: null, cor: null,
    velocidade: 3,

    desenhar: function () {
        for (let r of retangulo) {
            this.largura = parseInt(r.getAttribute("largura")) || (r.setAttribute("largura", 200), 200);
            this.altura = parseInt(r.getAttribute("altura")) || (r.setAttribute("altura", 100), 100);
            this.x = parseInt(r.getAttribute("posRetX")) || (r.setAttribute("posRetX", 100), 100);
            this.y = parseInt(r.getAttribute("posRetY")) || (r.setAttribute("posRetY", 100), 100);
            this.cor = r.getAttribute("cor") || (r.setAttribute("cor", "red"), "red");

            ctx.fillStyle = this.cor;
            ctx.fillRect(this.x, this.y, this.largura, this.altura);

            objArco.moverAteClique(r);
        }
    }
};

const objArco = {
    x: null, y: null, raio: null, rad: null, cor: null,
    velocidade: 3,

    desenhar: function () {
        for (let a of arco) {
            this.raio = parseInt(a.getAttribute("raio")) || (a.setAttribute("raio", 50), 50);
            this.x = parseInt(a.getAttribute("posX")) || (a.setAttribute("posX", 100), 100);
            this.y = parseInt(a.getAttribute("posY")) || (a.setAttribute("posY", 100), 100);
            this.cor = a.getAttribute("cor") || (a.setAttribute("cor", "blue"), "blue");

            let grau = parseInt(a.getAttribute("graus")) || (a.setAttribute("graus", 360), 360);
            this.rad = grau * (Math.PI / 180);

            ctx.beginPath();
            ctx.arc(this.x, this.y, this.raio, 0, this.rad, true);
            ctx.fillStyle = this.cor;
            ctx.fill();
            ctx.closePath();

            let moverArco = a.getAttribute("mover");
            if (moverArco) this.mover(a, moverArco);

            this.comportamento(a);

            this.moverAteClique(a);
        }
    },

    mover: function (a, moverArco) {
        if (moverArco === "acima") this.y -= this.velocidade;
        if (moverArco === "abaixo") this.y += this.velocidade;
        if (moverArco === "esquerda") this.x -= this.velocidade;
        if (moverArco === "direita") this.x += this.velocidade;

        a.setAttribute("posX", this.x);
        a.setAttribute("posY", this.y);

        let altura = canvas.height;
        let largura = canvas.width;

        if (this.y > altura) a.setAttribute("posY", this.raio);
        if (this.y <= 0) a.setAttribute("posY", altura);
        if (this.x > largura) a.setAttribute("posX", this.raio);
        if (this.x <= 0) a.setAttribute("posX", largura);
    },

    moverAteClique: function (a) {
        let destX = parseInt(a.getAttribute("destX"));
        let destY = parseInt(a.getAttribute("destY"));
        if (!destX || !destY) return;

        let atualX = parseInt(a.getAttribute("posX")) || parseInt(a.getAttribute("posRetX"));
        let atualY = parseInt(a.getAttribute("posY")) || parseInt(a.getAttribute("posRetY"));

        let dx = destX - atualX;
        let dy = destY - atualY;

        if (Math.abs(dx) > this.velocidade) atualX += this.velocidade * Math.sign(dx);
        if (Math.abs(dy) > this.velocidade) atualY += this.velocidade * Math.sign(dy);

        if (a.tagName === "ARCO") {
            a.setAttribute("posX", atualX);
            a.setAttribute("posY", atualY);
        } else if (a.tagName === "RETANGULO") {
            a.setAttribute("posRetX", atualX);
            a.setAttribute("posRetY", atualY);
        }
    },

    comportamento: function (a) {
        let tipo = a.getAttribute("comportamento");
        if (!tipo) return;

        if (tipo === "fatiar") {
            let grau = parseInt(a.getAttribute("graus"));
            grau -= 15;
            if (grau <= 0) grau = 360;
            a.setAttribute("graus", grau);
        }

        if (tipo === "colorir") {
            let novaCor = "#" + Math.floor(Math.random() * 16777215).toString(16);
            a.setAttribute("cor", novaCor);
        }
    }
};

function desenharFormas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (arco) objArco.desenhar();
    if (retangulo) objRet.desenhar();

    requestAnimationFrame(desenharFormas);
    colisao();
    colisaoRetangulos();
}

function calcularDistancia(a, b) {
    let ax = parseInt(a.getAttribute("posX"));
    let ay = parseInt(a.getAttribute("posY"));
    let bx = parseInt(b.getAttribute("posX"));
    let by = parseInt(b.getAttribute("posY"));

    return Math.sqrt((ax - bx) ** 2 + (ay - by) ** 2);
}

function colisaoRetangulos() {
    let vetor = document.querySelectorAll("retangulo[colisao]");

    for (let i = 0; i < vetor.length; i++) {
        for (let j = i + 1; j < vetor.length; j++) {
            let a = vetor[i];
            let b = vetor[j];

            let ax = parseInt(a.getAttribute("posRetX"));
            let ay = parseInt(a.getAttribute("posRetY"));
            let alargura = parseInt(a.getAttribute("largura"));
            let aaltura = parseInt(a.getAttribute("altura"));

            let bx = parseInt(b.getAttribute("posRetX"));
            let by = parseInt(b.getAttribute("posRetY"));
            let blargura = parseInt(b.getAttribute("largura"));
            let baltura = parseInt(b.getAttribute("altura"));

            let aLeft = ax;
            let aRight = ax + alargura;
            let aTop = ay;
            let aBottom = ay + aaltura;

            let bLeft = bx;
            let bRight = bx + blargura;
            let bTop = by;
            let bBottom = by + baltura;

            if (aLeft < bRight && aRight > bLeft && aTop < bBottom && aBottom > bTop) {
                inverteDirecao(a);
                inverteDirecao(b);
            }
        }
    }
}

function colisao() {
    let vetor = document.querySelectorAll("arco[colisao]");

    for (let i = 0; i < vetor.length; i++) {
        for (let j = i + 1; j < vetor.length; j++) {
            let a = vetor[i];
            let b = vetor[j];
            let dist = calcularDistancia(a, b);
            let raioA = parseInt(a.getAttribute("raio"));
            let raioB = parseInt(b.getAttribute("raio"));

            if (dist < (raioA + raioB)) {
                inverteDirecao(a);
                inverteDirecao(b);
            }
        }
    }
}

function inverteDirecao(el) {
    let mover = el.getAttribute("mover");
    if (!mover) return;

    if (mover === "acima") el.setAttribute("mover", "abaixo");
    else if (mover === "abaixo") el.setAttribute("mover", "acima");
    else if (mover === "esquerda") el.setAttribute("mover", "direita");
    else if (mover === "direita") el.setAttribute("mover", "esquerda");
}

canvas.addEventListener("click", function (e) {
    let destinoX = e.clientX;
    let destinoY = e.clientY;

    for (let a of arco) {
        a.setAttribute("destX", destinoX);
        a.setAttribute("destY", destinoY);
    }

    for (let r of retangulo) {
        r.setAttribute("destX", destinoX);
        r.setAttribute("destY", destinoY);
    }
});

desenharFormas();
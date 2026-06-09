<?php

class Professor {

    private int $id;
    private string $nome;
    private string $titulacao;

__toString(){
$retorno = "Professor: ";
foreach ($this as $atributo => $valor) {
    $retorno .= "$atributo = $valor; ";
}
        return $retorno;
    }


    public function getId() : int {
        return $this->id;
    }

    public function getNome() : string {
        return $this->nome;
    }

    public function setNome(string $nome) {
        $this->nome = $nome;
    }

    public function getTitulacao() : string {
        return $this->titulacao;
    }

    public function setTitulacao(string $titulacao) {
        $this->titulacao = $titulacao;
    }

}

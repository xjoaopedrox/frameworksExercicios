<?php

class Curso {

    private int $id;
    private string $nome;
    private int $duracao;

__toString(){
$retorno = "Curso: ";
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

    public function getDuracao() : int {
        return $this->duracao;
    }

    public function setDuracao(int $duracao) {
        $this->duracao = $duracao;
    }

}

<?php

class Aluno {

    private int $id;
    private string $nome;
    private string $nascimento;
    private string $curso;

__toString(){
$retorno = "Aluno: ";
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

    public function getNascimento() : string {
        return $this->nascimento;
    }

    public function setNascimento(string $nascimento) {
        $this->nascimento = $nascimento;
    }

    public function getCurso() : string {
        return $this->curso;
    }

    public function setCurso(string $curso) {
        $this->curso = $curso;
    }

}

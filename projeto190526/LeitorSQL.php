<?php

class LeitorSQL
{
    private string $conteudo;
    // Matriz associativa: ['nomeTabela' => ['nomeCampo' => ['tipo' => string, 'primary' => bool]]]
    private array $tabelas = [];

    public function __construct(string $arquivo)
    {
        if (!file_exists($arquivo)) {
            throw new Exception("Arquivo não encontrado: $arquivo");
        }

        $this->conteudo = file_get_contents($arquivo);

        $this->processarTabelas();
    }

    private function getTipoPHP(string $tipoSQL): string
    {
        $tipoSQL = strtolower($tipoSQL);

        $tipoSQL = preg_replace('/\(.+\)/', '', $tipoSQL); 
        $tipoSQL = preg_replace('/\s+unsigned\b/i', '', $tipoSQL);
        $tipoSQL = strtolower(trim($tipoSQL));

        return match ($tipoSQL) {
            'int', 'integer', 'bigint', 'smallint', 'tinyint' => 'int',
            'varchar', 'char', 'text', 'longtext' => 'string',
            'float', 'double', 'decimal' => 'float',
            'date', 'datetime', 'timestamp' => 'string',
            'boolean', 'bool' => 'bool',
            default => 'mixed'
        };
    }

    /**
     * Extrai do SQL:
     *  - nomes das tabelas
     *  - campos de cada tabela (nome e tipo)
     *  - quais campos são chave primária
     *  - quais campos são chave estrangeira (tabela e campo de referência)
     */
    private function processarTabelas(): void
    {
        /*
         * Regex — captura cada bloco CREATE TABLE
         *
         * CREATE TABLE `(.+?)`--> nome da tabela (grupo 1)
         *   .+? não-guloso: para no primeiro fechamento de `
         * \( --> abre parênteses literal
         * (.*?) --> conteúdo interno com todos os campos (grupo 2)
         *   não-guloso: para antes do primeiro ) ENGINE=
         * \)\s*ENGINE= --> fecha parênteses + zero ou mais espaços/enters + ENGINE=
         * Flag /s --> faz . casar com \n, permitindo múltiplas linhas
         */
        preg_match_all(
            '/CREATE TABLE `(.+?)` \((.*?)\)\s*ENGINE=/s',
            $this->conteudo,
            $matches,
            PREG_SET_ORDER
        );

        /*
         * Cada $match contém:
         *   [0] --> instrução CREATE TABLE completa
         *   [1] --> nome da tabela
         *   [2] --> conteúdo interno (todos os campos como texto)
         */
        foreach ($matches as $match) {

            $nomeTabela  = $match[1];
            $camposTexto = $match[2];

            $this->tabelas[$nomeTabela] = [];

            // Divide o bloco de campos linha por linha
            $linhas = explode("\n", $camposTexto);

            foreach ($linhas as $linha) {

                $linha = trim($linha);

                /*
                 * Ignora linhas que não definem campos.
                 * Linhas como PRIMARY KEY, KEY, INDEX e comentários são ignoradas.
                 */
                if (!str_starts_with($linha, '`')) {
                    continue;
                }

                /*
                 * Regex — captura nome e tipo do campo
                 *
                 * `(.+?)`          --> nome do campo entre crases (grupo 1)
                 * \s+              --> um ou mais espaços
                 * ([a-zA-Z0-9()]+) --> tipo do campo (grupo 2), aceita letras, números, com parênteses (ex: varchar(45)
                 * \s+unsigned)?     --> opcionalmente aceita "unsigned" após o tipo (ex: int(11) unsigned
                 * Flag /i           --> case-insensitive (INT, int, Int são tratados igual
                 * 
                 * Nota: essa regex é simplificada e pode não cobrir todos os casos complexos de tipos SQL, mas funciona para os tipos mais comuns.)
                 */
                preg_match('/`(.+?)`\s+([^\s,]+(?:\s+unsigned)?)/', $linha, $campo);

                $nomeCampo = $campo[1] ?? '';
                $tipoCampo = $campo[2] ?? '';

                if ($nomeCampo === '') continue;

                $this->tabelas[$nomeTabela][$nomeCampo] = [
                    'tipo'    => $this->getTipoPHP($tipoCampo),
                    'primary' => false  // atualizado depois pela seção ALTER TABLE
                ];
            }
        }

        /*
         * Regex — identifica chaves primárias a partir de instruções ALTER TABLE - comum em dumps SQL exportados do phpMyAdmin
         *
         * ALTER TABLE `(.+?)`         --> nome da tabela (grupo 1)
         * (.*?)                        --> trecho entre o nome e ADD PRIMARY KEY (grupo 2, ignorado)
         * ADD PRIMARY KEY \(`(.+?)`\) --> nome do campo PK (grupo 3)
         * Flag /s                      --> captura mesmo com quebras de linha
         *
         * Resultado em $primaryMatches[N]:
         *   [1] --> nome da tabela
         *   [3] --> nome do campo que é chave primária
         */
        preg_match_all(
            '/ALTER TABLE `(.+?)`(.*?)ADD PRIMARY KEY \(`(.+?)`\)/s',
            $this->conteudo,
            $primaryMatches,
            PREG_SET_ORDER
        );

        //TODO: detectar se a chave eh automatica (AUTO_INCREMENT) e marcar isso na estrutura de dados para uso posterior (ex: ignorar setter para PK auto_increment)

        foreach ($primaryMatches as $match) {
            $tabela  = $match[1];
            $campoPK = $match[3];

            if (isset($this->tabelas[$tabela][$campoPK])) {
                $this->tabelas[$tabela][$campoPK]['primary'] = true;
            }
        }

        preg_match_all(
            //captura chaves estrangeiras no formato: ALTER TABLE `aluno` ADD CONSTRAINT `fk_curso` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`)
            '/ALTER TABLE `([^`]+)`\s+ADD CONSTRAINT `[^`]+`\s+FOREIGN KEY \(`([^`]+)`\)\s+REFERENCES `([^`]+)`\s*\(`([^`]+)`\)/m',
            $this->conteudo,
            $estrangeiras,
            PREG_SET_ORDER
        );
        //TODO: detectar e logar mais de uma fk por tabela/campo

        foreach ($estrangeiras as $fkMatch) {
            $tabelaOrigem      = $fkMatch[1];
            $campoOrigem       = $fkMatch[2];
            $tabelaReferencia   = $fkMatch[3];
            $campoReferencia    = $fkMatch[4];

            if (isset($this->tabelas[$tabelaOrigem][$campoOrigem])) {
                
                $this->tabelas[$tabelaOrigem][$campoOrigem]['foreign'] = [
                    'referencia_tabela' => $tabelaReferencia,
                    'referencia_campo'  => $campoReferencia
                ];
            }
        }
    }

    /**
     * Retorna os nomes de todas as tabelas encontradas no SQL.
     * Ex: ['aluno', 'curso', 'professor']
     */
    public function getTabelas(): array
    {
        return array_keys($this->tabelas);
    }

    /**
     * Retorna os atributos de uma tabela no formato:
     * [
     *   'id'   => ['tipo' => 'int(11)',     'primary' => true],
     *   'nome' => ['tipo' => 'varchar(30)', 'primary' => false],
     *  'id_curso' => ['tipo' => 'int(11)', 
     * '               'primary' => false, 
     *                 'foreign' => ['referencia_tabela' => 'curso', 'referencia_campo' => 'id']]
     * ]
     */
    public function getAtributos(string $tabela): array
    {
        if (!isset($this->tabelas[$tabela])) {
            return [];
        }

        return $this->tabelas[$tabela];
    }
}
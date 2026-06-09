<?php

require_once("LeitorSQL.php");

function getCamelCase(string $nomeCampo): string
{
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $nomeCampo)));
}

function normalizarEntidade(string $entidade): string
{
    // Verifica extensão de tabela (ex: `schema.tabela`) e extrai apenas o nome da tabela
    if (str_contains($entidade, ".")) {
        $entidade = explode(".", $entidade)[1];
    }
    $entidade = strtolower($entidade); 
    // Remove caracteres especiais, mantendo apenas letras, números e underscores
    $entidade = preg_replace('/[^A-Za-z0-9_]/', '', $entidade);
    return getCamelCase($entidade);
}

function normalizarAtributos(array $atributos): array
{
    $normalizados = [];
    foreach ($atributos as $nomeCampo => $info) {
        $nomeNormalizado = trim(strtolower($nomeCampo));
        $normalizados[$nomeNormalizado] = $info; // preserva 'tipo' e 'primary'
    }
    return $normalizados;
}

/**
 * Normaliza o caminho do diretório:
 * - remove espaços nas pontas
 * - substitui espaços internos por _
 * - remove caracteres especiais
 * - garante que termina com /
 * - retorna o caminho absoluto
 */

//TODO: adicionar opcao para diretorio composto (ex: "src/Models/")
function normalizarDiretorio(string $diretorio): string
{
    $diretorio = trim($diretorio);
    $diretorio = preg_replace('/\s+/', '_', $diretorio);
    $diretorio = preg_replace('/[^a-zA-Z0-9_\-]/', '', $diretorio);
    $diretorio = rtrim($diretorio, '/') . '/';
    return __DIR__ . DIRECTORY_SEPARATOR . $diretorio;
}

/**
 * Gera o conteúdo PHP de uma classe Model a partir dos atributos da tabela.
 *
 * Regras aplicadas:
 * - Todos os atributos são privados
 * - Todos os atributos recebem getter
 * - Atributos que NÃO são chave primária recebem setter
 *   (PK é gerada pelo banco — não deve ser alterada manualmente)
 *
 */
function criarClasse(string $entidade, LeitorSQL $leitor): string
{
    $atributos    = $leitor->getAtributos($entidade);
    $atributos    = normalizarAtributos($atributos);
    $nomeEntidade = normalizarEntidade($entidade);

    $declaracoes     = "";
    $gettersSetters  = "";

    foreach ($atributos as $nomeCampo => $info) {
        $tipo      = $info['tipo'];
        $isPK      = $info['primary'];
        $isFK = isset($info['foreign']);
        $camelCase = getCamelCase($nomeCampo);

        // declaração do atributo privado
        $declaracoes .= "    private $tipo \$$nomeCampo;\n";

        // getter para todos os campos
        $gettersSetters .=
            "    public function get$camelCase() : $tipo {\n" .
            "        return \$this->$nomeCampo;\n" .
            "    }\n\n";

        // setter apenas para campos que NÃO são chave primária ou chave estrangeira
        if (!$isPK && !$isFK) {
            $gettersSetters .=
                "    public function set$camelCase($tipo \$$nomeCampo) {\n" .
                "        \$this->$nomeCampo = \$$nomeCampo;\n" .
                "    }\n\n";
        }
    }

    //TODO: adicionar toString() 

    $magico = "__toString(){\n" .
            "\$retorno = \"$nomeEntidade: \";\n" .
            "foreach (\$this as \$atributo => \$valor) {\n" .
            "    \$retorno .= \"\$atributo = \$valor; \";\n" .
            "}\n" .
              "        return \$retorno;\n" .
              "    }\n\n";

    $conteudo  = "<?php\n\n";
    $conteudo .= "class $nomeEntidade {\n\n";
    $conteudo .= $declaracoes;
    $conteudo .= "\n";
    $conteudo .= $magico;
    $conteudo .= "\n";
    $conteudo .= $gettersSetters;
    $conteudo .= "}\n";

    return $conteudo;
}

/**
 * Cria o diretório caso não exista.
 */
function criarDiretorio(string $diretorio): void
{
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
        chmod($diretorio, 0777);
    }
}

/**
 * Salva o conteúdo da classe em um arquivo .php dentro do diretório informado.
 * Retorna mensagem de sucesso ou erro.
 */
function criarArquivo(string $diretorio, string $entidade, string $conteudo): string
{
    $entidadeNormalizada  = normalizarEntidade($entidade);
    $arquivo              = $entidadeNormalizada . ".php";
    $diretorioNormalizado = normalizarDiretorio($diretorio);
    $caminhoCompleto      = $diretorioNormalizado . $arquivo;

    criarDiretorio($diretorioNormalizado);

    if (file_put_contents($diretorioNormalizado . $arquivo, $conteudo) !== false) {
        return "Arquivo criado: " . $caminhoCompleto;
    } else {
        $erro = error_get_last();
        return "Erro ao criar arquivo: " . ($erro['message'] ?? 'Erro desconhecido');
    }
}

/**
 * Lê e exibe o conteúdo de um arquivo PHP gerado, com syntax highlight.
 */
function lerArquivo(string $diretorio, string $entidade): void
{
    $entidade        = normalizarEntidade($entidade);
    $arquivo         = $entidade . ".php";
    $diretorio       = normalizarDiretorio($diretorio);
    $caminhoCompleto = $diretorio . $arquivo;

    if (file_exists($caminhoCompleto)) {
        $conteudo = file_get_contents($caminhoCompleto);
        echo "<h3>Conteúdo do arquivo: " . $arquivo . "</h3>";
        highlight_string($conteudo);
    } else {
        echo "<p>Arquivo não encontrado: $caminhoCompleto</p>";
    }
}
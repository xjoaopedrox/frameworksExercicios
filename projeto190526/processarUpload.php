<?php
require_once("CriadorClasses.php");

// 1. Verifica erro no upload
if ($_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    header("Location: formUpload.php?erro=0");
    exit;
}

// 2. Extrai dados do arquivo recebido
$tmpName      = $_FILES['arquivo']['tmp_name'];
$tamanho      = $_FILES['arquivo']['size'];
$nomeOriginal = $_FILES['arquivo']['name'];

// 3. Valida extensão
$partes   = explode(".", $nomeOriginal);
$extensao = strtolower(end($partes));

if ($extensao !== "sql") {
    header("Location: formUpload.php?erro=1");
    exit;
}

// 4. Define destino e move o arquivo
$pastaDestino = __DIR__ . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR;

if (!is_dir($pastaDestino)) {
    mkdir($pastaDestino, 0777, true);
}

$destino = $pastaDestino . $nomeOriginal;

if (!move_uploaded_file($tmpName, $destino)) {
    header("Location: formUpload.php?erro=2");
    exit;
}

// 5. Processa o SQL e gera as classes
$leitor    = new LeitorSQL($destino);
$tabelas   = $leitor->getTabelas();
$diretorio = "modelo";

echo "<h2>Arquivo recebido: <em>" . htmlspecialchars($nomeOriginal) . "</em></h2>";
echo "<p>Tamanho: " . number_format($tamanho / 1024, 2) . " KB | Tabelas encontradas: " . count($tabelas) . "</p>";
echo "<hr><h3>Classes geradas:</h3>";

foreach ($tabelas as $entidade) {
    $conteudo  = criarClasse($entidade, $leitor);
    $resultado = criarArquivo($diretorio, $entidade, $conteudo);
    echo "<p>" . $resultado . "</p>";
    lerArquivo($diretorio, $entidade);
}

echo "<hr><a href='formUpload.php'>← Enviar outro arquivo</a>";
<?php
if (isset($_GET['erro'])) {
    $mensagens = [
        0 => "Falha no envio. Verifique o arquivo.",
        1 => "Somente arquivos .sql são aceitos.",
        2 => "Não foi possível salvar o arquivo. Permissão negada.",
    ];
    $msg = $mensagens[$_GET['erro']] ?? "Erro desconhecido.";
    echo "<p style='color:red'>$msg</p>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerador de Classes</title>
</head>
<body>
    <h2>Gerar classes Model a partir de arquivo SQL</h2>
    <form action="processarUpload.php" method="post" enctype="multipart/form-data">
        <label for="arquivo">Selecione o arquivo .sql:</label><br><br>
        <input type="file" name="arquivo" id="arquivo" accept=".sql">
        <br><br>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>
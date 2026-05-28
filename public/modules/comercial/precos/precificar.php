<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}
include __DIR__ . "/../../../config/db.php";

$codigoFamilia = $_POST['codigo_familia'] ?? '';
$novoPreco = $_POST['novo_preco'] ?? '';

if (!$codigoFamilia || !$novoPreco) {
    die("Dados inválidos.");
}

// Converter vírgula para ponto
$novoPreco = str_replace(',', '.', $novoPreco);
$novoPreco = floatval($novoPreco);

$dataVigencia = date('Y-m-d', strtotime('+1 day'));
$usuario = $_SESSION['usuario']['codigo'];

// Buscar todos os produtos da família
$sql = "SELECT codigo FROM produto WHERE codigo_familia = :familia";
$stmt = $pdo->prepare($sql);
$stmt->execute([':familia' => $codigoFamilia]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inserir programação para todos os produtos
foreach ($produtos as $p) {
    $stmt = $pdo->prepare("INSERT INTO programacao_preco 
        (codigo_produto, preco_programado, data_entra_vigencia, atualizado, codigo_usuario_programacao) 
        VALUES (:produto, :preco, :data, 0, :usuario)");
    $stmt->execute([
        ':produto' => $p['codigo'],
        ':preco' => $novoPreco,
        ':data' => $dataVigencia,
        ':usuario' => $usuario
    ]);
}

header("Location: index.php?msg=Preços programados com sucesso");

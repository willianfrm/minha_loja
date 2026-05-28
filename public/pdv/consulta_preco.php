<?php
session_start();
include __DIR__ . "/../config/db.php";

$codigo = $_POST['codigo'] ?? null;
if (!$codigo) {
    echo json_encode(['erro' => 'Código não informado']);
    exit;
}

// Consulta produto + estoque + ean
$stmt = $pdo->prepare("
    SELECT p.codigo,
           CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) AS descricao,
           e.preco_vigente,
           e.preco_oferta
    FROM produto p
    JOIN familia f ON p.codigo_familia = f.codigo
    JOIN estoque e ON e.codigo_produto = p.codigo
    LEFT JOIN produto_eans pe ON pe.codigo_produto = p.codigo
    WHERE p.codigo = :codigo OR pe.codigo_ean = :codigo
    LIMIT 1
");
$stmt->execute([':codigo' => $codigo]);
$produto = $stmt->fetch();

if (!$produto) {
    echo json_encode(['erro' => 'Produto não encontrado']);
    exit;
}

$preco = $produto['preco_vigente'];
$descricao = $produto['descricao'];
$oferta = false;

if (!empty($produto['preco_oferta']) && $produto['preco_oferta'] > 0) {
    $preco = $produto['preco_oferta'];
    $oferta = true;
}

echo json_encode([
    'sucesso' => true,
    'descricao' => $descricao,
    'preco' => $preco,
    'oferta' => $oferta
]);

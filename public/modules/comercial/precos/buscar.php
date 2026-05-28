<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit;
}
include __DIR__ . "/../../../config/db.php";

$codigoProduto = $_POST['codigo_produto'] ?? '';
$codigoFamilia = $_POST['codigo_familia'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$ean = $_POST['ean'] ?? '';

$sql = "SELECT p.codigo, f.codigo AS familia_codigo, 
               CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) AS descricao, 
               MIN(pe.codigo_ean) AS ean
        FROM produto p
        JOIN familia f ON p.codigo_familia = f.codigo
        LEFT JOIN produto_eans pe ON pe.codigo_produto = p.codigo
        WHERE 1=1";
$params = [];

if ($codigoProduto !== '') {
    $sql .= " AND p.codigo = :codigoProduto";
    $params[':codigoProduto'] = $codigoProduto;
}
if ($codigoFamilia !== '') {
    $sql .= " AND f.codigo = :codigoFamilia";
    $params[':codigoFamilia'] = $codigoFamilia;
}
if ($descricao !== '') {
    $sql .= " AND CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) LIKE :descricao";
    $params[':descricao'] = "%$descricao%";
}
if ($ean !== '') {
    $sql .= " AND pe.codigo_ean = :ean";
    $params[':ean'] = $ean;
}

$sql .= " GROUP BY p.codigo, f.codigo, f.descricao, p.descricao_complemento LIMIT 25";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($resultados);

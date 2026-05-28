<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

$codigo = $_POST['codigo'] ?? '';
$descricao = $_POST['descricao'] ?? '';

$sql = "SELECT p.codigo, 
               CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) AS descricao
        FROM produto p
        JOIN familia f ON p.codigo_familia = f.codigo
        WHERE 1=1";
$params = [];

if ($codigo !== '') {
    $sql .= " AND p.codigo = :codigo";
    $params[':codigo'] = $codigo;
}
if ($descricao !== '') {
    $sql .= " AND CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) LIKE :desc";
    $params[':desc'] = "%$descricao%";
}

$sql .= " ORDER BY f.descricao, p.descricao_complemento";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultados);

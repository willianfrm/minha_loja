<?php
session_start();
include __DIR__ . "/../config/db.php";

$nome = $_GET['nome'] ?? '';
if (strlen($nome) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.codigo,
           CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) AS nome
    FROM produto p
    JOIN familia f ON p.codigo_familia = f.codigo
    WHERE CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) LIKE :nome
    LIMIT 10
");
$stmt->execute([':nome' => "%$nome%"]);
$produtos = $stmt->fetchAll();

echo json_encode($produtos);

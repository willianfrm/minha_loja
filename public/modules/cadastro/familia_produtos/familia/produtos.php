<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

$familiaId = $_GET['family'] ?? null;
if (!$familiaId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT codigo, descricao_complemento, linha 
                       FROM produto 
                       WHERE codigo_familia = :familia 
                       ORDER BY descricao_complemento");
$stmt->execute([':familia' => $familiaId]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($produtos);

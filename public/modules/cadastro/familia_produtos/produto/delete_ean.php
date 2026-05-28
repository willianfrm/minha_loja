<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = $_POST['codigo_produto'] ?? null;
    $ean = $_POST['codigo_ean'] ?? null;

    if (!$produtoId || !$ean) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    // Verificar se o EAN existe para este produto
    $stmt = $pdo->prepare("SELECT codigo_ean FROM produto_eans WHERE codigo_produto = :produto AND codigo_ean = :ean");
    $stmt->execute([':produto' => $produtoId, ':ean' => $ean]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existe) {
        echo json_encode(['success' => false, 'message' => 'EAN não encontrado neste produto.']);
        exit;
    }

    // Excluir EAN
    $stmt = $pdo->prepare("DELETE FROM produto_eans WHERE codigo_produto = :produto AND codigo_ean = :ean");
    $stmt->execute([':produto' => $produtoId, ':ean' => $ean]);

    echo json_encode(['success' => true]);
}

<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = $_POST['codigo_produto'] ?? null;
    $descricao = strtoupper($_POST['descricao_complemento']) ?? null;
    $linha = isset($_POST['linha']) ? (int)$_POST['linha'] : 1;

    if (!$produtoId) {
        echo json_encode(['success' => false, 'message' => 'Produto inválido.']);
        exit;
    }

    // Atualizar produto
    $stmt = $pdo->prepare("UPDATE produto 
                           SET descricao_complemento = :desc, linha = :linha 
                           WHERE codigo = :id");
    $stmt->execute([
        ':desc' => $descricao,
        ':linha' => $linha,
        ':id' => $produtoId
    ]);

    echo json_encode(['success' => true]);
}

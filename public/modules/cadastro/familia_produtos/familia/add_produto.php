<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $familiaId = $_POST['codigo_familia'] ?? null;
    $descricao = strtoupper($_POST['descricao_complemento'] ?? '');
    $linha = true; // sempre ativo ao cadastrar

    if (!$familiaId) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO produto (codigo_familia, descricao_complemento, linha) 
                           VALUES (:familia, :desc, :linha)");
    $stmt->execute([
        ':familia' => $familiaId,
        ':desc' => $descricao,
        ':linha' => $linha
    ]);

    // Criar registro de estoque zerado
    $produtoId = $pdo->lastInsertId();
    $stmtEstoque = $pdo->prepare("INSERT INTO estoque (codigo_produto) VALUES (:prod)");
    $stmtEstoque->execute([':prod' => $produtoId]);

    echo json_encode(['success' => true, 'id' => $produtoId]);
}

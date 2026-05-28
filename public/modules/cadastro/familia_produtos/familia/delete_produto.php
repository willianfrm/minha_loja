<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'] ?? null;
    if (!$codigo) {
        echo json_encode(['success' => false, 'message' => 'Produto inválido.']);
        exit;
    }

    // Verificar movimentações
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM movimentacao_produto WHERE codigo_produto = :codigo");
    $stmt->execute([':codigo' => $codigo]);
    $temMov = $stmt->fetchColumn();

    if ($temMov > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir: produto possui movimentações.']);
        exit;
    }
	
	// Excluir eans vinculado
    $stmtEans = $pdo->prepare("DELETE FROM produto_eans WHERE codigo_produto = :codigo");
    $stmtEans->execute([':codigo' => $codigo]);
	
	// Excluir estoque vinculado
    $stmtEstoque = $pdo->prepare("DELETE FROM estoque WHERE codigo_produto = :codigo");
    $stmtEstoque->execute([':codigo' => $codigo]);

    // Excluir produto
    $stmt = $pdo->prepare("DELETE FROM produto WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $codigo]);

    echo json_encode(['success' => true]);
}

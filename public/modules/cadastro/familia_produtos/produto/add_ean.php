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
	
	// Verificar se o EAN contém apenas números
	if (!ctype_digit($ean)) {
		echo json_encode(['success' => false, 'message' => 'EAN deve conter apenas números.']);
		exit;
	}

    // Verificar se o EAN já existe em outro produto
    $stmt = $pdo->prepare("SELECT codigo_produto FROM produto_eans WHERE codigo_ean = :ean");
    $stmt->execute([':ean' => $ean]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        echo json_encode(['success' => false, 'message' => 'Este EAN já está vinculado a outro produto.']);
        exit;
    }

    // Inserir novo EAN
    $stmt = $pdo->prepare("INSERT INTO produto_eans (codigo_produto, codigo_ean) VALUES (:produto, :ean)");
    $stmt->execute([':produto' => $produtoId, ':ean' => $ean]);

    echo json_encode(['success' => true]);
}

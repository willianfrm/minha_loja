<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = $_POST['codigo_produto'] ?? null;
    $novaFamilia = $_POST['codigo_familia'] ?? null;

    if (!$produtoId || !$novaFamilia) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
        exit;
    }

    // Verificar se a família existe
    $stmt = $pdo->prepare("SELECT descricao FROM familia WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $novaFamilia]);
    $familia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$familia) {
        echo json_encode(['success' => false, 'message' => 'Família não encontrada.']);
        exit;
    }

    // Atualizar produto para nova família
    $stmt = $pdo->prepare("UPDATE produto SET codigo_familia = :familia WHERE codigo = :produto");
    $stmt->execute([':familia' => $novaFamilia, ':produto' => $produtoId]);

    echo json_encode(['success' => true]);
}

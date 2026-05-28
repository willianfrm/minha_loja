<?php
session_start();
include __DIR__ . "/../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];

    // Verificar se tem filhos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categoria WHERE codigo_cat_pai = :codigo");
    $stmt->execute([':codigo' => $codigo]);
    $temFilhos = $stmt->fetchColumn();

    // Verificar se tem produtos vinculados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM familia WHERE codigo_categoria = :codigo");
    $stmt->execute([':codigo' => $codigo]);
    $temProdutos = $stmt->fetchColumn();

    if ($temFilhos == 0 && $temProdutos == 0) {
        $stmt = $pdo->prepare("DELETE FROM categoria WHERE codigo = :codigo");
        $stmt->execute([':codigo' => $codigo]);
    } else {
        echo "<script>alert('Não é possível excluir: possui filhos ou produtos vinculados.'); window.location.href='index.php';</script>";
        exit;
    }
}
header("Location: index.php");
exit;
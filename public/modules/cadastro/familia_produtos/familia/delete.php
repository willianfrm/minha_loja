<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];

    // Verificar se existem produtos vinculados a esta família
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produto WHERE codigo_familia = :codigo");
    $stmt->execute([':codigo' => $codigo]);
    $temProdutos = $stmt->fetchColumn();

    if ($temProdutos > 0) {
        echo "<script>alert('Não é possível excluir: esta família possui produtos vinculados.'); window.location.href='index.php';</script>";
        exit;
    }

    // Se não tiver produtos, pode excluir
    $stmt = $pdo->prepare("DELETE FROM familia WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $codigo]);

    header("Location: index.php");
    exit;
}
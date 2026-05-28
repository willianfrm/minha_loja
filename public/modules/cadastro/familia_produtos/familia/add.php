<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['codigo_categoria'];
    $descricao = strtoupper($_POST['descricao']);
    $embalagem = $_POST['embalagem'];
    $margem = $_POST['margem'] ?: null;

    $stmt = $pdo->prepare("INSERT INTO familia (codigo_categoria, descricao, embalagem, margem) VALUES (:cat, :desc, :emb, :margem)");
    $stmt->execute([
        ':cat' => $categoria,
        ':desc' => $descricao,
        ':emb' => $embalagem,
        ':margem' => $margem
    ]);
}
$id = $pdo->lastInsertId();
header("Location: index.php?codigo=".$id);
exit;
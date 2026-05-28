<?php
session_start();
include __DIR__ . "/../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'];
    $pai = $_POST['codigo_cat_pai'] ?: null;
    $nivel = $_POST['nivel'];
    $margem = $_POST['margem'] ?: null;

    $stmt = $pdo->prepare("INSERT INTO categoria (descricao, codigo_cat_pai, nivel, margem) VALUES (:descricao, :pai, :nivel, :margem)");
    $stmt->execute([
        ':descricao' => $descricao,
        ':pai' => $pai,
        ':nivel' => $nivel,
        ':margem' => $margem
    ]);
}
header("Location: index.php");
exit;
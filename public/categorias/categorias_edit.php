<?php
session_start();
include "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['codigo'])) {
    $stmt = $pdo->prepare("SELECT * FROM categoria WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $_GET['codigo']]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $descricao = $_POST['descricao'];
    $margem = $_POST['margem'] ?: null;

    $stmt = $pdo->prepare("UPDATE categoria SET descricao = :descricao, margem = :margem WHERE codigo = :codigo");
    $stmt->execute([
        ':descricao' => $descricao,
        ':margem' => $margem,
        ':codigo' => $codigo
    ]);
    header("Location: index.php");
    exit;
}
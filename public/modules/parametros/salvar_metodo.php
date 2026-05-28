<?php
session_start();
include __DIR__ . "/../../config/db.php";

$descricao = trim($_POST['descricao'] ?? '');

if (!$descricao) {
	echo "<script>alert('Descrição inválida.'); window.location.href='index.php';</script>";
	exit;
}

// Insere novo método sempre como ativo
$stmt = $pdo->prepare("INSERT INTO metodo_pagamento (descricao, ativo) VALUES (:descricao, 1)");
$stmt->execute([':descricao' => $descricao]);

header("Location: index.php");

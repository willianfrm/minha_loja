<?php
session_start();
include __DIR__ . "/../../config/db.php";

$codigo = $_GET['codigo'] ?? null;
if (!$codigo) {
	echo "<script>alert('Código inválido.'); window.location.href='index.php';</script>";
	exit;
}

// Não permite excluir ou inativar o método padrão (Dinheiro)
if ($codigo == 1) {
    echo "<script>alert('Não é possível alterar esse método.'); window.location.href='index.php';</script>";
    exit;
}

// Para outros métodos
$stmt = $pdo->prepare("UPDATE metodo_pagamento SET ativo = CASE WHEN ativo=1 THEN 0 ELSE 1 END WHERE codigo = :codigo");
$stmt->execute([':codigo' => $codigo]);

header("Location: index.php");

<?php
session_start();
include __DIR__ . "/../../config/db.php";

$codigo = $_GET['codigo'] ?? null;
if (!$codigo) { 
	echo "<script>alert('Códgio invalido.'); window.location.href='index.php';</script>";
	exit;
	}

// Verifica se já foi usado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM documento_pagamentos WHERE codigo_metodo_pagamento = :codigo");
$stmt->execute([':codigo' => $codigo]);
$usado = $stmt->fetchColumn();

if ($usado > 0 || $codigo == 1) {
    echo "<script>alert('Este método não pode ser excluído.'); window.location.href='index.php';</script>";
	exit;
}

$stmt = $pdo->prepare("DELETE FROM metodo_pagamento WHERE codigo = :codigo");
$stmt->execute([':codigo' => $codigo]);

header("Location: index.php");

<?php
session_start();
include __DIR__ . "/../../../config/db.php";

$usuarioLogado = (int)$_SESSION['usuario'];
$codigo = (int)$_POST['codigo'];

// Não pode excluir admin padrão
if ($codigo === 1) {
    echo "<script>alert('Não é permitido excluir o usuário admin padrão.'); window.history.back();</script>";
	exit;
}

// Não pode excluir o usuário logado
if ($codigo === $usuarioLogado) {
    echo "<script>alert('Você não pode se auto-excluir.'); window.history.back();</script>";
	exit;
}

$stmt = $pdo->prepare("DELETE FROM usuario WHERE codigo=:codigo");
$stmt->execute([':codigo'=>$codigo]);

header("Location: index.php");

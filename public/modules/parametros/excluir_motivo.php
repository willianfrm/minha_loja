<?php
session_start();
include __DIR__ . "/../../config/db.php";

$codigo = $_GET['codigo'] ?? null;

if (!$codigo) {
    echo "<script>alert('Motivo inválido.'); window.location.href='index.php';</script>";
    exit;
}

// Bloquear motivos padrão
if ($codigo == 1 || $codigo == 2) {
    echo "<script>alert('Este motivo é padrão do sistema e não pode ser excluído.'); window.location.href='index.php';</script>";
    exit;
}

// Verificar se há documentos vinculados
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM documento_movimentacao WHERE codigo_motivo=:codigo");
$stmtCheck->execute([':codigo'=>$codigo]);
$temDocs = $stmtCheck->fetchColumn();

if ($temDocs > 0) {
    echo "<script>alert('Não é possível excluir este motivo pois já existem documentos vinculados.'); window.location.href='index.php';</script>";
    exit;
}

// Excluir motivo
$stmtDel = $pdo->prepare("DELETE FROM motivo_movimentacoes WHERE codigo=:codigo");
$stmtDel->execute([':codigo'=>$codigo]);

echo "<script>alert('Motivo excluído com sucesso!'); window.location.href='index.php';</script>";
exit;

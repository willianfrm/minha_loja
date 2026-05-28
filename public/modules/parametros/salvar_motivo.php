<?php
session_start();
include __DIR__ . "/../../config/db.php";

$nome = $_POST['nome_motivo'] ?? null;
$descricao = $_POST['descricao_motivo'] ?? null;
$tipo = $_POST['tipo'] ?? null;
$tipoValor = $_POST['tipo_valor'] ?? 'MANUAL';
$atualiza = $_POST['atualiza_custo'] ?? 0;

if (!$nome || !$descricao || !$tipo) {
    echo "<script>alert('Dados inválidos.'); window.location.href='index.php';</script>";
    exit;
}

try {
    // Regra: se for Saída (S), força atualiza_custo = 0
    if ($tipo === 'S') {
        $atualiza = 0;
    }

    $stmt = $pdo->prepare("INSERT INTO motivo_movimentacoes 
        (nome_motivo, descricao_motivo, tipo, tipo_valor, atualiza_custo) 
        VALUES (:nome, :descricao, :tipo, :valor, :atualiza)");
    $stmt->execute([
        ':nome' => $nome,
        ':descricao' => $descricao,
        ':tipo' => $tipo,
        ':valor' => $tipoValor,
        ':atualiza' => $atualiza
    ]);

    echo "<script>alert('Motivo cadastrado com sucesso!'); window.location.href='index.php';</script>";
    exit;
} catch (Exception $e) {
    echo "<script>alert('Erro ao cadastrar motivo: " . $e->getMessage() . "'); window.location.href='index.php';</script>";
    exit;
}
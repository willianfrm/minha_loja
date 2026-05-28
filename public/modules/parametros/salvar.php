<?php
session_start();
include __DIR__ . "/../../config/db.php";

$tipoCustoRelatorio = $_POST['tipo_custo_relatorio'] ?? 'MEDIO';
$tipoCustoPrecificacao = $_POST['tipo_custo_precificacao'] ?? 'MEDIO';

try {
    $stmt = $pdo->prepare("UPDATE parametros_sistema 
                           SET tipo_custo_relatorio=:rel, tipo_custo_precificacao=:prec 
                           LIMIT 1");
    $stmt->execute([
        ':rel' => $tipoCustoRelatorio,
        ':prec' => $tipoCustoPrecificacao
    ]);

    echo "<script>alert('Parâmetros atualizados com sucesso!'); window.location.href='index.php';</script>";
    exit;
} catch (Exception $e) {
    echo "<script>alert('Erro ao atualizar parâmetros: " . $e->getMessage() . "'); window.location.href='index.php';</script>";
    exit;
}
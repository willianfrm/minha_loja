<?php
session_start();
include __DIR__ . "/../config/db.php";
include __DIR__ . "/pdv_session.php";

$codigoMetodo = $_POST['codigo_metodo'] ?? null;
$valor = (float)($_POST['valor'] ?? 0);

if (!$codigoMetodo || $valor <= 0) {
    echo json_encode(['erro' => 'Método de pagamento ou valor inválido']);
    exit;
}

// Busca descrição do método
$stmt = $pdo->prepare("SELECT descricao FROM metodo_pagamento WHERE codigo = :codigo AND ativo = 1");
$stmt->execute([':codigo' => $codigoMetodo]);
$metodo = $stmt->fetch();

if (!$metodo) {
    echo json_encode(['erro' => 'Método de pagamento inválido ou inativo']);
    exit;
}

// Checa saldo restante
$saldoRestante = calcularTotal() - calcularSaldoPago();
$troco = 0;

if ($codigoMetodo == 1 && $valor > $saldoRestante) {
    // Se for dinheiro, aceita valor maior e calcula troco
    $troco = $valor - $saldoRestante;
    $valor = $saldoRestante;
} elseif ($valor > $saldoRestante) {
    echo json_encode(['erro' => 'Valor maior que saldo restante']);
    exit;
}

// Registra pagamento
registrarPagamento($codigoMetodo, $metodo['descricao'], $valor);

// Retorna resposta
echo json_encode([
    'sucesso' => true,
    'pagamentos' => $_SESSION['pdv_pagamentos'],
    'totalVenda' => calcularTotal(),
    'saldoPago' => calcularSaldoPago(),
    'saldoRestante' => calcularTotal() - calcularSaldoPago(),
    'troco' => $troco
]);

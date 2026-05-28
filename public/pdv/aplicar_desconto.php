<?php
session_start();
include __DIR__ . "/pdv_session.php";

$itemIndex = $_POST['index'] ?? null;
$desconto = (float)($_POST['desconto'] ?? 0);

if ($itemIndex === null || !isset($_SESSION['pdv_itens'][$itemIndex])) {
    echo json_encode(['erro' => 'Item inválido']);
    exit;
}

// Bloqueio de alteração após pagamento iniciado
if (!empty($_SESSION['pdv_pagamentos'])) {
    echo json_encode(['erro' => 'Não é possível adicionar descontos após iniciar pagamento, termine o pagamento ou cancele a venda.']);
    exit;
}

$item = $_SESSION['pdv_itens'][$itemIndex];

// Valida desconto
if ($desconto <= 0 || $desconto >= $item['valor_unitario']) {
    echo json_encode(['erro' => 'Desconto inválido']);
    exit;
}

// Aplica desconto
$item['desconto'] = $desconto;
$item['subtotal'] = ($item['valor_unitario'] - $desconto) * $item['quantidade'];
$_SESSION['pdv_itens'][$itemIndex] = $item;

// Retorna itens e total
echo json_encode([
    'sucesso' => true,
    'itens' => $_SESSION['pdv_itens'],
    'total' => calcularTotal()
]);

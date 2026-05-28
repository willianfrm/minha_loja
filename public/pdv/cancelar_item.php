<?php
session_start();
include __DIR__ . "/../config/db.php";   // conexão com banco
include __DIR__ . "/pdv_session.php";    // funções de sessão

$index = $_POST['index'] ?? null;

if ($index === null) {
    echo json_encode(['erro' => 'Número do item não informado']);
    exit;
}

// Bloqueio de alteração após pagamento iniciado
if (!empty($_SESSION['pdv_pagamentos'])) {
    echo json_encode(['erro' => 'Não é possível cancelar itens após iniciar pagamento, termine o pagamento ou cancele a venda.']);
    exit;
}

// Cancela item pelo índice (lembrando que começa em 0)
cancelarItem((int)$index);

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Item cancelado com sucesso',
    'itens' => $_SESSION['pdv_itens'],
    'total' => calcularTotal()
]);

<?php
session_start();
include __DIR__ . "/../config/db.php";   // conexão com banco
include __DIR__ . "/pdv_session.php";    // funções de sessão

// Cancela a venda atual (limpa itens e pagamentos da sessão)
cancelarVenda();

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Venda cancelada com sucesso',
    'itens' => $_SESSION['pdv_itens'],
    'pagamentos' => $_SESSION['pdv_pagamentos'],
    'total' => calcularTotal(),
    'saldoPago' => calcularSaldoPago()
]);

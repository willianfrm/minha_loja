<?php
session_start();
include __DIR__ . "/pdv_session.php";

echo json_encode([
    'saldoPago' => calcularSaldoPago(),
    'totalVenda' => calcularTotal(),
    'saldoRestante' => calcularTotal() - calcularSaldoPago(),
    'itens' => $_SESSION['pdv_itens'],
    'pagamentos' => $_SESSION['pdv_pagamentos']
]);

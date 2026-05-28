<?php
session_start();
include __DIR__ . "/pdv_session.php";

$itens = $_SESSION['pdv_itens'] ?? [];

echo json_encode([
    'itens' => $itens,
    'total' => calcularTotal()
]);

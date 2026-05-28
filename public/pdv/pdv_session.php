<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Inicializa arrays se não existirem
if (!isset($_SESSION['pdv_itens'])) {
    $_SESSION['pdv_itens'] = [];
}
if (!isset($_SESSION['pdv_pagamentos'])) {
    $_SESSION['pdv_pagamentos'] = [];
}

// Adicionar item
function adicionarItem($codigoProduto, $descricao, $quantidade, $valorUnitario, $desconto = 0) {
    $subtotal = ($valorUnitario - $desconto) * $quantidade;
    $_SESSION['pdv_itens'][] = [
        'codigo_produto' => $codigoProduto,
        'descricao' => $descricao,
        'quantidade' => $quantidade,
        'valor_unitario' => $valorUnitario,
        'desconto' => $desconto,
        'subtotal' => $subtotal
    ];
}

// Cancelar item
function cancelarItem($index) {
    if (isset($_SESSION['pdv_itens'][$index])) {
        unset($_SESSION['pdv_itens'][$index]);
        $_SESSION['pdv_itens'] = array_values($_SESSION['pdv_itens']);
    }
}

// Cancelar venda
function cancelarVenda() {
    $_SESSION['pdv_itens'] = [];
    $_SESSION['pdv_pagamentos'] = [];
}

// Registrar pagamento (evita duplicação)
function registrarPagamento($codigoMetodo, $descricao, $valor) {
    foreach ($_SESSION['pdv_pagamentos'] as &$pg) {
        if ($pg['codigo_metodo'] == $codigoMetodo) {
            $pg['valor'] += $valor;
            return;
        }
    }
    $_SESSION['pdv_pagamentos'][] = [
        'codigo_metodo' => $codigoMetodo,
        'descricao' => $descricao,
        'valor' => $valor
    ];
}

// Calcular total
function calcularTotal() {
    return (float)array_sum(array_column($_SESSION['pdv_itens'], 'subtotal'));
}

// Calcular saldo pago
function calcularSaldoPago() {
    return (float)array_sum(array_column($_SESSION['pdv_pagamentos'], 'valor'));
}
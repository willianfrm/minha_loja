<?php
session_start();
include __DIR__ . "/../config/db.php";       // conexão com banco
include __DIR__ . "/pdv_session.php";  // funções de sessão

$itens = $_SESSION['pdv_itens'] ?? [];
$pagamentos = $_SESSION['pdv_pagamentos'] ?? [];
$totalVenda = calcularTotal();
$saldoPago = calcularSaldoPago();

// Validações básicas
if (empty($itens)) {
    echo json_encode(['erro' => 'Nenhum item na venda']);
    exit;
}
if ($saldoPago < $totalVenda) {
    echo json_encode(['erro' => 'Pagamento insuficiente']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Cria documento da venda
    $stmt = $pdo->prepare("INSERT INTO documento_movimentacao 
        (codigo_motivo, data_hora, valor_total, codigo_usuario) 
        VALUES (1, NOW(), :total, :usuario)");
    $stmt->execute([
        ':total' => $totalVenda,
        ':usuario' => $_SESSION['usuario']
    ]);
    $docId = $pdo->lastInsertId();

    // Insere itens
    $stmtItem = $pdo->prepare("INSERT INTO movimentacao_produto 
        (codigo_documento, codigo_produto, quantidade, valor_unitario, valor_total)
        VALUES (:doc, :prod, :qtd, :unit, :sub)");
    foreach ($itens as $item) {
        $stmtItem->execute([
            ':doc' => $docId,
            ':prod' => $item['codigo_produto'],
            ':qtd' => $item['quantidade'],
            ':unit' => $item['valor_unitario'],
            ':sub' => $item['subtotal']
        ]);

        // Atualiza estoque (saída)
        $stmtEst = $pdo->prepare("UPDATE estoque 
            SET estoque = estoque - :qtd 
            WHERE codigo_produto = :prod");
        $stmtEst->execute([
            ':qtd' => $item['quantidade'],
            ':prod' => $item['codigo_produto']
        ]);
    }

    // Insere pagamentos
    $stmtPag = $pdo->prepare("INSERT INTO documento_pagamentos 
        (codigo_documento, codigo_metodo_pagamento, valor)
        VALUES (:doc, :metodo, :valor)");
    foreach ($pagamentos as $pag) {
        $stmtPag->execute([
            ':doc' => $docId,
            ':metodo' => $pag['codigo_metodo'],
            ':valor' => $pag['valor']
        ]);
    }

    $pdo->commit();

    // Limpa sessão
    cancelarVenda();

    echo json_encode(['sucesso' => true, 'documento' => $docId]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['erro' => $e->getMessage()]);
}
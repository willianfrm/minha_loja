<?php
session_start();
include __DIR__ . "/../config/db.php";   // conexão com banco
include __DIR__ . "/pdv_session.php";    // funções de sessão

header('Content-Type: application/json');

$codigo = $_POST['codigo'] ?? null;
$qtd = $_POST['quantidade'] ?? 1;

if (!$codigo) {
    echo json_encode(['erro' => 'Código do produto não informado']);
    exit;
}

// Bloqueio de alteração após pagamento iniciado
if (!empty($_SESSION['pdv_pagamentos'])) {
    echo json_encode(['erro' => 'Não é possível adicionar produtos após iniciar pagamento, termine o pagamento ou cancele a venda.']);
    exit;
}

try {
    // Busca produto pelo código interno OU pelo EAN
    $stmt = $pdo->prepare("
        SELECT p.codigo,
               p.descricao_complemento,
               f.descricao AS familia_descricao,
               e.preco_vigente,
               e.preco_oferta
        FROM produto p
        JOIN familia f ON f.codigo = p.codigo_familia
        JOIN estoque e ON e.codigo_produto = p.codigo
        WHERE p.codigo = :codigo
           OR EXISTS (
               SELECT 1 FROM produto_eans pe
               WHERE pe.codigo_produto = p.codigo
                 AND pe.codigo_ean = :codigo
           )
    ");
    $stmt->execute([':codigo' => $codigo]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        echo json_encode(['erro' => 'Produto não encontrado: ' . $codigo]);
        exit;
    }

    // Monta descrição completa: família + complemento
    $descricaoCompleta = trim(($produto['familia_descricao'] ?? '') . ' ' . ($produto['descricao_complemento'] ?? ''));

    // Verifica preço de oferta
    $preco = null;
    if (!empty($produto['preco_oferta']) && $produto['preco_oferta'] > 0) {
        $preco = (float)$produto['preco_oferta'];
    } else {
        $preco = (float)$produto['preco_vigente'];
    }

    // Adiciona item na sessão
    adicionarItem(
        (int)$produto['codigo'],
        $descricaoCompleta ?: "Produto sem descrição",
        (float)$qtd,
        $preco
    );

    // Retorna resposta para o front-end
    echo json_encode([
        'sucesso' => true,
        'itens' => $_SESSION['pdv_itens'],
        'total' => calcularTotal()
    ]);
} catch (Exception $e) {
    echo json_encode(['erro' => 'Erro no servidor: ' . $e->getMessage()]);
}
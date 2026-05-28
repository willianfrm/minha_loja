<?php
include __DIR__ . "/../../../config/db.php";

$codigo = $_GET['codigo'] ?? null;
$ean = $_GET['ean'] ?? null;
$motivo = $_GET['motivo'] ?? null; // motivo selecionado no modal

// Buscar tipo_valor do motivo
$stmtMotivo = $pdo->prepare("SELECT tipo_valor FROM motivo_movimentacoes WHERE codigo=:motivo");
$stmtMotivo->execute([':motivo' => $motivo]);
$motivoInfo = $stmtMotivo->fetch(PDO::FETCH_ASSOC);
$tipoValor = $motivoInfo['tipo_valor'] ?? 'MANUAL';

$produto = null;

if ($codigo) {
    $stmt = $pdo->prepare("
        SELECT p.codigo, CONCAT(f.descricao, ' ', p.descricao_complemento) AS descricao,
               e.preco_vigente, e.custo_medio, e.custo_ult_entrada
        FROM produto p
        JOIN familia f ON p.codigo_familia = f.codigo
        JOIN estoque e ON e.codigo_produto = p.codigo
        WHERE p.codigo = :codigo
    ");
    $stmt->execute([':codigo' => $codigo]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($ean) {
    $stmt = $pdo->prepare("
        SELECT p.codigo, CONCAT(f.descricao, ' ', p.descricao_complemento) AS descricao,
               e.preco_vigente, e.custo_medio, e.custo_ult_entrada
        FROM produto_eans pe
        JOIN produto p ON pe.codigo_produto = p.codigo
        JOIN familia f ON p.codigo_familia = f.codigo
        JOIN estoque e ON e.codigo_produto = p.codigo
        WHERE pe.codigo_ean = :ean
    ");
    $stmt->execute([':ean' => $ean]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($produto) {
    $valorUnitario = null;

    if ($tipoValor === 'PRECO_VENDA') {
        $valorUnitario = $produto['preco_vigente'];
    } elseif ($tipoValor === 'CUSTO') {
        // Buscar parâmetro global
        $param = $pdo->query("SELECT tipo_custo_relatorio FROM parametros_sistema LIMIT 1")->fetchColumn();
        if ($param === 'MEDIO') {
            $valorUnitario = $produto['custo_medio'];
        } else {
            $valorUnitario = $produto['custo_ult_entrada'];
        }
    }

    echo json_encode([
        'ok' => true,
        'codigo' => $produto['codigo'],
        'descricao' => $produto['descricao'],
        'valor_unitario' => $valorUnitario,
        'tipo_valor' => $tipoValor
    ]);
} else {
    echo json_encode(['ok' => false]);
}

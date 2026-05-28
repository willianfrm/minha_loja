<?php
session_start();
include __DIR__ . "/../../../config/db.php";

$idItem = $_POST['id'] ?? null;
$codigoDoc = $_POST['codigo_documento'] ?? null;

if (!$idItem || !$codigoDoc) {
    echo "<script>alert('Item inválido.'); window.location.href='detalhes.php?doc={$codigoDoc}';</script>";
    exit;
}

try {
    $pdo->beginTransaction();

    // Buscar documento e motivo
    $sqlDoc = "SELECT d.*, m.tipo, m.atualiza_custo 
               FROM documento_movimentacao d
               JOIN motivo_movimentacoes m ON d.codigo_motivo = m.codigo
               WHERE d.codigo = :doc";
    $stmtDoc = $pdo->prepare($sqlDoc);
    $stmtDoc->execute([':doc' => $codigoDoc]);
    $doc = $stmtDoc->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        throw new Exception("Documento não encontrado.");
    }

    // Buscar item
    $sqlItem = "SELECT * FROM movimentacao_produto WHERE id=:id AND codigo_documento=:doc";
    $stmtItem = $pdo->prepare($sqlItem);
    $stmtItem->execute([':id' => $idItem, ':doc' => $codigoDoc]);
    $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item não encontrado.");
    }

    $codigoProduto = $item['codigo_produto'];
    $quantidade = $item['quantidade'];
    $valorUnitario = $item['valor_unitario'];

    // Buscar estoque atual
    $stmtEst = $pdo->prepare("SELECT estoque, custo_medio, custo_ult_entrada FROM estoque WHERE codigo_produto=:prod");
    $stmtEst->execute([':prod' => $codigoProduto]);
    $est = $stmtEst->fetch(PDO::FETCH_ASSOC);

    if ($est) {
        if ($doc['tipo'] === 'E') {
            // Entrada → reverter (subtrair)
            $novoEstoque = $est['estoque'] - $quantidade;

            if ($doc['atualiza_custo']) {
                // Verificar se há entradas posteriores
                $sqlPosterior = "SELECT COUNT(*) FROM documento_movimentacao d
                                 JOIN movimentacao_produto mp ON d.codigo = mp.codigo_documento
                                 JOIN motivo_movimentacoes m ON d.codigo_motivo = m.codigo
                                 WHERE mp.codigo_produto = :prod
                                   AND d.data_hora > :data
                                   AND m.tipo = 'E'
                                   AND m.atualiza_custo = 1";
                $stmtPosterior = $pdo->prepare($sqlPosterior);
                $stmtPosterior->execute([
                    ':prod' => $codigoProduto,
                    ':data' => $doc['data_hora']
                ]);
                $temPosterior = $stmtPosterior->fetchColumn();

                if (!$temPosterior) {
                    // Recalcular custo médio e última entrada com base nas anteriores
                    $sqlAnterior = "SELECT mp.quantidade, mp.valor_unitario, d.data_hora
                                    FROM movimentacao_produto mp
                                    JOIN documento_movimentacao d ON mp.codigo_documento = d.codigo
                                    JOIN motivo_movimentacoes m ON d.codigo_motivo = m.codigo
                                    WHERE mp.codigo_produto = :prod
                                      AND d.data_hora < :data
                                      AND m.tipo = 'E'
                                      AND m.atualiza_custo = 1
                                    ORDER BY d.data_hora DESC";
                    $stmtAnterior = $pdo->prepare($sqlAnterior);
                    $stmtAnterior->execute([
                        ':prod' => $codigoProduto,
                        ':data' => $doc['data_hora']
                    ]);
                    $anteriores = $stmtAnterior->fetchAll(PDO::FETCH_ASSOC);

                    if ($anteriores) {
                        $totalQtd = 0;
                        $totalValor = 0;
                        foreach ($anteriores as $ant) {
                            $totalQtd += $ant['quantidade'];
                            $totalValor += $ant['quantidade'] * $ant['valor_unitario'];
                        }
                        $novoCustoMedio = $totalQtd > 0 ? ($totalValor / $totalQtd) : 0;
                        $novoCustoUlt = $anteriores[0]['valor_unitario'];
                    } else {
                        $novoCustoMedio = 0;
                        $novoCustoUlt = 0;
                    }

                    $sqlUpd = "UPDATE estoque SET estoque=:est, custo_medio=:custoMedio, custo_ult_entrada=:custoUlt WHERE codigo_produto=:prod";
                    $stmtUpd = $pdo->prepare($sqlUpd);
                    $stmtUpd->execute([
                        ':est' => $novoEstoque,
                        ':custoMedio' => $novoCustoMedio,
                        ':custoUlt' => $novoCustoUlt,
                        ':prod' => $codigoProduto
                    ]);
                } else {
                    $sqlUpd = "UPDATE estoque SET estoque=:est WHERE codigo_produto=:prod";
                    $stmtUpd = $pdo->prepare($sqlUpd);
                    $stmtUpd->execute([
                        ':est' => $novoEstoque,
                        ':prod' => $codigoProduto
                    ]);
                }
            } else {
                $sqlUpd = "UPDATE estoque SET estoque=:est WHERE codigo_produto=:prod";
                $stmtUpd = $pdo->prepare($sqlUpd);
                $stmtUpd->execute([
                    ':est' => $novoEstoque,
                    ':prod' => $codigoProduto
                ]);
            }
        } else {
            // Saída → reverter (somar)
            $novoEstoque = $est['estoque'] + $quantidade;
            $sqlUpd = "UPDATE estoque SET estoque=:est WHERE codigo_produto=:prod";
            $stmtUpd = $pdo->prepare($sqlUpd);
            $stmtUpd->execute([
                ':est' => $novoEstoque,
                ':prod' => $codigoProduto
            ]);
        }
    }

    // Excluir item
    $stmtDel = $pdo->prepare("DELETE FROM movimentacao_produto WHERE id=:id");
    $stmtDel->execute([':id' => $idItem]);

    // Atualizar valor total do documento
    $stmtTotal = $pdo->prepare("SELECT SUM(valor_total) FROM movimentacao_produto WHERE codigo_documento=:doc");
    $stmtTotal->execute([':doc' => $codigoDoc]);
    $novoTotal = $stmtTotal->fetchColumn();

    if ($novoTotal > 0) {
        $stmtUpdDoc = $pdo->prepare("UPDATE documento_movimentacao SET valor_total=:total WHERE codigo=:doc");
        $stmtUpdDoc->execute([':total' => $novoTotal, ':doc' => $codigoDoc]);
		$pdo->commit();
		echo "<script>alert('Produto estornado com sucesso!'); window.location.href='detalhes.php?doc={$codigoDoc}';</script>";
    } else {
        // Se não restarem itens → excluir documento também
        $stmtDelDoc = $pdo->prepare("DELETE FROM documento_movimentacao WHERE codigo=:doc");
        $stmtDelDoc->execute([':doc' => $codigoDoc]);
		$pdo->commit();
		echo "<script>alert('Produto estornado e documento excluído pois não havia mais itens.'); window.location.href='index.php';</script>";
    }
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "<script>alert('Erro ao estornar produto: " . $e->getMessage() . "'); window.location.href='detalhes.php?doc={$codigoDoc}';</script>";
    exit;
}

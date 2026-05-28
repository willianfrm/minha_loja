<?php
session_start();
include __DIR__ . "/../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	if (empty($_POST['codigo_produto'])) {
		echo "<script>alert('Não é permitido salvar documento sem produtos.'); window.location.href='index.php';</script>";
		exit;
	}

    try {
        $pdo->beginTransaction();

        $codigoMotivo = $_POST['codigo_motivo'];
        $dataHora = $_POST['data_hora'];
        $valorTotal = $_POST['valor_total'];
        $usuario = $_SESSION['usuario'];

        // Inserir documento
        $sql = "INSERT INTO documento_movimentacao (codigo_motivo, data_hora, valor_total, codigo_usuario)
                VALUES (:motivo, :dataHora, :valorTotal, :usuario)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':motivo' => $codigoMotivo,
            ':dataHora' => $dataHora,
            ':valorTotal' => $valorTotal,
            ':usuario' => $usuario
        ]);

        $codigoDocumento = $pdo->lastInsertId();

        // Inserir produtos vinculados
        if (!empty($_POST['codigo_produto'])) {
            foreach ($_POST['codigo_produto'] as $i => $codigoProduto) {
                $quantidade = $_POST['quantidade'][$i] ?? 0;
                $valorUnitario = $_POST['valor_unitario'][$i] ?? 0;
                $valorTotalItem = $quantidade * $valorUnitario;

                // Inserir item
                $sqlItem = "INSERT INTO movimentacao_produto (codigo_documento, codigo_produto, quantidade, valor_unitario, valor_total)
                            VALUES (:doc, :prod, :qtd, :unit, :total)";
                $stmtItem = $pdo->prepare($sqlItem);
                $stmtItem->execute([
                    ':doc' => $codigoDocumento,
                    ':prod' => $codigoProduto,
                    ':qtd' => $quantidade,
                    ':unit' => $valorUnitario,
                    ':total' => $valorTotalItem
                ]);

                // Atualizar estoque
                $sqlMotivo = "SELECT tipo, atualiza_custo FROM motivo_movimentacoes WHERE codigo=:motivo";
                $stmtMotivo = $pdo->prepare($sqlMotivo);
                $stmtMotivo->execute([':motivo' => $codigoMotivo]);
                $motivo = $stmtMotivo->fetch(PDO::FETCH_ASSOC);

                if ($motivo['tipo'] === 'E') {
                    // Entrada → aumenta estoque
                    $stmtEst = $pdo->prepare("SELECT estoque, custo_medio, custo_ult_entrada FROM estoque WHERE codigo_produto=:prod");
                    $stmtEst->execute([':prod' => $codigoProduto]);
                    $est = $stmtEst->fetch(PDO::FETCH_ASSOC);

                    if ($est) {
                        $novoEstoque = $est['estoque'] + $quantidade;
                        $novoCustoUlt = $est['custo_ult_entrada'];
                        $novoCustoMedio = $est['custo_medio'];

                        if ($motivo['atualiza_custo']) {
							$novoCustoUlt = $valorUnitario;
                            $novoCustoMedio = (($est['estoque'] * $est['custo_medio']) + ($quantidade * $valorUnitario)) / $novoEstoque;
                        }

                        $sqlUpd = "UPDATE estoque SET estoque=:est, custo_ult_entrada=:custoUlt, custo_medio=:custoMedio WHERE codigo_produto=:prod";
                        $stmtUpd = $pdo->prepare($sqlUpd);
                        $stmtUpd->execute([
                            ':est' => $novoEstoque,
                            ':custoUlt' => $novoCustoUlt,
                            ':custoMedio' => $novoCustoMedio,
                            ':prod' => $codigoProduto
                        ]);
                    }
                } else {
                    // Saída → diminui estoque
                    $stmtEst = $pdo->prepare("SELECT estoque FROM estoque WHERE codigo_produto=:prod");
                    $stmtEst->execute([':prod' => $codigoProduto]);
                    $est = $stmtEst->fetch(PDO::FETCH_ASSOC);

                    if ($est) {
                        $novoEstoque = $est['estoque'] - $quantidade;

                        $sqlUpd = "UPDATE estoque SET estoque=:est WHERE codigo_produto=:prod";
                        $stmtUpd = $pdo->prepare($sqlUpd);
                        $stmtUpd->execute([
                            ':est' => $novoEstoque,
                            ':prod' => $codigoProduto
                        ]);
                    }
                }
            }
        }

        $pdo->commit();
        echo "<script>alert('Movimentação registrada com sucesso!'); window.location.href='index.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro ao registrar movimentação: " . $e->getMessage() . "'); window.location.href='index.php';</script>";
        exit;
    }
}
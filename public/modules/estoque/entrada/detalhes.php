<?php
session_start();
include __DIR__ . "/../../../config/db.php";

$codigoDoc = $_GET['doc'] ?? null;
if (!$codigoDoc) {
    echo "<script>alert('Documento inválido.'); window.location.href='index.php';</script>";
    exit;
}

// Buscar documento
$sql = "SELECT d.*, u.nome AS usuario, m.nome_motivo, m.tipo
        FROM documento_movimentacao d
        JOIN usuario u ON d.codigo_usuario = u.codigo
        JOIN motivo_movimentacoes m ON d.codigo_motivo = m.codigo
        WHERE d.codigo = :doc";
$stmt = $pdo->prepare($sql);
$stmt->execute([':doc'=>$codigoDoc]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar produtos vinculados (com descrição completa)
$sqlItens = "SELECT mp.*, p.codigo AS codigo_produto,
                    CONCAT(f.descricao, ' ', p.descricao_complemento) AS descricao_completa
             FROM movimentacao_produto mp
             JOIN produto p ON mp.codigo_produto = p.codigo
             JOIN familia f ON p.codigo_familia = f.codigo
             WHERE mp.codigo_documento = :doc";
$stmtItens = $pdo->prepare($sqlItens);
$stmtItens->execute([':doc'=>$codigoDoc]);
$itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Detalhes da Movimentação</title>
  <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2>Detalhes da Movimentação</h2>

  <!-- Botões -->
  <a href="index.php" class="btn btn-secondary mb-3">← Voltar</a>
  <form method="POST" action="delete.php" style="display:inline;" 
        onsubmit="return confirm('Tem certeza que deseja estornar este documento?')">
    <input type="hidden" name="codigo" value="<?= $doc['codigo'] ?>">
    <button type="submit" class="btn btn-danger mb-3">Estornar Documento</button>
  </form>

  <p><strong>Código:</strong> <?= $doc['codigo'] ?></p>
  <p><strong>Data/Hora:</strong> <?= $doc['data_hora'] ?></p>
  <p><strong>Motivo:</strong> <?= $doc['nome_motivo'] ?></p>
  <p><strong>Usuário:</strong> <?= $doc['usuario'] ?></p>
  <p><strong>Valor Total:</strong> R$ <?= number_format($doc['valor_total'],2,',','.') ?></p>

  <h4>Produtos</h4>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Código</th>
        <th>Produto</th>
        <th>Quantidade</th>
        <th>Valor Unitário</th>
        <th>Valor Total</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($itens): ?>
        <?php foreach ($itens as $i): ?>
        <tr>
          <td><?= $i['codigo_produto'] ?></td>
          <td><?= $i['descricao_completa'] ?></td>
          <td><?= $i['quantidade'] ?></td>
          <td>R$ <?= number_format($i['valor_unitario'],2,',','.') ?></td>
          <td>R$ <?= number_format($i['valor_total'],2,',','.') ?></td>
          <td>
            <form method="POST" action="delete_item.php" style="display:inline;" 
                  onsubmit="return confirm('Estornar este produto específico?')">
              <input type="hidden" name="id" value="<?= $i['id'] ?>">
              <input type="hidden" name="codigo_documento" value="<?= $doc['codigo'] ?>">
              <button type="submit" class="btn btn-warning btn-sm">Estornar Produto</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">Nenhum produto vinculado.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

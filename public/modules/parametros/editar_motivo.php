<?php
session_start();
include __DIR__ . "/../../config/db.php";

$codigo = $_GET['codigo'] ?? null;

if (!$codigo) {
    echo "<script>alert('Motivo inválido.'); window.location.href='index.php';</script>";
    exit;
}

// Bloquear motivos padrão
if ($codigo == 1 || $codigo == 2) {
    echo "<script>alert('Este motivo é padrão do sistema e não pode ser editado.'); window.location.href='index.php';</script>";
    exit;
}

// Buscar motivo
$stmt = $pdo->prepare("SELECT * FROM motivo_movimentacoes WHERE codigo=:codigo");
$stmt->execute([':codigo'=>$codigo]);
$motivo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$motivo) {
    echo "<script>alert('Motivo não encontrado.'); window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_motivo'];
    $descricao = $_POST['descricao_motivo'];
    $tipoValor = $_POST['tipo_valor'];

    $stmtUpd = $pdo->prepare("UPDATE motivo_movimentacoes 
                              SET nome_motivo=:nome, descricao_motivo=:desc, tipo_valor=:valor 
                              WHERE codigo=:codigo");
    $stmtUpd->execute([
        ':nome'=>$nome,
        ':desc'=>$descricao,
        ':valor'=>$tipoValor,
        ':codigo'=>$codigo
    ]);

    echo "<script>alert('Motivo atualizado com sucesso!'); window.location.href='index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Motivo</title>
  <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2>Editar Motivo</h2>
  <form method="POST" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Nome</label>
      <input type="text" name="nome_motivo" class="form-control" value="<?= htmlspecialchars($motivo['nome_motivo']) ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Descrição</label>
      <input type="text" name="descricao_motivo" class="form-control" value="<?= htmlspecialchars($motivo['descricao_motivo']) ?>" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Tipo Valor</label>
      <select name="tipo_valor" class="form-select">
        <option value="MANUAL" <?= ($motivo['tipo_valor']=='MANUAL'?'selected':'') ?>>Manual</option>
        <option value="PRECO_VENDA" <?= ($motivo['tipo_valor']=='PRECO_VENDA'?'selected':'') ?>>Preço de Venda</option>
        <option value="CUSTO" <?= ($motivo['tipo_valor']=='CUSTO'?'selected':'') ?>>Custo</option>
      </select>
    </div>
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
      <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
</body>
</html>

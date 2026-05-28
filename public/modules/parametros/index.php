<?php
session_start();
include __DIR__ . "/../../config/db.php";

// Buscar parâmetros atuais
$param = $pdo->query("SELECT * FROM parametros_sistema LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Buscar motivos existentes
$motivos = $pdo->query("SELECT * FROM motivo_movimentacoes ORDER BY codigo ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Parâmetros do Sistema</title>
  <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2>Parâmetros do Sistema</h2>
  <a href="/minha_loja_copilot/index.php" class="btn btn-secondary mb-3">← Voltar ao Dashboard</a>

  <!-- Formulário de parâmetros -->
  <form method="POST" action="salvar.php" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Tipo de Custo para Relatórios</label>
      <select name="tipo_custo_relatorio" class="form-select">
        <option value="MEDIO" <?= ($param['tipo_custo_relatorio']=='MEDIO'?'selected':'') ?>>Custo Médio</option>
        <option value="ULTIMA_ENTRADA" <?= ($param['tipo_custo_relatorio']=='ULTIMA_ENTRADA'?'selected':'') ?>>Última Entrada</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Tipo de Custo para Precificação</label>
      <select name="tipo_custo_precificacao" class="form-select">
        <option value="MEDIO" <?= ($param['tipo_custo_precificacao']=='MEDIO'?'selected':'') ?>>Custo Médio</option>
        <option value="ULTIMA_ENTRADA" <?= ($param['tipo_custo_precificacao']=='ULTIMA_ENTRADA'?'selected':'') ?>>Última Entrada</option>
      </select>
    </div>
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
  </form>

  <!-- Listagem de motivos -->
  <h3 class="mt-5">Motivos de Movimentação</h3>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Código</th>
        <th>Nome</th>
        <th>Tipo</th>
        <th>Tipo Valor</th>
        <th>Atualiza Custo</th>
        <th>Descrição</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($motivos as $m): ?>
      <tr>
        <td><?= $m['codigo'] ?></td>
        <td><?= $m['nome_motivo'] ?></td>
        <td><?= $m['tipo'] ?></td>
        <td><?= $m['tipo_valor'] ?></td>
        <td><?= $m['atualiza_custo'] ? 'Sim' : 'Não' ?></td>
        <td>
          <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#descModal<?= $m['codigo'] ?>">Ver</button>
          <!-- Modal da descrição -->
          <div class="modal fade" id="descModal<?= $m['codigo'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Descrição do Motivo</h5></div>
                <div class="modal-body"><?= nl2br(htmlspecialchars($m['descricao_motivo'])) ?></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td>
          <?php if ($m['codigo'] > 2): ?>
            <a href="editar_motivo.php?codigo=<?= $m['codigo'] ?>" class="btn btn-warning btn-sm">Editar</a>
            <a href="excluir_motivo.php?codigo=<?= $m['codigo'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este motivo?')">Excluir</a>
          <?php else: ?>
            <span class="text-muted">Padrão do sistema</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Formulário para novo motivo -->
  <h4>Incluir Novo Motivo</h4>
  <form method="POST" action="salvar_motivo.php" class="row g-3">
    <div class="col-md-3">
      <label class="form-label">Nome</label>
      <input type="text" name="nome_motivo" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Descrição</label>
      <input type="text" name="descricao_motivo" class="form-control" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Tipo</label>
      <select name="tipo" id="tipo" class="form-select" onchange="toggleAtualizaCusto()">
        <option value="E">Entrada</option>
        <option value="S">Saída</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Tipo Valor</label>
      <select name="tipo_valor" class="form-select">
        <option value="MANUAL">Manual</option>
        <option value="PRECO_VENDA">Preço de Venda</option>
        <option value="CUSTO">Custo</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Atualiza Custo</label>
      <select name="atualiza_custo" id="atualiza_custo" class="form-select">
        <option value="0">Não</option>
        <option value="1">Sim</option>
      </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-success w-100">Salvar Motivo</button>
    </div>
  </form>
  
	<!-- Seção de métodos de pagamentos -->
	<?php
	// Buscar métodos de pagamento
	$metodos = $pdo->query("SELECT * FROM metodo_pagamento ORDER BY codigo ASC")->fetchAll(PDO::FETCH_ASSOC);
	?>

	<h3 class="mt-5">Métodos de Pagamento</h3>
	<table class="table table-bordered">
	  <thead>
		<tr>
		  <th>Código</th>
		  <th>Descrição</th>
		  <th>Ativo</th>
		  <th>Ações</th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach ($metodos as $m): ?>
		<tr>
		  <td><?= $m['codigo'] ?></td>
		  <td><?= htmlspecialchars($m['descricao']) ?></td>
		  <td><?= $m['ativo'] ? 'Ativo' : 'Inativo' ?></td>
		  <td>
			<?php if ($m['codigo'] == 1): ?>
			  <span class="text-muted">Padrão do sistema</span>
			<?php else: ?>
			  <button class="btn btn-sm <?= $m['ativo'] ? 'btn-warning' : 'btn-success' ?>"
					  onclick="location.href='toggle_metodo.php?codigo=<?= $m['codigo'] ?>'">
				<?= $m['ativo'] ? 'Inativar' : 'Ativar' ?>
			  </button>
			  <a href="excluir_metodo.php?codigo=<?= $m['codigo'] ?>" 
				 class="btn btn-danger btn-sm"
				 onclick="return confirm('Tem certeza que deseja excluir este método? Só é possível excluir se nunca tiver sido usado.')">
				 Excluir
			  </a>
			<?php endif; ?>
		  </td>
		</tr>
		<?php endforeach; ?>
	  </tbody>
	</table>

	<!-- Formulário para novo método -->
	<h4>Incluir Novo Método de Pagamento</h4>
	<form method="POST" action="salvar_metodo.php" class="row g-3" onsubmit="return confirm('Após cadastrar, não será possivel editar seu nome, e caso seja usado não poderá mais ser excluído. certifique-se que o nome está correto. Deseja continuar?')">
	  <div class="col-md-4">
		<label class="form-label">Descrição</label>
		<input type="text" name="descricao" class="form-control" required>
	  </div>
	  <div class="col-md-2 d-flex align-items-end">
		<button type="submit" class="btn btn-success w-100">Salvar Método</button>
	  </div>
	</form>
	
	<!--Div para dar uma distancia do fim da tela-->
	<div style="margin-bottom: 20px"></div>
  
</div>

<script>
function toggleAtualizaCusto() {
  const tipo = document.getElementById('tipo').value;
  const atualiza = document.getElementById('atualiza_custo');
  if (tipo === 'S') {
    atualiza.value = "0";
    atualiza.disabled = true;
  } else {
    atualiza.disabled = false;
  }
}
</script>
<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
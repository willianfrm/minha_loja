<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}
include __DIR__ . "/../../../config/db.php";

// Filtros
$codigoFiltro = $_GET['codigo'] ?? null;
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-d');
$dataFim = $_GET['data_fim'] ?? date('Y-m-d');
$motivoFiltro = $_GET['motivo'] ?? null;

// Montar SQL dinâmico
$sql = "SELECT d.codigo, d.data_hora, d.valor_total, u.nome AS usuario, m.nome_motivo, m.tipo
        FROM documento_movimentacao d
        JOIN usuario u ON d.codigo_usuario = u.codigo
        JOIN motivo_movimentacoes m ON d.codigo_motivo = m.codigo
        WHERE m.tipo = 'S'";

$params = [];

if ($codigoFiltro) {
    // Filtro por número do documento
    $sql .= " AND d.codigo = :codigo";
    $params[':codigo'] = $codigoFiltro;
} else {
    // Filtro por período
    $sql .= " AND DATE(d.data_hora) BETWEEN :inicio AND :fim";
    $params[':inicio'] = $dataInicio;
    $params[':fim'] = $dataFim;

    // Filtro por motivo (se informado)
    if ($motivoFiltro) {
        $sql .= " AND d.codigo_motivo = :motivo";
        $params[':motivo'] = $motivoFiltro;
    }
}

$sql .= " ORDER BY d.data_hora DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar lista de motivos de saida para o filtro
$motivos = $pdo->query("SELECT codigo, nome_motivo FROM motivo_movimentacoes WHERE tipo='S'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Movimentações de Saídas</title>
  <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2>Movimentações de Saídas</h2>
  <a href="../index.php" class="btn btn-secondary mb-3">← Voltar ao Módulo Estoque</a>

	<!-- Filtros -->
	<form method="GET" class="row g-3 mb-4 align-items-end">
	  <div class="col-md-2">
		<label class="form-label">Número do Documento</label>
		<input type="text" name="codigo" value="<?= htmlspecialchars($codigoFiltro ?? '') ?>" class="form-control">
	  </div>
	  <div class="col-md-2">
		<label class="form-label">Data Início</label>
		<input type="date" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>" class="form-control">
	  </div>
	  <div class="col-md-2">
		<label class="form-label">Data Fim</label>
		<input type="date" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>" class="form-control">
	  </div>
	  <div class="col-md-3">
		<label class="form-label">Motivo</label>
		<select name="motivo" class="form-select">
		  <option value="">Todos</option>
		  <?php foreach ($motivos as $m): ?>
			<option value="<?= $m['codigo'] ?>" <?= ($motivoFiltro == $m['codigo']) ? 'selected' : '' ?>>
			  <?= $m['nome_motivo'] ?>
			</option>
		  <?php endforeach; ?>
		</select>
	  </div>
	  <div class="col-md-2">
		<button type="submit" class="btn btn-primary w-100">Filtrar</button>
	  </div>
	</form>

  <!-- Botão Nova Movimentação -->
  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAdd">Nova Saída</button>

  <!-- Listagem -->
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Código</th>
        <th>Data/Hora</th>
        <th>Motivo</th>
        <th>Usuário</th>
        <th>Valor Total</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($documentos): ?>
        <?php foreach ($documentos as $d): ?>
        <tr>
          <td><?= $d['codigo'] ?></td>
          <td><?= $d['data_hora'] ?></td>
          <td><?= $d['nome_motivo'] ?></td>
          <td><?= $d['usuario'] ?></td>
          <td>R$ <?= number_format($d['valor_total'],2,',','.') ?></td>
          <td>
            <a href="detalhes.php?doc=<?= $d['codigo'] ?>" class="btn btn-sm btn-info">Detalhes</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">Nenhuma movimentação encontrada.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nova Saída -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="add.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nova Movimentação de Saída</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Motivo</label>
          <select name="codigo_motivo" class="form-select" required>
            <?php
            $motivos = $pdo->query("SELECT codigo, nome_motivo FROM motivo_movimentacoes WHERE tipo='S' AND codigo != 1")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($motivos as $m) {
              echo "<option value='{$m['codigo']}'>{$m['nome_motivo']}</option>";
            }
            ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Data/Hora</label>
          <input type="datetime-local" name="data_hora" value="<?= date('Y-m-d\TH:i') ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Valor Total</label>
          <input type="number" step="0.01" name="valor_total" id="valorTotal" class="form-control" readonly>
        </div>

        <h5>Produtos</h5>
        <table class="table table-bordered" id="tabelaProdutos">
          <thead>
            <tr>
              <th>Produto</th>
			  <th>Detalhes</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
        <button type="button" class="btn btn-info" onclick="adicionarProduto()">Adicionar Produto</button>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" disabled>Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
function adicionarProduto() {
  const tbody = document.querySelector('#tabelaProdutos tbody');
  const row = document.createElement('tr');

  row.innerHTML = `
    <td colspan="2">
      <div class="row mb-2">
        <div class="col-md-3">
          <input type="text" name="codigo_produto[]" class="form-control codigo" placeholder="Código interno" onblur="buscarProduto(this)">
        </div>
        <div class="col-md-3">
          <input type="text" name="codigo_ean[]" class="form-control ean" placeholder="EAN" maxlength="13" onblur="buscarProduto(this)">
        </div>
        <div class="col-md-6">
          <input type="text" class="form-control nome" placeholder="Nome do produto" readonly>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3">
          <input type="number" step="0.001" name="quantidade[]" class="form-control qtd" placeholder="Quantidade" oninput="atualizarLinha(this)">
        </div>
        <div class="col-md-3">
          <input type="number" step="0.01" name="valor_unitario[]" class="form-control unit" placeholder="Valor unitário" oninput="atualizarLinha(this)">
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control total" placeholder="Valor total" readonly>
        </div>
        <div class="col-md-3">
          <button type="button" class="btn btn-danger" onclick="removerProduto(this)">Remover</button>
        </div>
      </div>
    </td>
  `;
  tbody.appendChild(row);
  verificarProdutos();
}

function verificarProdutos() {
  const rows = document.querySelectorAll('#tabelaProdutos tbody tr');
  const btnSalvar = document.querySelector('#modalAdd button[type="submit"]');

  let valido = true;

  if (rows.length === 0) {
    valido = false; // não pode salvar sem produtos
  }

  rows.forEach(row => {
    const nome = row.querySelector('.nome').value;
    const qtd = parseFloat(row.querySelector('.qtd').value) || 0;
    const unit = parseFloat(row.querySelector('.unit').value) || 0;

    if (!nome || nome === "Produto não cadastrado" || qtd <= 0 || unit <= 0) {
      valido = false;
    }
  });

  btnSalvar.disabled = !valido;
}

function removerProduto(btn) {
  btn.closest('tr').remove();
  atualizarValorTotal();
  verificarProdutos();
}

function atualizarLinha(input) {
  const row = input.closest('tr');
  const qtd = parseFloat(row.querySelector('.qtd').value) || 0;
  const unit = parseFloat(row.querySelector('.unit').value) || 0;
  const total = qtd * unit;
  row.querySelector('.total').value = total.toFixed(2);
  atualizarValorTotal();
  verificarProdutos()
}

function atualizarValorTotal() {
  let soma = 0;
  document.querySelectorAll('#tabelaProdutos .total').forEach(t => {
    soma += parseFloat(t.value) || 0;
  });
  document.getElementById('valorTotal').value = soma.toFixed(2);
}

function buscarProduto(input) {
  const row = input.closest('tr');
  const codigo = row.querySelector('.codigo').value;
  const ean = row.querySelector('.ean').value;
  const motivo = document.querySelector('select[name="codigo_motivo"]').value;

  fetch(`buscar_produto.php?codigo=${codigo}&ean=${ean}&motivo=${motivo}`)
    .then(res => res.json())
    .then(data => {
      if (data.ok) {
        row.querySelector('.nome').value = data.descricao;
        row.querySelector('.codigo').value = data.codigo;

        const unitInput = row.querySelector('.unit');
        if (data.tipo_valor === 'MANUAL') {
          unitInput.readOnly = false;
          unitInput.value = "";
        } else {
          unitInput.readOnly = true;
          unitInput.value = data.valor_unitario ?? "";
        }
      } else {
        row.querySelector('.nome').value = "Produto não cadastrado";
      }
      verificarProdutos();
    });
}
</script>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
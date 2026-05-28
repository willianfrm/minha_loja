<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}
include __DIR__ . "/../../../config/db.php";

// Filtro de data (por padrão amanhã)
$dataFiltro = $_GET['data'] ?? date('Y-m-d', strtotime('+1 day'));

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 20;
$offset = ($pagina - 1) * $limite;

// Consulta preços programados
$sqlBase = "FROM programacao_preco pp 
            JOIN produto p ON pp.codigo_produto = p.codigo 
            JOIN familia f ON p.codigo_familia = f.codigo 
            WHERE pp.data_entra_vigencia = :data";
$params = [':data' => $dataFiltro];

// Contagem
$stmt = $pdo->prepare("SELECT COUNT(*) ".$sqlBase);
$stmt->execute($params);
$totalRegistros = $stmt->fetchColumn();
$totalPaginas = ceil($totalRegistros / $limite);

// Registros
$sql = "SELECT pp.*, p.descricao_complemento, f.descricao AS familia_desc ".$sqlBase." 
        ORDER BY f.descricao, p.descricao_complemento 
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':data', $dataFiltro);
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$precos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciador de Preços</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Gerenciador de Preços</h2>
    <!-- Botões de navegação -->
    <a href="../index.php" class="btn btn-secondary mb-3">← Voltar ao Módulo Comercial</a>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPrecificar">Precificar Produto</button>

    <!-- Filtro de data -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Data de Vigência</label>
            <input type="date" name="data" value="<?= htmlspecialchars($dataFiltro) ?>" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Listagem -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Família</th>
                <th>Produto</th>
                <th>Preço Programado</th>
                <th>Data Vigência</th>
                <th>Atualizado</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($precos): ?>
                <?php foreach ($precos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['familia_desc']) ?></td>
                    <td><?= htmlspecialchars($p['descricao_complemento']) ?></td>
                    <td>R$ <?= number_format($p['preco_programado'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y', strtotime($p['data_entra_vigencia'])) ?></td>
                    <td><?= $p['atualizado'] ? 'Sim' : 'Não' ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Nenhum preço programado encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <nav>
        <ul class="pagination">
            <?php for ($i=1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina'=>$i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- Modal Precificar Produto -->
<div class="modal fade" id="modalPrecificar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="precificar.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Precificar Produto/Família</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-5">
            <label class="form-label">Código da Família</label>
            <input type="text" name="codigo_familia" id="codigoFamilia" class="form-control">
          </div>
          <div class="col-md-5">
            <label class="form-label">Código do Produto</label>
            <input type="text" name="codigo_produto" id="codigoProduto" class="form-control">
          </div>
          <div class="col-md-2 d-flex align-items-end">
			<!-- Botão para carregar dados digitados -->
			<button type="button" class="btn btn-success w-100" id="btnCarregar">Carregar</button>
            <!-- Botão para abrir modal de busca -->
            <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#modalBuscar">Buscar Produto</button>
          </div>
        </div>
        <div id="infoFamilia" class="mt-3">
          <!-- Aqui será carregada via Ajax a descrição da família, produtos e preços sugeridos -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Salvar Programação</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Buscar Produto (auxiliar do Precificar) -->
<div class="modal fade" id="modalBuscar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Buscar Produto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formBuscar" class="row g-3 mb-3">
          <div class="col-md-3"><input type="text" name="codigo_produto" placeholder="Código Produto" class="form-control"></div>
          <div class="col-md-3"><input type="text" name="codigo_familia" placeholder="Código Família" class="form-control"></div>
          <div class="col-md-3"><input type="text" name="descricao" placeholder="Descrição" class="form-control"></div>
          <div class="col-md-3"><input type="text" name="ean" placeholder="Código EAN" class="form-control"></div>
          <div class="col-md-12 d-flex justify-content-end">
            <button type="button" class="btn btn-primary" id="btnBuscar">Buscar</button>
          </div>
        </form>
        <table class="table table-sm table-hover">
          <thead><tr><th>Código</th><th>Família</th><th>Descrição</th><th>EAN</th><th>Ação</th></tr></thead>
          <tbody id="resultadoBusca">
            <!-- Resultados da busca via Ajax -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
// Exemplo de integração Ajax para buscar produto
document.getElementById('btnBuscar').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('formBuscar'));
    fetch('buscar.php', { method: 'POST', body: formData })
      .then(resp => resp.json())
      .then(data => {
        let html = '';
        data.forEach(prod => {
          html += `<tr>
            <td>${prod.codigo}</td>
            <td>${prod.familia}</td>
            <td>${prod.descricao}</td>
            <td>${prod.ean}</td>
            <td><button class="btn btn-sm btn-success" onclick="selecionarProduto(${prod.codigo}, ${prod.familia_codigo})">Selecionar</button></td>
          </tr>`;
        });
        document.getElementById('resultadoBusca').innerHTML = html || '<tr><td colspan="5" class="text-center">Nenhum produto encontrado.</td></tr>';
      });
});

// Botão Carregar: busca família/produto digitado
document.getElementById('btnCarregar').addEventListener('click', function() {
    let codigoFamilia = document.getElementById('codigoFamilia').value;
    let codigoProduto = document.getElementById('codigoProduto').value;
    if (codigoFamilia) {
        fetch('info_familia.php?codigo_familia=' + codigoFamilia)
          .then(resp => resp.text())
          .then(html => {
            document.getElementById('infoFamilia').innerHTML = html;
          });
    } else if (codigoProduto) {
        // Se informado produto, buscar família correspondente
        fetch('info_familia.php?codigo_produto=' + codigoProduto)
          .then(resp => resp.text())
          .then(html => {
            document.getElementById('infoFamilia').innerHTML = html;
          });
    }
});

// Função para selecionar produto da busca e preencher modal de precificação
function selecionarProduto(codigoProduto, codigoFamilia) {
    document.getElementById('codigoProduto').value = codigoProduto;
    document.getElementById('codigoFamilia').value = codigoFamilia;
    // Fecha modal de busca
    var modalBuscar = bootstrap.Modal.getInstance(document.getElementById('modalBuscar'));
    modalBuscar.hide();
    // Reabre modal de precificação
    var modalPrecificar = new bootstrap.Modal(document.getElementById('modalPrecificar'));
    modalPrecificar.show();
    // Carregar informações da família via Ajax
    fetch('info_familia.php?codigo_familia=' + codigoFamilia)
      .then(resp => resp.text())
      .then(html => {
        document.getElementById('infoFamilia').innerHTML = html;
      });
}
</script>
</body>
</html>

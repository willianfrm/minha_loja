<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../../index.php");
    exit;
}
include __DIR__ . "/../../../../config/db.php";

// Captura filtros (sem categoria)
$filtroCodigo = $_GET['codigo'] ?? '';
$filtroDescricao = $_GET['descricao'] ?? '';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 20;
$offset = ($pagina - 1) * $limite;

// Monta SQL com filtros
$sqlBase = "FROM familia f 
            JOIN categoria c ON f.codigo_categoria = c.codigo 
            WHERE 1=1";
$params = [];

if ($filtroCodigo !== '') {
    $sqlBase .= " AND f.codigo = :codigo";
    $params[':codigo'] = $filtroCodigo;
}
if ($filtroDescricao !== '') {
    $sqlBase .= " AND f.descricao LIKE :desc";
    $params[':desc'] = "%$filtroDescricao%";
}

// Contar total
$stmt = $pdo->prepare("SELECT COUNT(*) ".$sqlBase);
$stmt->execute($params);
$totalRegistros = $stmt->fetchColumn();
$totalPaginas = ceil($totalRegistros / $limite);

// Buscar registros da página
$sql = "SELECT f.*, c.descricao AS categoria_desc ".$sqlBase." ORDER BY f.descricao LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$familias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Famílias</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Gerenciar Famílias</h2>
    <a href="../index.php" class="btn btn-secondary mb-3">← Voltar ao Cadastro de Produtos</a>

    <!-- Formulário de Filtros -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label">Código</label>
            <input type="text" name="codigo" value="<?= htmlspecialchars($filtroCodigo) ?>" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Descrição</label>
            <input type="text" name="descricao" value="<?= htmlspecialchars($filtroDescricao) ?>" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Botão para cadastrar nova família -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAdd">Cadastrar Nova Família</button>

    <!-- Listagem -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Categoria</th>
                <th>Descrição</th>
                <th>Embalagem</th>
                <th>Margem</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($familias): ?>
                <?php foreach ($familias as $f): ?>
                <tr>
                    <td><?= $f['codigo'] ?></td>
                    <td><?= $f['categoria_desc'] ?></td>
                    <td><?= $f['descricao'] ?></td>
                    <td><?= $f['embalagem'] ?></td>
                    <td><?= $f['margem'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?= $f['codigo'] ?>">Editar</button>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalProdutos" data-id="<?= $f['codigo'] ?>">Produtos</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Nenhuma família encontrada.</td></tr>
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

<!-- Modal Adicionar Família -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="add.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Família</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Categoria</label>
          <select id="nivel1" class="form-select" required></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subcategoria</label>
          <select id="nivel2" class="form-select" disabled required></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Grupo</label>
          <select id="nivel3" class="form-select" disabled required></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subgrupo</label>
          <select name="codigo_categoria" id="nivel4" class="form-select" disabled required></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Descrição</label>
          <input type="text" name="descricao" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Embalagem</label>
          <select name="embalagem" class="form-select" required>
            <option value="UN">Unidade</option>
            <option value="KG">Quilo</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Margem (%)</label>
          <input type="number" step="0.01" name="margem" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar Família -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="edit.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Família</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="codigo" id="editId">

        <div class="mb-3">
          <label class="form-label">Categoria</label>
          <select id="editNivel1" class="form-select" required></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subcategoria</label>
          <select id="editNivel2" class="form-select" disabled required></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Grupo</label>
          <select id="editNivel3" class="form-select" disabled required></select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subgrupo</label>
          <select name="codigo_categoria" id="editNivel4" class="form-select" disabled required></select>
        </div>

        <div class="mb-3">
          <label class="form-label">Descrição</label>
          <input type="text" name="descricao" id="editDescricao" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Embalagem</label>
          <select name="embalagem" id="editEmbalagem" class="form-select" required>
            <option value="UN">Unidade</option>
            <option value="KG">Quilo</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Margem (%)</label>
          <input type="number" step="0.01" name="margem" id="editMargem" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <button type="submit" formaction="delete.php" class="btn btn-danger">Excluir</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Produtos da Família -->
<div class="modal fade" id="modalProdutos" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalProdutos">Produtos da Família</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Lista de produtos -->
        <table class="table table-bordered table-striped" id="tabelaProdutos">
          <thead>
            <tr>
              <th>Código</th>
              <th>Descrição Complemento</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <!-- preenchido via JS -->
          </tbody>
        </table>

        <!-- Formulário para adicionar novo produto -->
        <form id="formAddProduto" class="mt-3">
          <input type="hidden" name="codigo_familia" id="familiaId">
          <div class="mb-3">
            <label class="form-label">Descrição Complemento</label>
            <input type="text" name="descricao_complemento" class="form-control">
          </div>
          <button type="submit" class="btn btn-success">Adicionar Produto</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
function carregarCategorias(nivel, pai, selectId) {
  fetch('get_categorias.php?nivel='+nivel+(pai ? '&pai='+pai : ''))
    .then(resp => resp.json())
    .then(data => {
      const select = document.getElementById(selectId);
      select.innerHTML = '<option value="">Selecione</option>';
      data.forEach(c => {
        select.innerHTML += `<option value="${c.codigo}">${c.descricao}</option>`;
      });
      select.disabled = false;
    });
}

carregarCategorias(1, null, 'nivel1');

document.getElementById('nivel1').addEventListener('change', e => {
  carregarCategorias(2, e.target.value, 'nivel2');
  document.getElementById('nivel3').innerHTML = '';
  document.getElementById('nivel3').disabled = true;
  document.getElementById('nivel4').innerHTML = '';
  document.getElementById('nivel4').disabled = true;
});

document.getElementById('nivel2').addEventListener('change', e => {
  carregarCategorias(3, e.target.value, 'nivel3');
  document.getElementById('nivel4').innerHTML = '';
  document.getElementById('nivel4').disabled = true;
});

document.getElementById('nivel3').addEventListener('change', e => {
  carregarCategorias(4, e.target.value, 'nivel4');
});
</script>
<script>
function carregarCategoriasEdit(nivel, pai, selectId, selectedValue = null) {
  fetch('get_categorias.php?nivel='+nivel+(pai ? '&pai='+pai : ''))
    .then(resp => resp.json())
    .then(data => {
      const select = document.getElementById(selectId);
      select.innerHTML = '<option value="">Selecione</option>';
      data.forEach(c => {
        select.innerHTML += `<option value="${c.codigo}" ${selectedValue == c.codigo ? 'selected' : ''}>${c.descricao}</option>`;
      });
      select.disabled = false;
    });
}

var modalEdit = document.getElementById('modalEdit');
modalEdit.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var id = button.getAttribute('data-id');
  fetch('edit.php?codigo='+id)
    .then(resp => resp.json())
    .then(data => {
      document.getElementById('editId').value = data.codigo;
      document.getElementById('editDescricao').value = data.descricao;
      document.getElementById('editEmbalagem').value = data.embalagem;
      document.getElementById('editMargem').value = data.margem;

      // Carregar árvore de categorias
      carregarCategoriasEdit(1, null, 'editNivel1', data.arvore[1]?.codigo);
      carregarCategoriasEdit(2, data.arvore[1]?.codigo, 'editNivel2', data.arvore[2]?.codigo);
      carregarCategoriasEdit(3, data.arvore[2]?.codigo, 'editNivel3', data.arvore[3]?.codigo);
      carregarCategoriasEdit(4, data.arvore[3]?.codigo, 'editNivel4', data.arvore[4]?.codigo);
    });
});

// Resetar selects inferiores ao mudar nível
document.getElementById('editNivel1').addEventListener('change', e => {
  carregarCategoriasEdit(2, e.target.value, 'editNivel2');
  document.getElementById('editNivel3').innerHTML = '';
  document.getElementById('editNivel3').disabled = true;
  document.getElementById('editNivel4').innerHTML = '';
  document.getElementById('editNivel4').disabled = true;
});

document.getElementById('editNivel2').addEventListener('change', e => {
  carregarCategoriasEdit(3, e.target.value, 'editNivel3');
  document.getElementById('editNivel4').innerHTML = '';
  document.getElementById('editNivel4').disabled = true;
});

document.getElementById('editNivel3').addEventListener('change', e => {
  carregarCategoriasEdit(4, e.target.value, 'editNivel4');
});
</script>
<script>
var modalProdutos = document.getElementById('modalProdutos');
modalProdutos.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var familiaId = button.getAttribute('data-id');
  document.getElementById('familiaId').value = familiaId;
  
  // Carrega info da familia para add titulo no modal
  fetch('edit.php?codigo='+familiaId)
  .then(resp => resp.json())
  .then(data => {
    document.getElementById('tituloModalProdutos').textContent = familiaId + ' - ' + data.descricao;
  });
  
  // Carregar lista de produtos
  fetch('produtos.php?family='+familiaId)
    .then(resp => resp.json())
    .then(data => {
      const tbody = document.querySelector('#tabelaProdutos tbody');
      tbody.innerHTML = '';
      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhum produto encontrado.</td></tr>';
      } else {
        data.forEach(p => {
          tbody.innerHTML += `
            <tr>
              <td>${p.codigo}</td>
              <td>${p.descricao_complemento}</td>
              <td>${p.linha ? 'Ativo' : 'Fora de Linha'}</td>
              <td>
                <a href="../produto/index.php?produto=${p.codigo}" target="_blank" class="btn btn-sm btn-info">Detalhes</a>
                <button type="button" class="btn btn-sm btn-danger" onclick="excluirProduto(${p.codigo}, ${familiaId})">Excluir</button>
              </td>
            </tr>`;
        });
      }
    });
});

// Adicionar novo produto
document.getElementById('formAddProduto').addEventListener('submit', function(e) {
  e.preventDefault();
  if (!confirm('Deseja realmente adicionar este produto?')) return;
  
  const formData = new FormData(this);
  fetch('add_produto.php', {
    method: 'POST',
    body: formData
  })
  .then(resp => resp.json())
  .then(data => {
    if (data.success) {
      // Recarregar lista
      fetch('produtos.php?family='+formData.get('codigo_familia'))
        .then(resp => resp.json())
        .then(produtos => {
          const tbody = document.querySelector('#tabelaProdutos tbody');
          tbody.innerHTML = '';
          produtos.forEach(p => {
            tbody.innerHTML += `
              <tr>
                <td>${p.codigo}</td>
                <td>${p.descricao_complemento}</td>
                <td>${p.linha ? 'Ativo' : 'Fora de Linha'}</td>
                <td>
                  <a href="../produto/index.php?produto=${p.codigo}" target="_blank" class="btn btn-sm btn-info">Detalhes</a>
                  <button type="button" class="btn btn-sm btn-danger" onclick="excluirProduto(${p.codigo}, ${formData.get('codigo_familia')})">Excluir</button>
                </td>
              </tr>`;
          });
        });
      this.reset();
    } else {
      alert(data.message);
    }
  });
});

// Excluir produto
function excluirProduto(produtoId, familiaId) {
  if (!confirm('Tem certeza que deseja excluir este produto?')) return;
  fetch('delete_produto.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'codigo='+produtoId
  })
  .then(resp => resp.json())
  .then(data => {
    if (data.success) {
      // Recarregar lista
      fetch('produtos.php?family='+familiaId)
        .then(resp => resp.json())
        .then(produtos => {
          const tbody = document.querySelector('#tabelaProdutos tbody');
          tbody.innerHTML = '';
          produtos.forEach(p => {
            tbody.innerHTML += `
              <tr>
                <td>${p.codigo}</td>
                <td>${p.descricao_complemento}</td>
                <td>${p.linha ? 'Ativo' : 'Fora de Linha'}</td>
                <td>
                  <a href="../produto/index.php?produto=${p.codigo}" target="_blank" class="btn btn-sm btn-info">Detalhes</a>
                  <button type="button" class="btn btn-sm btn-danger" onclick="excluirProduto(${p.codigo}, ${familiaId})">Excluir</button>
                </td>
              </tr>`;
          });
        });
    } else {
      alert(data.message);
    }
  });
}
</script>
</body>
</html>

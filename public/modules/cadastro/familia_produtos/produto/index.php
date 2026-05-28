<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

$produtoId = $_GET['produto'] ?? null;
$produto = null;
$eans = [];

if ($produtoId) {
    $sql = "SELECT p.*, f.descricao AS familia_desc, 
                   CONCAT(f.descricao, ' ', IFNULL(p.descricao_complemento,'')) AS descricao_produto,
                   e.estoque AS estoque, e.preco_vigente AS preco, e.preco_oferta,
                   c1.descricao AS cat1, c2.descricao AS cat2, c3.descricao AS cat3, c4.descricao AS cat4
            FROM produto p
            JOIN familia f ON p.codigo_familia = f.codigo
            JOIN categoria c4 ON f.codigo_categoria = c4.codigo
            LEFT JOIN categoria c3 ON c4.codigo_cat_pai = c3.codigo
            LEFT JOIN categoria c2 ON c3.codigo_cat_pai = c2.codigo
            LEFT JOIN categoria c1 ON c2.codigo_cat_pai = c1.codigo
            LEFT JOIN estoque e ON e.codigo_produto = p.codigo
            WHERE p.codigo = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $produtoId]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar EANs
    $stmtEan = $pdo->prepare("SELECT codigo_ean FROM produto_eans WHERE codigo_produto = :id");
    $stmtEan->execute([':id' => $produtoId]);
    $eans = $stmtEan->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar Produtos</title>
  <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h2>Gerenciar Produtos</h2>
  <a href="../index.php" class="btn btn-secondary mb-3">← Voltar ao Cadastro de Produtos</a>
  <button class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#modalBuscar">Buscar Produto</button>
  <button class="btn btn-warning mb-3" data-bs-toggle="modal" data-bs-target="#modalAlterarFamilia" <?= $produto ? '' : 'disabled' ?>>Alterar Produto da Família</button>
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalEditarProduto" <?= $produto ? '' : 'disabled' ?>>Editar Produto</button>

  <?php if ($produto): ?>
    <h4><?= $produto['codigo'] ?> - <?= $produto['descricao_produto'] ?></h4>
	<p><strong>Código Família:</strong> <a href="../familia/index.php?codigo=<?= $produto['codigo_familia'] ?>" target="_blank"> <?= $produto['codigo_familia'] ?> </a></p>
    <p>
	  <strong>Status:</strong> 
	  <?php if ($produto['linha']): ?>
		<span class="text-success"><strong>Ativo</strong></span>
	  <?php else: ?>
		<span class="text-danger"><strong>Fora de Linha</strong></span>
	  <?php endif; ?>
	</p>
    <p><strong>Estoque:</strong> <?= $produto['estoque'] ?></p>
    <p><strong>Preço:</strong> <?= $produto['preco'] ?></p>
    <p><strong>Preço Oferta:</strong> <?= $produto['preco_oferta'] ?></p>
    <p><strong>Mercadológica:</strong> <?= $produto['cat1'].' > '.$produto['cat2'].' > '.$produto['cat3'].' > '.$produto['cat4'] ?></p>

    <h5>Códigos EAN</h5>
    <ul id="listaEans">
      <?php foreach ($eans as $ean): ?>
        <li><?= $ean ?> 
          <button class="btn btn-sm btn-danger" onclick="excluirEan('<?= $ean ?>', <?= $produto['codigo'] ?>)">Excluir</button>
        </li>
      <?php endforeach; ?>
    </ul>
    <form id="formAddEan" class="mt-2">
      <input type="hidden" name="codigo_produto" value="<?= $produto['codigo'] ?>">
      <div class="input-group">
        <input type="text" name="codigo_ean" class="form-control" placeholder="Novo EAN">
        <button type="submit" class="btn btn-success">Adicionar EAN</button>
      </div>
    </form>
  <?php else: ?>
    <p class="text-muted">Nenhum produto selecionado.</p>
  <?php endif; ?>
</div>

<!-- Modal Buscar Produto -->
<div class="modal fade" id="modalBuscar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Buscar Produto</h5></div>
      <div class="modal-body">
        <form id="formBuscarProduto" class="row g-3 mb-3">
          <div class="col-md-3"><input type="text" name="codigo" class="form-control" placeholder="Código"></div>
          <div class="col-md-6"><input type="text" name="descricao" class="form-control" placeholder="Descrição"></div>
          <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Pesquisar</button></div>
        </form>
        <table class="table table-bordered" id="resultadoBusca">
          <thead><tr><th>Código</th><th>Descrição</th><th>Ações</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Alterar Família -->
<div class="modal fade" id="modalAlterarFamilia" tabindex="-1">
  <div class="modal-dialog">
    <form id="formAlterarFamilia" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Alterar Produto de Família</h5></div>
      <div class="modal-body">
        <input type="hidden" name="codigo_produto" value="<?= $produto['codigo'] ?? '' ?>">
        <div class="mb-3">
          <label>Código da nova família</label>
          <input type="text" name="codigo_familia" id="novaFamilia" class="form-control">
        </div>
        <div class="mb-3">
          <button type="button" class="btn btn-info" onclick="carregarFamilia()">Carregar Família</button>
          <p id="descricaoFamilia" class="mt-2"></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning">Mover Produto</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar Produto -->
<div class="modal fade" id="modalEditarProduto" tabindex="-1">
  <div class="modal-dialog">
    <form id="formEditarProduto" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Editar Produto</h5></div>
      <div class="modal-body">
        <input type="hidden" name="codigo_produto" value="<?= $produto['codigo'] ?? '' ?>">
        <div class="mb-3">
          <label>Descrição Complemento</label>
          <input type="text" name="descricao_complemento" value="<?= $produto['descricao_complemento'] ?? '' ?>" class="form-control">
        </div>
        <div class="mb-3">
          <label>Status</label>
          <select name="linha" class="form-select">
            <option value="1" <?= isset($produto) && $produto['linha'] ? 'selected' : '' ?>>Ativo</option>
            <option value="0" <?= isset($produto) && !$produto['linha'] ? 'selected' : '' ?>>Fora de Linha</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
// Buscar produto
document.getElementById('formBuscarProduto').addEventListener('submit', function(e){
  e.preventDefault();
  const formData = new FormData(this);
  fetch('buscar_produto.php', {method:'POST', body:formData})
    .then(resp=>resp.json())
    .then(data=>{
      const tbody = document.querySelector('#resultadoBusca tbody');
      tbody.innerHTML='';
      data.forEach(p=>{
        tbody.innerHTML += `<tr>
          <td>${p.codigo}</td>
          <td>${p.descricao}</td>
          <td><a href="index.php?produto=${p.codigo}" class="btn btn-sm btn-success">Selecionar</a></td>
        </tr>`;
      });
    });
});

// Alterar família
function carregarFamilia(){
  const codigo = document.getElementById('novaFamilia').value;
  fetch('../familia/edit.php?codigo='+codigo)
    .then(resp=>resp.json())
    .then(data=>{
      document.getElementById('descricaoFamilia').textContent = data.descricao ? (codigo+' - '+data.descricao) : 'Família não encontrada';
    });
}

document.getElementById('formAlterarFamilia').addEventListener('submit', function(e){
  e.preventDefault();
  if (!confirm('Deseja realmente mover este produto para a nova família?')) return;
  const formData = new FormData(this);
  fetch('alterar_familia.php', {method:'POST', body:formData})
    .then(resp=>resp.json())
    .then(data=>{
      if(data.success){
        window.location.href = 'index.php?produto='+formData.get('codigo_produto');
      } else {
        alert(data.message);
      }
    });
});

// Editar produto
document.getElementById('formEditarProduto').addEventListener('submit', function(e){
  e.preventDefault();
  const formData = new FormData(this);
  fetch('editar_produto.php', {method:'POST', body:formData})
    .then(resp=>resp.json())
    .then(data=>{
      if(data.success){
        window.location.href = 'index.php?produto='+formData.get('codigo_produto');
      } else {
        alert(data.message);
      }
    });
});

// Adicionar EAN
document.getElementById('formAddEan')?.addEventListener('submit', function(e){
  e.preventDefault();
  const formData = new FormData(this);
  fetch('add_ean.php', {method:'POST', body:formData})
    .then(resp=>resp.json())
    .then(data=>{
      if(data.success){
        window.location.href = 'index.php?produto='+formData.get('codigo_produto');
      } else {
        alert(data.message);
      }
    });
});

// Excluir EAN
function excluirEan(ean, produtoId){
  if(!confirm('Deseja realmente excluir este EAN?')) return;
  fetch('delete_ean.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'codigo_produto='+produtoId+'&codigo_ean='+ean
  })
  .then(resp=>resp.json())
  .then(data=>{
    if(data.success){
      window.location.href = 'index.php?produto='+produtoId;
    } else {
      alert(data.message);
    }
  });
}
</script>
</body>
</html>
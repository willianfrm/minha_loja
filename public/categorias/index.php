<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/db.php";

// Buscar todas categorias
$categorias = $pdo->query("SELECT * FROM categoria ORDER BY nivel, descricao")->fetchAll(PDO::FETCH_ASSOC);

// Função recursiva para renderizar árvore
function renderCategorias($categorias, $pai = null, $nivel = 1) {
    foreach ($categorias as $cat) {
        if ($cat['codigo_cat_pai'] == $pai) {
            echo str_repeat("&nbsp;&nbsp;&nbsp;", $nivel-1) . "➤ " . htmlspecialchars($cat['descricao']);
            if ($nivel < 4) {
                echo " <button class='btn btn-sm btn-success' data-bs-toggle='modal' data-bs-target='#modalAdd' data-pai='{$cat['codigo']}' data-nivel='{$nivel}'>Adicionar</button>";
            }
            echo " <button class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#modalEdit' data-id='{$cat['codigo']}'>Editar</button>";
            echo "<br>";
            if ($nivel < 4) {
                renderCategorias($categorias, $cat['codigo'], $nivel+1);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Categorias</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Árvore Mercadológica</h2>
    <?php renderCategorias($categorias); ?>
</div>

<!-- Modal Adicionar -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="categorias_add.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Adicionar Categoria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="codigo_cat_pai" id="addPai">
        <input type="hidden" name="nivel" id="addNivel">
        <div class="mb-3">
          <label class="form-label">Descrição</label>
          <input type="text" name="descricao" class="form-control" required>
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

<!-- Modal Editar -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="categorias_edit.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Categoria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="codigo" id="editId">
        <div class="mb-3">
          <label class="form-label">Descrição</label>
          <input type="text" name="descricao" id="editDescricao" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Margem (%)</label>
          <input type="number" step="0.01" name="margem" id="editMargem" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <button type="submit" formaction="categorias_delete.php" class="btn btn-danger">Excluir</button>
      </div>
    </form>
  </div>
</div>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
var modalAdd = document.getElementById('modalAdd');
modalAdd.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  document.getElementById('addPai').value = button.getAttribute('data-pai');
  document.getElementById('addNivel').value = parseInt(button.getAttribute('data-nivel')) + 1;
});

var modalEdit = document.getElementById('modalEdit');
modalEdit.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var id = button.getAttribute('data-id');
  // AJAX para buscar dados da categoria
  fetch('categorias_edit.php?codigo='+id)
    .then(response => response.json())
    .then(data => {
      document.getElementById('editId').value = data.codigo;
      document.getElementById('editDescricao').value = data.descricao;
      document.getElementById('editMargem').value = data.margem;
    });
});
</script>
</body>
</html>
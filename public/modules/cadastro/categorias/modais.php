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
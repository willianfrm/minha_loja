<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}
include __DIR__ . "/../../../config/db.php";

// Filtros
$filtroCodigo = $_GET['codigo'] ?? '';
$filtroLogin = $_GET['login'] ?? '';
$filtroNome = $_GET['nome'] ?? '';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 20;
$offset = ($pagina - 1) * $limite;

// SQL base
$sqlBase = "FROM usuario u WHERE 1=1";
$params = [];

if ($filtroCodigo !== '') {
    $sqlBase .= " AND u.codigo = :codigo";
    $params[':codigo'] = $filtroCodigo;
}
if ($filtroLogin !== '') {
    $sqlBase .= " AND u.login LIKE :login";
    $params[':login'] = "%$filtroLogin%";
}
if ($filtroNome !== '') {
    $sqlBase .= " AND u.nome LIKE :nome";
    $params[':nome'] = "%$filtroNome%";
}

// Contagem
$stmt = $pdo->prepare("SELECT COUNT(*) ".$sqlBase);
$stmt->execute($params);
$totalRegistros = $stmt->fetchColumn();
$totalPaginas = ceil($totalRegistros / $limite);

// Registros
$sql = "SELECT u.* ".$sqlBase." ORDER BY u.nome LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Gerenciar Usuários</h2>
    <a href="../index.php" class="btn btn-secondary mb-3">← Voltar ao Módulo de Cadastro</a>

    <!-- Filtros -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label">Código</label>
            <input type="text" name="codigo" value="<?= htmlspecialchars($filtroCodigo) ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Login</label>
            <input type="text" name="login" value="<?= htmlspecialchars($filtroLogin) ?>" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($filtroNome) ?>" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Botão Novo -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAdd">Cadastrar Novo Usuário</button>

    <!-- Listagem -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Login</th>
                <th>Nome</th>
                <th>Permissões</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($usuarios): ?>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= $u['codigo'] ?></td>
                    <td><?= $u['login'] ?></td>
                    <td><?= $u['nome'] ?></td>
                    <td>
                        <?= $u['opera_pdv'] ? 'Opera PDV | ' : '' ?>
                        <?= $u['admin_pdv'] ? 'Admin PDV | ' : '' ?>
                        <?= $u['admin'] ? 'Admin' : '' ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEdit" data-id="<?= $u['codigo'] ?>">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Nenhum usuário encontrado.</td></tr>
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

<!-- Modal Adicionar Usuário -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="add.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Login</label><input type="text" name="login" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Senha</label><input type="password" name="senha" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" required></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="opera_pdv"><label class="form-check-label">Opera PDV</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="admin_pdv"><label class="form-check-label">Admin PDV</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="admin"><label class="form-check-label">Admin</label></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-success">Salvar</button></div>
    </form>
  </div>
</div>

<!-- Modal Editar Usuário -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="edit.php" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Editar Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="codigo" id="editId">
        <div class="mb-3"><label class="form-label">Login</label><input type="text" name="login" id="editLogin" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Senha (em branco para não alterar)</label><input type="password" name="senha" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Nome</label><input type="text" name="nome" id="editNome" class="form-control" required></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="opera_pdv" id="editOpera"><label class="form-check-label">Opera PDV</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="admin_pdv" id="editAdminPdv"><label class="form-check-label">Admin PDV</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="admin" id="editAdmin"><label class="form-check-label">Admin</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <button type="submit" formaction="delete.php" class="btn btn-danger">Excluir</button>
      </div>
    </form>
  </div>
</div>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preencher modal de edição com dados do usuário
var modalEdit = document.getElementById('modalEdit');
modalEdit.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var id = button.getAttribute('data-id');
  fetch('edit.php?codigo='+id)
    .then(resp => resp.json())
    .then(data => {
      document.getElementById('editId').value = data.codigo;
      document.getElementById('editLogin').value = data.login;
      document.getElementById('editNome').value = data.nome;
      document.getElementById('editOpera').checked = data.opera_pdv == 1;
      document.getElementById('editAdminPdv').checked = data.admin_pdv == 1;
      document.getElementById('editAdmin').checked = data.admin == 1;
    });
});
</script>
</body>
</html>

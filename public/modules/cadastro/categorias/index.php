<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}
include __DIR__ . "/../../../config/db.php";

$categorias = $pdo->query("SELECT * FROM categoria ORDER BY nivel, descricao")->fetchAll(PDO::FETCH_ASSOC);

function renderCategorias($categorias, $pai = null, $nivel = 1) {
    foreach ($categorias as $cat) {
        if ($cat['codigo_cat_pai'] == $pai) {
            $collapseId = "cat_" . $cat['codigo'];
            echo "<div class='categoria-item nivel-{$nivel}'>";
            echo "<span class='fw-bold'>" . htmlspecialchars($cat['descricao']) . "</span>";

            // Botões de ação
            if ($nivel < 4) {
                echo " <button class='btn btn-sm btn-success' data-bs-toggle='modal' data-bs-target='#modalAdd' data-pai='{$cat['codigo']}' data-nivel='{$nivel}'>Adicionar</button>";
            }
            echo " <button class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#modalEdit' data-id='{$cat['codigo']}'>Editar</button>";

            // Botão expandir se tiver filhos
            $temFilhos = false;
            foreach ($categorias as $c) {
                if ($c['codigo_cat_pai'] == $cat['codigo']) {
                    $temFilhos = true;
                    break;
                }
            }
            if ($temFilhos) {
                echo " <button class='btn btn-sm btn-secondary' data-bs-toggle='collapse' data-bs-target='#{$collapseId}'>Abrir/Fechar</button>";
            }

            echo "</div>";

            // Renderizar filhos dentro do collapse
            if ($temFilhos) {
                echo "<div class='collapse' id='{$collapseId}'>";
                renderCategorias($categorias, $cat['codigo'], $nivel+1);
                echo "</div>";
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
	<style>
	.categoria-item {
		padding: 6px 0;
		border-bottom: 1px solid #ddd;
	}

	/* Indentação por nível */
	.nivel-1 { padding-left: 0; }
	.nivel-2 { padding-left: 30px; }
	.nivel-3 { padding-left: 60px; }
	.nivel-4 { padding-left: 90px; }
	.nivel-5 { padding-left: 120px; }
	</style>
</head>
<body>
<div class="container mt-4">
    <h2>Árvore Mercadológica</h2>
	<a href="../index.php" class="btn btn-secondary mb-3">← Voltar ao Módulo Cadastro</a>
	<?php renderCategorias($categorias); ?>
</div>

<!-- Modais iguais aos que já fizemos -->
<?php include "modais.php"; ?>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
// JS para preencher modais (igual ao que já fizemos antes)
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
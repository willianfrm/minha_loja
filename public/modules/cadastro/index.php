<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Módulo Cadastro</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Módulo Cadastro</h2>
    <div class="row">
        <!-- Programa: Categorias -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Árvore Mercadológica</h5>
                    <p class="card-text">Gerencie categorias e subcategorias.</p>
                    <a href="categorias/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <!-- Programa: Famílias e Produtos -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Famílias e Produtos</h5>
                    <p class="card-text">Cadastre famílias e produtos vinculados.</p>
                    <a href="familia_produtos/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <!-- Programa: Usuários -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Usuários</h5>
                    <p class="card-text">Gerencie usuários e permissões.</p>
                    <a href="usuarios/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
    </div>
    <a href="../../dashboard.php" class="btn btn-secondary mt-3">← Voltar ao Dashboard</a>
</div>
<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
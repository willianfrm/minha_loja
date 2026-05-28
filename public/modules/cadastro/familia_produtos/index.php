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
    <title>Cadastro de Produtos</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Cadastro de Produtos</h2>
    <a href="../index.php" class="btn btn-secondary mb-3">← Voltar ao Módulo Cadastro</a>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Gerenciar Famílias</h5>
                    <p class="card-text">Cadastre e edite famílias e produtos.</p>
                    <a href="familia/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Gerenciar Produtos</h5>
                    <p class="card-text">Visualize e edite produtos vinculados às famílias.</p>
                    <a href="produto/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
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
    <title>Módulo Comercial</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Módulo Comercial</h2>
    <div class="row">
        <!-- Programa: Gerenciador de Preços -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Gerenciador de Preços</h5>
                    <p class="card-text">Manutenção de preços fixos com base em custos e margens.</p>
                    <a href="precos/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <!-- Programa: Gerenciador de Ofertas -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Gerenciador de Ofertas</h5>
                    <p class="card-text">Crie e gerencie ofertas com vigência.</p>
                    <a href="ofertas/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <!-- Programa: Relatório de Alterações -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Relatório de Alterações</h5>
                    <p class="card-text">Acompanhe alterações de preços e cargas.</p>
                    <a href="relatorio_alteracoes/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <!-- Programa: Consulta de Produto -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Consulta de Produto</h5>
                    <p class="card-text">Veja estoque, preços, custos e movimentações.</p>
                    <a href="consulta_produto/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
    </div>
    <a href="../../dashboard.php" class="btn btn-secondary mt-3">← Voltar ao Dashboard</a>
</div>
<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

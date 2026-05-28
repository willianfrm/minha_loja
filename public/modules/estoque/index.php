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
    <title>Módulo Estoque</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Módulo Estoque</h2>
    <div class="row">
        <!-- Programa: Movimentações de Entrada -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Movimentações de Entrada</h5>
                    <p class="card-text">Registre compras, bonificações e outras entradas de mercadorias.</p>
                    <a href="entrada/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
        <!-- Programa: Movimentações de Saída -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Movimentações de Saída</h5>
                    <p class="card-text">Registre vendas, perdas, devoluções e outras saídas de mercadorias.</p>
                    <a href="saida/" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>
    </div>
    <a href="../../dashboard.php" class="btn btn-secondary mt-3">← Voltar ao Dashboard</a>
</div>
<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

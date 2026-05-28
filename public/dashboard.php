<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema Loja</title>
    <link href="/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            flex-shrink: 0;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
            display: block;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-3">
        <h4 class="text-center mb-4">Sistema Loja</h4>
        <p class="text-center">Bem-vindo, <?= $_SESSION['nome'] ?>!</p>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="modules/parametros/index.php">⚙️ Parâmetros</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/cadastro/index.php">📂 Cadastro</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/comercial/index.php">💰 Comercial</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/estoque/index.php">📦 Estoque</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/gerencial/index.php">📊 Gerencial</a></li>
            <li class="nav-item"><a class="nav-link" href="pdv/index.php">🖥️ PDV</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger mt-auto">Sair</a>
    </div>

    <!-- Conteúdo principal -->
    <div class="content">
        <h2>Dashboard</h2>
        <p>Selecione um módulo no menu lateral para começar.</p>
    </div>

<script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
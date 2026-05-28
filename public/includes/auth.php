<?php
session_start();
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE login = :login LIMIT 1");
    $stmt->bindParam(":login", $login);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario'] = $usuario['codigo'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['admin'] = $usuario['admin'];
        $_SESSION['opera_pdv'] = $usuario['opera_pdv'];
        $_SESSION['admin_pdv'] = $usuario['admin_pdv'];
        header("Location: ../dashboard.php");
        exit;
    } else {
        echo "<script>alert('Login ou senha inválidos!'); window.location.href='../index.php';</script>";
    }
}
?>
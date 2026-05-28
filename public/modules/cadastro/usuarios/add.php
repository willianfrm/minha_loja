<?php
include __DIR__ . "/../../../config/db.php";

$login = $_POST['login'];
$senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
$nome = $_POST['nome'];
$opera = isset($_POST['opera_pdv']) ? 1 : 0;
$adminPdv = isset($_POST['admin_pdv']) ? 1 : 0;
$admin = isset($_POST['admin']) ? 1 : 0;

$sql = "INSERT INTO usuario (login, senha, nome, opera_pdv, admin_pdv, admin)
        VALUES (:login, :senha, :nome, :opera, :adminPdv, :admin)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':login'=>$login, ':senha'=>$senha, ':nome'=>$nome,
  ':opera'=>$opera, ':adminPdv'=>$adminPdv, ':admin'=>$admin
]);

header("Location: index.php");

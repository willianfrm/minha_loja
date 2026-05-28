<?php
session_start();
include __DIR__ . "/../config/db.php";

$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';

if (!$login || !$senha) {
    echo json_encode(['erro' => 'Login e senha obrigatórios']);
    exit;
}

$stmt = $pdo->prepare("SELECT senha, admin_pdv FROM usuario WHERE login = :login AND admin_pdv = 1");
$stmt->execute([':login' => $login]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['erro' => 'Usuário não encontrado ou sem permissão']);
    exit;
}

if (!password_verify($senha, $user['senha'])) {
    echo json_encode(['erro' => 'Senha incorreta']);
    exit;
}

echo json_encode(['sucesso' => true]);

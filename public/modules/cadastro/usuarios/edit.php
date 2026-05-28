<?php
session_start();
include __DIR__ . "/../../../config/db.php";

$usuarioLogado = (int)$_SESSION['usuario']; // id do usuário logado

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $codigo = (int)$_GET['codigo'];
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE codigo = :codigo");
    $stmt->execute([':codigo'=>$codigo]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = (int)$_POST['codigo'];
    $login = $_POST['login'];
    $nome = $_POST['nome'];
    $opera = isset($_POST['opera_pdv']) ? 1 : 0;
    $adminPdv = isset($_POST['admin_pdv']) ? 1 : 0;
    $admin = isset($_POST['admin']) ? 1 : 0;

    // Regras de segurança
    if ($codigo === 1) {
        // Admin padrão nunca perde privilégios
        $admin = 1;
        $adminPdv = 1;
        $opera = 1;
    }
    if ($codigo === $usuarioLogado) {
        // Usuário logado não pode remover seu próprio admin
        $stmtCheck = $pdo->prepare("SELECT admin FROM usuario WHERE codigo=:codigo");
        $stmtCheck->execute([':codigo'=>$codigo]);
        $isAdmin = $stmtCheck->fetchColumn();
        if ($isAdmin) {
            $admin = 1; // força manter admin
        }
    }

    $sql = "UPDATE usuario SET login=:login, nome=:nome,
            opera_pdv=:opera, admin_pdv=:adminPdv, admin=:admin";

    $params = [':login'=>$login, ':nome'=>$nome,
               ':opera'=>$opera, ':adminPdv'=>$adminPdv, ':admin'=>$admin,
               ':codigo'=>$codigo];

    if (!empty($_POST['senha'])) {
        $sql .= ", senha=:senha";
        $params[':senha'] = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    }

    $sql .= " WHERE codigo=:codigo";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: index.php");
}

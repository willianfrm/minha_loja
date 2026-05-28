<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['codigo'])) {
    // Busca dados da família
    $stmt = $pdo->prepare("SELECT * FROM familia WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $_GET['codigo']]);
    $familia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($familia) {
        // Buscar árvore da categoria vinculada
        $stmtCat = $pdo->prepare("SELECT codigo, descricao, nivel, codigo_cat_pai FROM categoria WHERE codigo = :codigo");
        $stmtCat->execute([':codigo' => $familia['codigo_categoria']]);
        $catNivel4 = $stmtCat->fetch(PDO::FETCH_ASSOC);

        $arvore = [];
        if ($catNivel4) {
            $arvore[4] = $catNivel4;
            // Subir até o nível 1
            $pai3 = $catNivel4['codigo_cat_pai'];
            if ($pai3) {
                $stmtCat->execute([':codigo' => $pai3]);
                $arvore[3] = $stmtCat->fetch(PDO::FETCH_ASSOC);
                $pai2 = $arvore[3]['codigo_cat_pai'];
                if ($pai2) {
                    $stmtCat->execute([':codigo' => $pai2]);
                    $arvore[2] = $stmtCat->fetch(PDO::FETCH_ASSOC);
                    $pai1 = $arvore[2]['codigo_cat_pai'];
                    if ($pai1) {
                        $stmtCat->execute([':codigo' => $pai1]);
                        $arvore[1] = $stmtCat->fetch(PDO::FETCH_ASSOC);
                    }
                }
            }
        }
        $familia['arvore'] = $arvore;
    }

    echo json_encode($familia);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $descricao = strtoupper($_POST['descricao']);
    $embalagem = $_POST['embalagem'];
    $margem = $_POST['margem'] ?: null;
    $codigo_categoria = $_POST['codigo_categoria'];

    if (!$codigo_categoria) {
        echo "<script>alert('Selecione uma categoria até o nível 4.'); window.location.href='index.php';</script>";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE familia 
                           SET descricao = :desc, embalagem = :emb, margem = :margem, codigo_categoria = :cat 
                           WHERE codigo = :codigo");
    $stmt->execute([
        ':desc' => $descricao,
        ':emb' => $embalagem,
        ':margem' => $margem,
        ':cat' => $codigo_categoria,
        ':codigo' => $codigo
    ]);
    header("Location: index.php?codigo=".$codigo);
    exit;
}

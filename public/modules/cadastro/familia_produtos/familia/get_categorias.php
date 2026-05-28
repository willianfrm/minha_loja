<?php
session_start();
include __DIR__ . "/../../../../config/db.php";

$nivel = $_GET['nivel'] ?? null;
$pai = $_GET['pai'] ?? null;

if (!$nivel) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT codigo, descricao FROM categoria WHERE nivel = :nivel";
$params = [':nivel' => $nivel];

if ($pai) {
    $sql .= " AND codigo_cat_pai = :pai";
    $params[':pai'] = $pai;
}

$sql .= " ORDER BY descricao";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

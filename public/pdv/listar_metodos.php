<?php
session_start();
include __DIR__ . "/../config/db.php";

$stmt = $pdo->query("SELECT codigo, descricao FROM metodo_pagamento WHERE ativo=1");
$metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($metodos);

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit;
}
include __DIR__ . "/../../../config/db.php";

$codigoFamilia = $_GET['codigo_familia'] ?? '';
if (!$codigoFamilia) {
    exit("Família não informada.");
}

$sql = "SELECT p.codigo, p.descricao_complemento, e.preco_vigente, e.custo_medio, e.custo_ult_entrada,
               f.descricao AS familia_desc, f.margem AS margem_familia, f.codigo_categoria,
               c.margem AS margem_categoria
        FROM produto p
        JOIN familia f ON p.codigo_familia = f.codigo
        LEFT JOIN categoria c ON f.codigo_categoria = c.codigo
        LEFT JOIN estoque e ON e.codigo_produto = p.codigo
        WHERE f.codigo = :codigoFamilia";
$stmt = $pdo->prepare($sql);
$stmt->execute([':codigoFamilia' => $codigoFamilia]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$produtos) {
    exit("Nenhum produto encontrado para esta família.");
}

// Determinar custo base (maior custo da família)
$custoBase = 0;
foreach ($produtos as $p) {
    $custo = max($p['custo_medio'], $p['custo_ult_entrada']);
    if ($custo > $custoBase) {
        $custoBase = $custo;
    }
}

// Determinar margem cadastrada
$margemCadastrada = null;
if ($produtos[0]['margem_familia'] > 0) {
    $margemCadastrada = $produtos[0]['margem_familia'];
} elseif ($produtos[0]['margem_categoria'] > 0) {
    $margemCadastrada = $produtos[0]['margem_categoria'];
} else {
    $margemCadastrada = 30; // padrão
}

// Calcular preço sugerido
function precoSugerido($custo, $margem) {
    $preco = $custo * (1 + $margem/100);
    $novo = ceil(($preco*100)/10)*10 - 1; // arredonda para X,X9
    return $novo/100;
}
$precoSugerido = precoSugerido($custoBase, $margemCadastrada);

// Margem atual (com preço vigente)
$precoAtual = $produtos[0]['preco_vigente'];
$margemAtual = $custoBase > 0 ? (($precoAtual - $custoBase) / $custoBase) * 100 : 0;

// Margem nova (com preço sugerido)
$margemNova = $custoBase > 0 ? (($precoSugerido - $custoBase) / $custoBase) * 100 : 0;

echo "<h5>Família: ".$produtos[0]['familia_desc']." (Código ".$codigoFamilia.")</h5>";
echo "<p><strong>Margem cadastrada:</strong> ".$margemCadastrada."%</p>";
echo "<p><strong>Custo base:</strong> R$ ".number_format($custoBase,2,',','.')."</p>";

echo "<table class='table table-sm table-bordered'>";
echo "<thead><tr><th>Produto</th><th>Preço Atual</th></tr></thead><tbody>";
foreach ($produtos as $p) {
    echo "<tr>
            <td>".htmlspecialchars($p['descricao_complemento'])."</td>
            <td>R$ ".number_format($p['preco_vigente'],2,',','.')."</td>
          </tr>";
}
echo "</tbody></table>";

echo "<div class='mt-3'>";
echo "<p><strong>Margem atual:</strong> ".number_format($margemAtual,2,',','.')."%</p>";
echo "<p><strong>Preço sugerido:</strong> R$ ".number_format($precoSugerido,2,',','.')."</p>";
echo "<p><strong>Margem nova:</strong> ".number_format($margemNova,2,',','.')."%</p>";
echo "<label class='form-label'>Novo Preço</label>";
echo "<input type='text' name='novo_preco' value='".number_format($precoSugerido,2,'.','')."' class='form-control'>";
echo "</div>";

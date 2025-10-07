<?php
// api/get_options.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$tipo = $_GET['tipo'] ?? '';
$tabelas_permitidas = ['setores', 'tipos_manutencao', 'equipamentos'];

if (!in_array($tipo, $tabelas_permitidas)) {
    echo json_encode([]);
    exit;
}

if ($tipo === 'equipamentos') {
    $result = $conn->query("SELECT id, nome, tag, setor_id FROM equipamentos ORDER BY tag");
} else {
    $result = $conn->query("SELECT id, nome FROM {$tipo} ORDER BY nome");
}

$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = $row;
}

echo json_encode($options);

$conn->close();
?>

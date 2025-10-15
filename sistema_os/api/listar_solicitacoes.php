<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Não foi possível buscar as solicitações.'];

$sql = "
    SELECT 
        sol.id,
        sol.solicitante,
        sol.descricao_problema,
        sol.data_solicitacao,
        sol.status,
        sol.ordem_servico_id,
        eq.id as equipamento_id,
        eq.nome as equipamento_nome,
        eq.tag as equipamento_tag,
        s.id as setor_id,
        s.nome as setor_nome
    FROM solicitacoes_servico sol
    JOIN equipamentos eq ON sol.equipamento_id = eq.id
    JOIN setores s ON sol.setor_id = s.id
    ORDER BY sol.data_solicitacao DESC
";

$result = $conn->query($sql);
$data = $result->fetch_all(MYSQLI_ASSOC);

$response['success'] = true;
$response['data'] = $data;

$conn->close();
echo json_encode($response);
?>
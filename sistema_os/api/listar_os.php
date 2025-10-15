<?php
// api/listar_os.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Query para buscar as O.S. com informações das tabelas relacionadas
    $sql = "
        SELECT 
            os.id,
            os.numero_os,
            os.data_inicial,
            os.data_final,
            os.status,
            os.prioridade,
            eq.tag as equipamento_tag,
            eq.nome as equipamento_nome,
            s.nome as setor_nome,
            t.nome as tecnico_nome
        FROM 
            ordens_servico os
        LEFT JOIN 
            equipamentos eq ON os.equipamento_id = eq.id
        LEFT JOIN 
            setores s ON os.setor_id = s.id
        LEFT JOIN
            tecnicos t ON os.tecnico_id = t.id
        ORDER BY 
            os.id DESC";

    $result = $conn->query($sql);

    $response['success'] = true;
    $response['data'] = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
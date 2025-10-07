<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Query para buscar os planos com informações das tabelas relacionadas
    $sql = "
        SELECT 
            pm.id,
            pm.periodicidade,
            pm.data_ultima_preventiva,
            pm.data_proxima_preventiva,
            pm.instrucoes,
            eq.tag as equipamento_tag,
            eq.nome as equipamento_nome,
            s.nome as setor_nome
        FROM 
            planos_manutencao pm
        JOIN 
            equipamentos eq ON pm.equipamento_id = eq.id
        JOIN 
            setores s ON eq.setor_id = s.id
        ORDER BY 
            pm.data_proxima_preventiva ASC";

    $result = $conn->query($sql);

    $response['success'] = true;
    $response['data'] = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
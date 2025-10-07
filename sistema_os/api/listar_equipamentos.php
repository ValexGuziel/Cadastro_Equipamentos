<?php
// api/listar_equipamentos.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // A query com JOIN é mais útil, pois já traz o nome do setor em vez de apenas o ID.
    $sql = "SELECT 
                e.id, 
                e.tag, 
                e.nome,
                e.setor_id, 
                s.nome AS setor_nome 
            FROM 
                equipamentos e
            JOIN 
                setores s ON e.setor_id = s.id
            ORDER BY 
                e.nome ASC";

    $result = $conn->query($sql);

    if ($result) {
        $response['success'] = true;
        $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        throw new Exception('Erro ao buscar equipamentos: ' . $conn->error);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
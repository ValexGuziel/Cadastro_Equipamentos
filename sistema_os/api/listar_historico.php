<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$plano_id = (int)($_GET['plano_id'] ?? 0);

$response = ['success' => false, 'data' => [], 'message' => 'ID do plano não fornecido.'];

if ($plano_id > 0) {
    try {
        $stmt = $conn->prepare("
            SELECT h.data_realizacao, h.observacoes, os.numero_os
            FROM historico_preventivas h
            LEFT JOIN ordens_servico os ON h.ordem_servico_id = os.id
            WHERE h.plano_manutencao_id = ?
            ORDER BY h.data_realizacao DESC
        ");
        $stmt->bind_param("i", $plano_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $response['success'] = true;
        $response['data'] = $result->fetch_all(MYSQLI_ASSOC);
        $response['message'] = '';
    } catch (Exception $e) {
        $response['message'] = 'Erro ao buscar histórico: ' . $e->getMessage();
    }
}

echo json_encode($response);
$conn->close();
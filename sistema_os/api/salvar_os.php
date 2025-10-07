<?php
// api/salvar_os.php
require_once 'db_connect.php';

header('Content-Type: application/json');

/**
 * Lida com a lógica para salvar uma ordem de serviço.
 * @param array $postData The data from the POST request.
 * @param mysqli $conn The database connection object.
 * @return array The response array.
 */
function saveServiceOrder(array $postData, mysqli $conn): array
{
    $response = ['success' => false, 'message' => 'Ocorreu um erro desconhecido.'];

    // Coleta e sanitiza os dados
    $numero_os = trim($postData['numero_os'] ?? '');
    $equipamento_id = (int)($postData['equipamento_id'] ?? 0);
    $setor_id = (int)($postData['setor_id'] ?? 0);
    $tipo_manutencao_id = (int)($postData['tipo_manutencao_id'] ?? 0);
    $area_manutencao = trim($postData['area_manutencao'] ?? '');
    $prioridade = trim($postData['prioridade'] ?? '');
    $solicitante = trim($postData['solicitante'] ?? '');
    $horas_estimadas = (float)($postData['horas_estimadas'] ?? 1.0);
    $status = trim($postData['status'] ?? '');
    $data_inicial = trim($postData['data_inicial'] ?? '');
    $data_final = !empty($postData['data_final']) ? trim($postData['data_final']) : null;
    $descricao_problema = trim($postData['descricao_problema'] ?? '');

    // Validação de campos obrigatórios
    if (empty($numero_os) || empty($equipamento_id) || empty($setor_id) || empty($tipo_manutencao_id) || empty($solicitante) || empty($data_inicial) || empty($descricao_problema)) {
        $response['message'] = 'Campos obrigatórios não foram preenchidos.';
        return $response;
    }

    $sql = "INSERT INTO ordens_servico (numero_os, equipamento_id, setor_id, tipo_manutencao_id, area_manutencao, prioridade, solicitante, horas_estimadas, status, data_inicial, data_final, descricao_problema) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response['message'] = 'Erro na preparação da query: ' . $conn->error;
        return $response;
    }

    $stmt->bind_param('siiisssdssss', $numero_os, $equipamento_id, $setor_id, $tipo_manutencao_id, $area_manutencao, $prioridade, $solicitante, $horas_estimadas, $status, $data_inicial, $data_final, $descricao_problema);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Ordem de Serviço salva com sucesso!';
        $response['numero_os'] = $numero_os;
        $response['id'] = $conn->insert_id;
    } else {
        if ($conn->errno == 1062) { // Código de erro para entrada duplicada
            $response['message'] = 'O número da O.S. (' . htmlspecialchars($numero_os) . ') já existe. Tente novamente.';
        } else {
            $response['message'] = 'Erro ao salvar a Ordem de Serviço: ' . $stmt->error;
        }
    }
    $stmt->close();

    return $response;
}

// Bloco principal que lida com a requisição HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = saveServiceOrder($_POST, $conn);
} else {
    $response = ['success' => false, 'message' => 'Método de requisição inválido.'];
}

$conn->close();
echo json_encode($response);
?>

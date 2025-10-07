<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

/**
 * Atualiza um plano de manutenção existente.
 * @param array $postData Os dados da requisição POST.
 * @param mysqli $conn O objeto de conexão com o banco de dados.
 * @return array A resposta da operação.
 */
function updatePlanoManutencao(array $postData, mysqli $conn): array
{
    $response = ['success' => false, 'message' => 'Ocorreu um erro desconhecido.'];

    $id = (int)($postData['id'] ?? 0);
    $periodicidade = trim($postData['periodicidade'] ?? '');
    $data_ultima_preventiva_raw = trim($postData['data_ultima_preventiva'] ?? '');
    $instrucoes = trim($postData['instrucoes'] ?? '');

    // Validação
    if (empty($id) || empty($periodicidade) || empty($data_ultima_preventiva_raw)) {
        $response['message'] = 'ID, periodicidade e data da última preventiva são obrigatórios.';
        return $response;
    }

    $data_ultima_preventiva = (new DateTime($data_ultima_preventiva_raw))->format('Y-m-d');
    if (!$data_ultima_preventiva) {
        $response['message'] = 'Data da última preventiva inválida.';
        return $response;
    }

    // Calcular a próxima data
    $periodicidade_dias = ['Semanal' => 7, 'Quinzenal' => 15, 'Mensal' => 30, 'Bimestral' => 60, 'Trimestral' => 90, 'Semestral' => 180, 'Anual' => 365];
    $dias_a_adicionar = $periodicidade_dias[$periodicidade] ?? 0;
    $data_proxima_preventiva = (new DateTime($data_ultima_preventiva))->modify("+$dias_a_adicionar days")->format('Y-m-d');

    $stmt = $conn->prepare("UPDATE planos_manutencao SET periodicidade = ?, data_ultima_preventiva = ?, data_proxima_preventiva = ?, instrucoes = ? WHERE id = ?");
    if (!$stmt) {
        $response['message'] = 'Erro na preparação da query: ' . $conn->error;
        return $response;
    }

    $stmt->bind_param("ssssi", $periodicidade, $data_ultima_preventiva, $data_proxima_preventiva, $instrucoes, $id);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Plano de Manutenção atualizado com sucesso!';
    } else {
        $response['message'] = 'Erro ao atualizar no banco de dados: ' . $stmt->error;
    }
    $stmt->close();
    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = updatePlanoManutencao($_POST, $conn);
} else {
    $response = ['success' => false, 'message' => 'Método de requisição inválido.'];
}

$conn->close();
echo json_encode($response);
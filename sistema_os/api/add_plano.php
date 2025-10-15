<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

/**
 * Converte uma data de Y-m-d para d/m/Y e vice-versa.
 * @param string $date A data a ser convertida.
 * @param string $format O formato de destino ('Y-m-d' ou 'd/m/Y').
 * @return string|null A data formatada ou null se a entrada for inválida.
 */
function formatDate(string $date, string $format = 'Y-m-d'): ?string {
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Adiciona um novo plano de manutenção.
 * @param array $postData Os dados da requisição POST.
 * @param mysqli $conn O objeto de conexão com o banco de dados.
 * @return array A resposta da operação.
 */
function addPlanoManutencao(array $postData, mysqli $conn): array
{
    $response = ['success' => false, 'message' => 'Ocorreu um erro desconhecido.'];

    $equipamento_id = (int)($postData['equipamento_id'] ?? 0);
    $periodicidade = trim($postData['periodicidade'] ?? '');
    $data_ultima_preventiva_raw = trim($postData['data_ultima_preventiva'] ?? '');
    $instrucoes = trim($postData['instrucoes'] ?? '');

    // Validação
    if (empty($equipamento_id) || empty($periodicidade) || empty($data_ultima_preventiva_raw)) {
        $response['message'] = 'Equipamento, periodicidade e data da última preventiva são obrigatórios.';
        return $response;
    }

    $data_ultima_preventiva = formatDate($data_ultima_preventiva_raw, 'Y-m-d H:i:s');
    if (!$data_ultima_preventiva) {
        $response['message'] = 'Data da última preventiva inválida.';
        return $response;
    }

    // Calcular a próxima data
    $periodicidade_dias = ['Semanal' => 7, 'Quinzenal' => 15, 'Mensal' => 30, 'Bimestral' => 60, 'Trimestral' => 90, 'Semestral' => 180, 'Anual' => 365];
    $dias_a_adicionar = $periodicidade_dias[$periodicidade] ?? 0;
    $data_proxima_preventiva = (new DateTime($data_ultima_preventiva))->modify("+$dias_a_adicionar days")->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO planos_manutencao (equipamento_id, periodicidade, data_ultima_preventiva, data_proxima_preventiva, instrucoes) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        $response['message'] = 'Erro na preparação da query: ' . $conn->error;
        return $response;
    }

    $stmt->bind_param("issss", $equipamento_id, $periodicidade, $data_ultima_preventiva, $data_proxima_preventiva, $instrucoes);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Plano de Manutenção salvo com sucesso!';
        $response['equipamento_id'] = $equipamento_id;
        $response['plano_id'] = $conn->insert_id;
    } else {
        $response['message'] = ($conn->errno == 1062) ? 'Já existe um plano de manutenção para este equipamento.' : 'Erro ao salvar no banco de dados: ' . $stmt->error;
    }
    $stmt->close();
    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = addPlanoManutencao($_POST, $conn);
} else {
    $response = ['success' => false, 'message' => 'Método de requisição inválido.'];
}

$conn->close();
echo json_encode($response);
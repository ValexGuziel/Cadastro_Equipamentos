<?php
// api/atualizar_os.php
require_once 'db_connect.php';

header('Content-Type: application/json');

/**
 * Atualiza o plano de manutenção e registra o histórico quando uma O.S. preventiva é concluída.
 * @param int $os_id ID da Ordem de Serviço.
 * @param int $equipamento_id ID do Equipamento.
 * @param string $data_final Data de conclusão da O.S.
 * @param mysqli $conn Objeto de conexão com o banco de dados.
 */
function registrarHistoricoPreventiva(int $os_id, int $equipamento_id, string $data_final, mysqli $conn) {
    // 1. Encontrar o plano de manutenção do equipamento
    $stmt_plano = $conn->prepare("SELECT id, periodicidade FROM planos_manutencao WHERE equipamento_id = ?");
    $stmt_plano->bind_param("i", $equipamento_id);
    $stmt_plano->execute();
    $plano_result = $stmt_plano->get_result();

    if ($plano_result->num_rows > 0) {
        $plano = $plano_result->fetch_assoc();
        $plano_id = $plano['id'];
        $periodicidade = $plano['periodicidade'];

        // Inicia uma transação para garantir a consistência dos dados
        $conn->begin_transaction();

        try {
            // 2. Inserir no histórico
            $stmt_hist = $conn->prepare("INSERT INTO historico_preventivas (plano_manutencao_id, ordem_servico_id, data_realizacao) VALUES (?, ?, ?)");
            $stmt_hist->bind_param("iis", $plano_id, $os_id, $data_final);
            $stmt_hist->execute();

            // 3. Atualizar o plano de manutenção principal
            $periodicidade_dias = ['Semanal' => 7, 'Quinzenal' => 15, 'Mensal' => 30, 'Bimestral' => 60, 'Trimestral' => 90, 'Semestral' => 180, 'Anual' => 365];
            $dias_a_adicionar = $periodicidade_dias[$periodicidade] ?? 0;
            $data_proxima_preventiva = (new DateTime($data_final))->modify("+$dias_a_adicionar days")->format('Y-m-d');

            $stmt_update_plano = $conn->prepare("UPDATE planos_manutencao SET data_ultima_preventiva = ?, data_proxima_preventiva = ? WHERE id = ?");
            $stmt_update_plano->bind_param("ssi", $data_final, $data_proxima_preventiva, $plano_id);
            $stmt_update_plano->execute();

            $conn->commit(); // Confirma as alterações se tudo deu certo
        } catch (Exception $e) {
            $conn->rollback(); // Desfaz as alterações em caso de erro
        }
    }
}

$response = ['success' => false, 'message' => 'Método de requisição inválido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta e sanitiza os dados
    $id = (int)($_POST['id'] ?? 0);
    $equipamento_id = (int)($_POST['equipamento_id'] ?? 0);
    $setor_id = (int)($_POST['setor_id'] ?? 0);
    $tipo_manutencao_id = (int)($_POST['tipo_manutencao_id'] ?? 0);
    $area_manutencao = trim($_POST['area_manutencao'] ?? '');
    $prioridade = trim($_POST['prioridade'] ?? '');
    $solicitante = trim($_POST['solicitante'] ?? '');
    $horas_estimadas = (float)($_POST['horas_estimadas'] ?? 1.0);
    $status = trim($_POST['status'] ?? '');
    $data_inicial = trim($_POST['data_inicial'] ?? '');
    $data_final = !empty($_POST['data_final']) ? trim($_POST['data_final']) : null;
    $descricao_problema = trim($_POST['descricao_problema'] ?? '');
    $custo_pecas = (float)($_POST['custo_pecas'] ?? 0.0);
    $custo_mao_de_obra = (float)($_POST['custo_mao_de_obra'] ?? 0.0);

    if (empty($id) || empty($equipamento_id) || empty($setor_id) || empty($tipo_manutencao_id) || empty($solicitante) || empty($data_inicial) || empty($descricao_problema)) {
        http_response_code(400);
        $response['message'] = 'Todos os campos obrigatórios devem ser preenchidos.';
    } else {
        $sql = "UPDATE ordens_servico SET 
                    equipamento_id = ?, setor_id = ?, tipo_manutencao_id = ?, area_manutencao = ?, 
                    prioridade = ?, solicitante = ?, horas_estimadas = ?, status = ?, data_inicial = ?, data_final = ?, 
                    descricao_problema = ?, custo_pecas = ?, custo_mao_de_obra = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        // Tipos: i=integer, s=string, d=double
        $stmt->bind_param('iiisssdsdssddi', 
            $equipamento_id, $setor_id, $tipo_manutencao_id, $area_manutencao, 
            $prioridade, $solicitante, $horas_estimadas, $status, $data_inicial, $data_final, 
            $descricao_problema, $custo_pecas, $custo_mao_de_obra, $id
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Ordem de Serviço atualizada com sucesso!';

            // Verifica se é uma O.S. de Preventiva que foi concluída
            $stmt_tipo = $conn->prepare("SELECT nome FROM tipos_manutencao WHERE id = ?");
            $stmt_tipo->bind_param("i", $tipo_manutencao_id);
            $stmt_tipo->execute();
            $tipo_result = $stmt_tipo->get_result()->fetch_assoc();

            if ($status === 'Concluída' && $tipo_result && strtolower($tipo_result['nome']) === 'preventiva' && $data_final) {
                registrarHistoricoPreventiva($id, $equipamento_id, (new DateTime($data_final))->format('Y-m-d'), $conn);
            }
        } else {
            http_response_code(500);
            $response['message'] = 'Erro ao atualizar a Ordem de Serviço: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>
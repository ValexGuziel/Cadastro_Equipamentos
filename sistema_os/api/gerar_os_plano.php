<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Requisição inválida.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode($response);
    exit;
}

/**
 * Gera um número de O.S. único.
 * @param mysqli $conn Conexão com o banco de dados.
 * @return string O novo número da O.S.
 */
function gerarNumeroOS(mysqli $conn): string {
    $result = $conn->query("SELECT MAX(id) AS last_id FROM ordens_servico");
    $row = $result->fetch_assoc();
    $next_id = ($row['last_id'] ?? 0) + 1;
    $numero_formatado = str_pad($next_id, 5, '0', STR_PAD_LEFT);
    $data_atual = date('Y-m-d');
    return "{$numero_formatado}-{$data_atual}";
}

$plano_id = (int)($_POST['plano_id'] ?? 0);

if (empty($plano_id)) {
    http_response_code(400); // Bad Request
    $response['message'] = 'ID do plano não fornecido.';
    echo json_encode($response);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Buscar dados do plano e do equipamento associado
    $stmt_plano = $conn->prepare("
        SELECT 
            p.id, p.equipamento_id, p.periodicidade, p.instrucoes,
            e.setor_id
        FROM planos_manutencao p
        JOIN equipamentos e ON p.equipamento_id = e.id
        WHERE p.id = ?
    ");
    $stmt_plano->bind_param("i", $plano_id);
    $stmt_plano->execute();
    $plano_result = $stmt_plano->get_result();

    if ($plano_result->num_rows === 0) {
        throw new Exception("Plano de manutenção não encontrado.");
    }
    $plano = $plano_result->fetch_assoc();

    // 2. Buscar ID do tipo de manutenção 'Preventiva'
    $stmt_tipo = $conn->prepare("SELECT id FROM tipos_manutencao WHERE nome LIKE 'Preventiva' LIMIT 1");
    $stmt_tipo->execute();
    $tipo_result = $stmt_tipo->get_result()->fetch_assoc();
    if (!$tipo_result) {
        throw new Exception("Tipo de manutenção 'Preventiva' não encontrado no sistema.");
    }
    $tipo_manutencao_id = $tipo_result['id'];

    // 3. Gerar nova O.S.
    $novo_numero_os = gerarNumeroOS($conn);
    $data_atual = date('Y-m-d H:i:s');
    $descricao_os = "Manutenção Preventiva conforme plano.\n\nInstruções:\n" . $plano['instrucoes'];

    $stmt_insert_os = $conn->prepare(
        "INSERT INTO ordens_servico (numero_os, equipamento_id, setor_id, tipo_manutencao_id, area_manutencao, prioridade, solicitante, status, data_inicial, descricao_problema) 
         VALUES (?, ?, ?, ?, 'Preventiva', 'Média', 'Sistema (Plano)', 'Aberta', ?, ?)"
    );
    $stmt_insert_os->bind_param("siiiss", $novo_numero_os, $plano['equipamento_id'], $plano['setor_id'], $tipo_manutencao_id, $data_atual, $descricao_os);
    $stmt_insert_os->execute();
    $nova_os_id = $conn->insert_id;

    if ($nova_os_id === 0) {
        throw new Exception("Falha ao inserir a nova Ordem de Serviço.");
    }

    // 4. Atualizar o plano de manutenção com a nova data (opcional, mas recomendado)
    // A data será efetivamente atualizada quando a O.S. for concluída, mas podemos "empurrar" para evitar gerar O.S. duplicadas.
    $periodicidade_dias = ['Semanal' => 7, 'Quinzenal' => 15, 'Mensal' => 30, 'Bimestral' => 60, 'Trimestral' => 90, 'Semestral' => 180, 'Anual' => 365];
    $dias_a_adicionar = $periodicidade_dias[$plano['periodicidade']] ?? 30; // Default 30 dias
    $data_proxima_preventiva = (new DateTime())->modify("+$dias_a_adicionar days")->format('Y-m-d H:i:s');

    $stmt_update_plano = $conn->prepare("UPDATE planos_manutencao SET data_proxima_preventiva = ? WHERE id = ?");
    $stmt_update_plano->bind_param("si", $data_proxima_preventiva, $plano_id);
    $stmt_update_plano->execute();

    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'O.S. Preventiva gerada com sucesso!';
    $response['numero_os'] = $novo_numero_os;
    $response['os_id'] = $nova_os_id;

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Não foi possível calcular os KPIs.',
    'data' => [
        'mtbf' => 0,
        'mttr' => 0,
        'mp_compliance' => 0,
        'backlog' => 0
    ]
];

try {
    // --- 1. Cálculo do Backlog (em Horas) ---
    // Soma as horas estimadas de todas as O.S. que não estão 'Concluída' ou 'Cancelada'.
    $result_backlog = $conn->query("SELECT SUM(horas_estimadas) AS total_horas FROM ordens_servico WHERE status NOT IN ('Concluída', 'Cancelada')");
    $backlog_data = $result_backlog->fetch_assoc();
    $response['data']['backlog'] = round((float) ($backlog_data['total_horas'] ?? 0), 2);

    // --- 2. Cálculo do MTTR (Tempo Médio Para Reparo) em horas ---
    // Média do tempo entre a abertura e a conclusão das O.S. corretivas.
    $query_mttr = "
        SELECT AVG(TIMESTAMPDIFF(HOUR, data_inicial, data_final)) AS mttr_horas
        FROM ordens_servico
        WHERE status = 'Concluída'
        AND data_final IS NOT NULL
        AND tipo_manutencao_id = (SELECT id FROM tipos_manutencao WHERE nome LIKE '%Corretiva%' LIMIT 1)
    ";
    $result_mttr = $conn->query($query_mttr);
    $mttr_data = $result_mttr->fetch_assoc();
    // Arredonda para 2 casas decimais. Se for null (nenhuma OS), o valor é 0.
    $response['data']['mttr'] = round((float) ($mttr_data['mttr_horas'] ?? 0), 2);


    // --- 3. Cálculo do Cumprimento de Preventivas (MP) em % ---
    // (Planos executados no prazo / Planos totais vencidos ou executados) * 100
    $query_mp = "
        SELECT
            -- Conta planos cuja última preventiva foi feita ANTES da data da próxima planejada.
            SUM(CASE WHEN p.data_ultima_preventiva <= p.data_proxima_preventiva THEN 1 ELSE 0 END) AS executadas_no_prazo,
            -- Conta todos os planos que já tiveram uma data de próxima preventiva no passado.
            COUNT(p.id) AS total_vencidas
        FROM planos_manutencao p
        WHERE p.data_proxima_preventiva <= CURDATE()
    ";
    $result_mp = $conn->query($query_mp);
    $mp_data = $result_mp->fetch_assoc();

    $executadas_no_prazo = (int) ($mp_data['executadas_no_prazo'] ?? 0);
    $total_vencidas = (int) ($mp_data['total_vencidas'] ?? 0);

    if ($total_vencidas > 0) {
        $response['data']['mp_compliance'] = round(($executadas_no_prazo / $total_vencidas) * 100, 2);
    } else {
        $response['data']['mp_compliance'] = 100; // Se não há preventivas vencidas, o cumprimento é 100%
    }

    // --- 4. Cálculo do MTBF (Tempo Médio Entre Falhas) em horas ---
    // Este é um cálculo complexo. A abordagem aqui é uma simplificação comum:
    // (Soma do tempo de operação entre falhas consecutivas) / (Número de falhas - 1)
    // Vamos calcular por equipamento e depois tirar a média geral.

    $query_falhas = "
        SELECT
            equipamento_id,
            data_inicial AS data_falha,
            data_final AS data_reparo
        FROM ordens_servico
        WHERE status = 'Concluída'
        AND tipo_manutencao_id = (SELECT id FROM tipos_manutencao WHERE nome LIKE '%Corretiva%' LIMIT 1)
        ORDER BY equipamento_id, data_inicial ASC
    ";
    $result_falhas = $conn->query($query_falhas);

    $tempos_operacao = [];
    $falhas_por_equipamento = [];
    while ($falha = $result_falhas->fetch_assoc()) {
        $falhas_por_equipamento[$falha['equipamento_id']][] = $falha;
    }

    foreach ($falhas_por_equipamento as $equip_id => $falhas) {
        if (count($falhas) > 1) {
            for ($i = 0; $i < count($falhas) - 1; $i++) {
                $reparo_anterior = new DateTime($falhas[$i]['data_reparo']);
                $falha_seguinte = new DateTime($falhas[$i + 1]['data_falha']);
                $diff = $falha_seguinte->getTimestamp() - $reparo_anterior->getTimestamp();
                $tempos_operacao[] = $diff / 3600; // Converte para horas
            }
        }
    }

    if (count($tempos_operacao) > 0) {
        $response['data']['mtbf'] = round(array_sum($tempos_operacao) / count($tempos_operacao), 2);
    } // Se não, mantém 0

    $response['success'] = true;
    $response['message'] = 'KPIs calculados com sucesso.';

} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
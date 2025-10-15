<?php
// api/kpi_data.php

require_once 'db_connect.php';
header('Content-Type: application/json');

$period = $_GET['period'] ?? '90'; // Padrão de 90 dias
$period_filter_os = ""; // Filtro para a tabela 'ordens_servico' com alias 'os'
$period_filter_no_alias_final = ""; // Filtro para tabelas sem alias, baseado na data final
$period_filter_no_alias_inicial = ""; // Filtro para tabelas sem alias, baseado na data inicial
if (is_numeric($period)) {
    $period_filter_os = "AND os.data_final >= DATE_SUB(NOW(), INTERVAL $period DAY)";
    $period_filter_no_alias_final = "AND data_final >= DATE_SUB(NOW(), INTERVAL $period DAY)";
    $period_filter_no_alias_inicial = "AND data_inicial >= DATE_SUB(NOW(), INTERVAL $period DAY)";
}

$response = ['success' => true, 'data' => []];

try {
    // --- MTBF (Mean Time Between Failures) ---
    // Simplificado: Média de dias entre falhas corretivas para um mesmo equipamento.
    // Uma implementação mais robusta agruparia por equipamento.
    $sql_mtbf = "
        SELECT AVG(DATEDIFF(next_failure, data_inicial)) as mtbf_days
        FROM (
            SELECT 
                data_inicial, 
                LEAD(data_inicial, 1) OVER (PARTITION BY equipamento_id ORDER BY data_inicial) as next_failure
            FROM ordens_servico
            WHERE tipo_manutencao_id = (SELECT id FROM tipos_manutencao WHERE nome LIKE 'Corretiva')
              AND status = 'Concluída' $period_filter_no_alias_final
        ) as failures
        WHERE next_failure IS NOT NULL
    ";
    $mtbf_result = $conn->query($sql_mtbf);
    $mtbf_data = $mtbf_result->fetch_assoc();
    $response['data']['mtbf'] = round(($mtbf_data['mtbf_days'] ?? 0) * 24, 2); // Em horas

    // --- MTTR (Mean Time To Repair) ---
    $sql_mttr = "
        SELECT AVG(TIMESTAMPDIFF(HOUR, data_inicial, data_final)) as mttr_hours
        FROM ordens_servico
        WHERE status = 'Concluída' AND data_final IS NOT NULL $period_filter_no_alias_final
    ";
    $mttr_result = $conn->query($sql_mttr);
    $mttr_data = $mttr_result->fetch_assoc();
    $response['data']['mttr'] = round($mttr_data['mttr_hours'] ?? 0, 2);

    // --- Cumprimento de Preventivas (%) ---
    $sql_mp_total = "SELECT COUNT(id) as total FROM ordens_servico WHERE tipo_manutencao_id = (SELECT id FROM tipos_manutencao WHERE nome LIKE 'Preventiva') $period_filter_no_alias_inicial";
    $sql_mp_concluidas = "SELECT COUNT(id) as concluidas FROM ordens_servico WHERE tipo_manutencao_id = (SELECT id FROM tipos_manutencao WHERE nome LIKE 'Preventiva') AND status = 'Concluída' $period_filter_no_alias_final";
    $total_mp = $conn->query($sql_mp_total)->fetch_assoc()['total'] ?? 0;
    $concluidas_mp = $conn->query($sql_mp_concluidas)->fetch_assoc()['concluidas'] ?? 0;
    $response['data']['mp_compliance'] = ($total_mp > 0) ? round(($concluidas_mp / $total_mp) * 100, 2) : 0;

    // --- Backlog (Horas) ---
    $sql_backlog = "SELECT SUM(horas_estimadas) as backlog_hours FROM ordens_servico WHERE status = 'Aberta'";
    $backlog_result = $conn->query($sql_backlog);
    $response['data']['backlog'] = round($backlog_result->fetch_assoc()['backlog_hours'] ?? 0, 2);

    // --- Fator de Produtividade da Mão de Obra ---
    $sql_prod = "
        SELECT 
            SUM(horas_estimadas) as total_estimado,
            SUM(TIMESTAMPDIFF(HOUR, data_inicial, data_final)) as total_real
        FROM ordens_servico 
        WHERE status = 'Concluída' AND data_final IS NOT NULL AND data_inicial IS NOT NULL $period_filter_no_alias_final
    ";
    $prod_result = $conn->query($sql_prod)->fetch_assoc();
    $total_estimado = $prod_result['total_estimado'] ?? 0;
    $total_real = $prod_result['total_real'] ?? 0;
    
    // Calcula o fator de produtividade (resultado entre 0 e 1, que pode ser formatado como %)
    // Um valor de 1.0 significa que o tempo real foi igual ao estimado.
    // Um valor > 1.0 significa que a equipe foi mais rápida que o estimado (produtiva).
    // Um valor < 1.0 significa que a equipe demorou mais que o estimado.
    $response['data']['produtividade'] = ($total_real > 0) ? round($total_estimado / $total_real, 2) : 0;

    // --- O.S. Concluídas por Técnico ---
    $sql_os_tecnico = "
        SELECT
            t.nome as tecnico_nome,
            COUNT(os.id) as total_os
        FROM ordens_servico os
        JOIN tecnicos t ON os.tecnico_id = t.id
        WHERE os.status = 'Concluída' AND os.tecnico_id IS NOT NULL $period_filter_os
        GROUP BY t.nome
        HAVING total_os > 0
        ORDER BY total_os DESC
    ";
    $os_tecnico_result = $conn->query($sql_os_tecnico);
    $response['data']['os_por_tecnico'] = $os_tecnico_result->fetch_all(MYSQLI_ASSOC);


} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

$conn->close();
echo json_encode($response);
?>

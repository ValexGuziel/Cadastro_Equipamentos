<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Método de requisição inválido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta e sanitiza os dados
    $equipamento_id = (int)($_POST['equipamento_id'] ?? 0);
    $setor_id = (int)($_POST['setor_id'] ?? 0);
    $solicitante = trim($_POST['solicitante'] ?? '');
    $descricao_problema = trim($_POST['descricao_problema'] ?? '');

    // Validação
    if (empty($equipamento_id) || empty($setor_id) || empty($solicitante) || empty($descricao_problema)) {
        http_response_code(400);
        $response['message'] = 'Todos os campos são obrigatórios.';
        echo json_encode($response);
        exit;
    }

    $sql = "INSERT INTO solicitacoes_servico (equipamento_id, setor_id, solicitante, descricao_problema) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $equipamento_id, $setor_id, $solicitante, $descricao_problema);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Solicitação de serviço enviada com sucesso!';
    } else {
        http_response_code(500);
        $response['message'] = 'Erro ao salvar a solicitação: ' . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
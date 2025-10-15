<?php
// api/aprovar_solicitacao.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Método de requisição inválido ou ID não fornecido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    if (empty($id)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'ID da Solicitação é obrigatório.';
    } else {
        // Atualiza o status da solicitação para 'Aprovada'
        $stmt = $conn->prepare("UPDATE solicitacoes_servico SET status = 'Aprovada' WHERE id = ? AND status = 'Pendente'");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Solicitação aprovada com sucesso.';
            } else {
                http_response_code(404); // Not Found or Not Modified
                $response['message'] = 'Nenhuma solicitação pendente encontrada com o ID fornecido.';
            }
        } else {
            http_response_code(500); // Internal Server Error
            $response['message'] = 'Erro no banco de dados ao tentar aprovar a solicitação: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>
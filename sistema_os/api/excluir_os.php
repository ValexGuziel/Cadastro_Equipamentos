<?php
// api/excluir_os.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Método de requisição inválido ou ID não fornecido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usamos file_get_contents e json_decode para aceitar JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    if (empty($id)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'ID da Ordem de Serviço é obrigatório.';
        echo json_encode($response);
        exit;
    }

    // Antes de excluir, podemos verificar se a O.S. não está vinculada a um histórico que não pode ser quebrado.
    // Neste caso, a constraint `ON DELETE SET NULL` em `historico_preventivas` já lida com isso.

    $stmt = $conn->prepare("DELETE FROM ordens_servico WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Ordem de Serviço excluída com sucesso.';
        } else {
            http_response_code(404); // Not Found
            $response['message'] = 'Nenhuma Ordem de Serviço encontrada com o ID fornecido.';
        }
    } else {
        http_response_code(500); // Internal Server Error
        $response['message'] = 'Erro no banco de dados ao tentar excluir a O.S.: ' . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

/**
 * Exclui um plano de manutenção.
 * @param mysqli $conn O objeto de conexão com o banco de dados.
 * @return array A resposta da operação.
 */
function excluirPlanoManutencao(mysqli $conn): array
{
    $response = ['success' => false, 'message' => 'Requisição inválida.'];

    // Decodifica o corpo da requisição JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    if (empty($id)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'O ID do plano de manutenção é obrigatório.';
        return $response;
    }

    // A constraint `ON DELETE CASCADE` na tabela `historico_preventivas`
    // garantirá que o histórico associado também seja removido.
    $stmt = $conn->prepare("DELETE FROM planos_manutencao WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Plano de manutenção excluído com sucesso.';
        } else {
            http_response_code(404); // Not Found
            $response['message'] = 'Nenhum plano de manutenção encontrado com o ID fornecido.';
        }
    } else {
        http_response_code(500); // Internal Server Error
        $response['message'] = 'Erro no banco de dados ao tentar excluir o plano: ' . $stmt->error;
    }
    $stmt->close();
    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = excluirPlanoManutencao($conn);
} else {
    $response = ['success' => false, 'message' => 'Método de requisição inválido.'];
    http_response_code(405); // Method Not Allowed
}

$conn->close();
echo json_encode($response);
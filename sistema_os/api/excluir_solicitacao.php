<?php
// api/excluir_solicitacao.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Método de requisição inválido ou ID não fornecido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usamos file_get_contents e json_decode para aceitar JSON, que é mais flexível
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    if (empty($id)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'ID da Solicitação é obrigatório.';
    } else {
        $conn->begin_transaction();
        try {
            // 1. Verificar se existe uma OS vinculada
            $stmt_check = $conn->prepare("SELECT ordem_servico_id FROM solicitacoes_servico WHERE id = ?");
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $solicitacao = $result_check->fetch_assoc();

            if (!$solicitacao) {
                throw new Exception('Nenhuma solicitação encontrada com o ID fornecido.', 404);
            }

            // 2. Se houver OS, excluí-la primeiro
            if (!empty($solicitacao['ordem_servico_id'])) {
                $os_id = $solicitacao['ordem_servico_id'];
                $stmt_delete_os = $conn->prepare("DELETE FROM ordens_servico WHERE id = ?");
                $stmt_delete_os->bind_param("i", $os_id);
                $stmt_delete_os->execute();
            }

            // 3. Excluir a solicitação
            $stmt_delete_sol = $conn->prepare("DELETE FROM solicitacoes_servico WHERE id = ?");
            $stmt_delete_sol->bind_param("i", $id);
            $stmt_delete_sol->execute();

            if ($stmt_delete_sol->affected_rows > 0) {
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Solicitação e O.S. associada (se houver) foram excluídas com sucesso.';
            } else {
                // Isso não deveria acontecer se o primeiro check passou, mas é uma segurança
                throw new Exception('Falha ao excluir a solicitação, apesar de ter sido encontrada.', 500);
            }

        } catch (Exception $e) {
            $conn->rollback();
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            http_response_code($code);
            $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }
}

$conn->close();
echo json_encode($response);
?>
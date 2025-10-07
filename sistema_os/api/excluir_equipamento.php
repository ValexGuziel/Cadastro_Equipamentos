<?php
// api/excluir_equipamento.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Método de requisição inválido ou ID não fornecido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if (empty($id)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'ID do equipamento é obrigatório.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM equipamentos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Equipamento excluído com sucesso.';
        } else {
            http_response_code(404); // Not Found
            $response['message'] = 'Nenhum equipamento encontrado com o ID fornecido.';
        }
    } else {
        http_response_code(500); // Internal Server Error
        // Erro 1451: Foreign Key constraint. O equipamento está em uso.
        if ($conn->errno == 1451) {
            $response['message'] = 'Não é possível excluir o equipamento, pois ele está associado a uma ou mais Ordens de Serviço.';
        } else {
            $response['message'] = 'Erro no banco de dados: ' . $stmt->error;
        }
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
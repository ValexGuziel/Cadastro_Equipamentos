<?php
// api/atualizar_equipamento.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Método de requisição inválido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Coleta e validação dos dados
    $id = (int)($_POST['id'] ?? 0);
    $tag = trim($_POST['tag'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $setor_id = (int)($_POST['setor_id'] ?? 0);

    if (empty($id) || empty($tag) || empty($nome) || empty($setor_id)) {
        http_response_code(400); // Bad Request
        $response['message'] = 'Todos os campos são obrigatórios.';
    } else {
        // 2. Preparação da query SQL de atualização
        $sql = "UPDATE equipamentos SET tag = ?, nome = ?, setor_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            http_response_code(500);
            $response['message'] = 'Erro na preparação da query: ' . $conn->error;
        } else {
            // 3. Bind dos parâmetros e execução
            $stmt->bind_param("ssii", $tag, $nome, $setor_id, $id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Equipamento atualizado com sucesso!';
            } else {
                http_response_code(500);
                // Erro 1062: Chave duplicada (para a 'tag')
                if ($conn->errno == 1062) {
                    $response['message'] = 'A Tag (' . htmlspecialchars($tag) . ') informada já está em uso por outro equipamento.';
                } else {
                    $response['message'] = 'Erro ao atualizar no banco de dados: ' . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}

$conn->close();
echo json_encode($response);
?>
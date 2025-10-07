<?php
// api/add_equipamento.php
require_once 'db_connect.php';

header('Content-Type: application/json');

/**
 * Lida com a lógica para adicionar um novo equipamento.
 * @param array $postData Os dados da requisição POST.
 * @param mysqli $conn O objeto de conexão com o banco de dados.
 * @return array A resposta da operação.
 */
function addEquipamento(array $postData, mysqli $conn): array
{
    $response = ['success' => false, 'message' => 'Ocorreu um erro desconhecido.'];

    $tag = trim($postData['tag'] ?? '');
    $nome = trim($postData['nome'] ?? '');
    $setor_id = (int)($postData['setor_id'] ?? 0);

    if (empty($tag) || empty($nome) || empty($setor_id)) {
        $response['message'] = 'Todos os campos são obrigatórios.';
        return $response;
    }

    $stmt = $conn->prepare("INSERT INTO equipamentos (tag, nome, setor_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        $response['message'] = 'Erro na preparação da query: ' . $conn->error;
        return $response;
    }

    $stmt->bind_param("ssi", $tag, $nome, $setor_id);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Equipamento adicionado com sucesso!';
        $response['id'] = $conn->insert_id;
    } else {
        $response['message'] = ($conn->errno == 1062) ? 'A Tag (' . htmlspecialchars($tag) . ') informada já existe.' : 'Erro ao salvar no banco de dados: ' . $stmt->error;
    }
    $stmt->close();
    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = addEquipamento($_POST, $conn);
} else {
    $response = ['success' => false, 'message' => 'Método de requisição inválido.'];
}

$conn->close();
echo json_encode($response);
?>
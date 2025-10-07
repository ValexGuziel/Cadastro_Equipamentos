<?php
// api/add_setor.php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');

    if (empty($nome)) {
        $response['message'] = 'O nome do setor não pode ser vazio.';
    } else {
        $stmt = $conn->prepare("INSERT INTO setores (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['id'] = $conn->insert_id; // Retorna o ID do novo setor
        } else {
            $response['message'] = 'Erro ao salvar no banco de dados: ' . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
}

$conn->close();
echo json_encode($response);
?>

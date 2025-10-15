<?php

require_once 'api/db_connect.php';

$equipamento_id = (int)($_GET['id'] ?? 0);
$equipamento = null;
$setores = $conn->query("SELECT * FROM setores ORDER BY nome");

if ($equipamento_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM equipamentos WHERE id = ?");
    $stmt->bind_param("i", $equipamento_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipamento = $result->fetch_assoc();
    $stmt->close();
}

if (!$equipamento) {
    die("Equipamento não encontrado!");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Equipamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Equipamento: <?= htmlspecialchars($equipamento['nome']) ?></h2>
        <hr>

        <!-- Placeholder para mensagens de sucesso ou erro -->
        <div id="form-message" class="alert" role="alert" style="display: none;"></div>

        <?php if ($equipamento): ?>
        <form id="form-edit-equipamento" action="api/atualizar_equipamento.php" method="POST">
            <!-- Campo oculto para enviar o ID -->
            <input type="hidden" name="id" value="<?= $equipamento['id'] ?>">

            <div class="mb-3">
                <label for="tag" class="form-label">Tag</label>
                <input type="text" class="form-control" id="tag" name="tag" value="<?= htmlspecialchars($equipamento['tag']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Equipamento</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($equipamento['nome']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="setor_id" class="form-label">Setor</label>
                <select class="form-select" id="setor_id" name="setor_id" required>
                    <option value="" disabled>Selecione...</option>
                    <?php while($setor = $setores->fetch_assoc()): ?>
                        <option value="<?= $setor['id'] ?>" <?= ($setor['id'] == $equipamento['setor_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($setor['nome']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="equipamentos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
        <?php else: ?>
            <div class="alert alert-danger">Equipamento com ID <?= $equipamento_id ?> não encontrado.</div>
        <?php endif; ?>

    </div>

    <script>
    // Você pode adicionar aqui o JavaScript para enviar o formulário via AJAX
    // de forma similar ao que fizemos para salvar a O.S.
    // Por enquanto, ele fará um envio de formulário tradicional.

    document.getElementById('form-edit-equipamento').addEventListener('submit', function(event) {
        event.preventDefault(); // Impede o envio tradicional do formulário

        const form = this;
        const formData = new FormData(form);
        const messageDiv = document.getElementById('form-message');

        fetch('api/atualizar_equipamento.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            messageDiv.textContent = result.message;
            messageDiv.style.display = 'block';

            if (result.success) {
                messageDiv.className = 'alert alert-success';
                // Redireciona para a lista após 2 segundos
                setTimeout(() => {
                    window.location.href = 'equipamentos.php';
                }, 2000);
            } else {
                messageDiv.className = 'alert alert-danger';
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            messageDiv.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
            messageDiv.className = 'alert alert-danger';
            messageDiv.style.display = 'block';
        });
    });
    </script>
</body>
</html>

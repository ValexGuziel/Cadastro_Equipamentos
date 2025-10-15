<?php
require_once 'api/db_connect.php';

// Buscar equipamentos e setores
$equipamentos = $conn->query("SELECT eq.id, eq.nome, eq.tag, eq.setor_id, s.nome as setor_nome FROM equipamentos eq JOIN setores s ON eq.setor_id = s.id ORDER BY eq.tag");
$setores = $conn->query("SELECT * FROM setores ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-bell"></i> Nova Solicitação de Serviço</h2>
            <div>
                <a href="listar_solicitacoes.php" class="btn btn-secondary"><i class="bi bi-list-check"></i> Ver Solicitações</a>
                <a href="index.php" class="btn btn-info"><i class="bi bi-house"></i> Início</a>
            </div>
        </div>
        <hr>

        <!-- Mensagem de sucesso/erro -->
        <div id="form-message" class="alert" style="display: none;"></div>

        <form id="form-solicitacao" class="needs-validation" novalidate>
            <div class="row g-3">

                <!-- Equipamento -->
                <div class="col-md-12">
                    <label for="equipamento_id" class="form-label">Equipamento</label>
                    <select class="form-select" id="equipamento_id" name="equipamento_id" required>
                        <option value="" selected disabled>Selecione o equipamento...</option>
                        <?php
                        // Resetar o ponteiro para reutilizar a query
                        $equipamentos->data_seek(0);
                        while($equip = $equipamentos->fetch_assoc()): ?>
                            <option value="<?= $equip['id'] ?>" data-setor-id="<?= $equip['setor_id'] ?>"><?= htmlspecialchars($equip['tag']) ?> - <?= htmlspecialchars($equip['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, selecione um equipamento.</div>
                </div>

                <!-- Setor (preenchido automaticamente) -->
                <div class="col-md-6">
                    <label for="setor_id" class="form-label">Setor</label>
                    <select class="form-select" id="setor_id" name="setor_id" required readonly>
                        <option value="" selected disabled>Selecione um equipamento primeiro</option>
                         <?php while($setor = $setores->fetch_assoc()): ?>
                            <option value="<?= $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="invalid-feedback">O setor é obrigatório.</div>
                </div>

                <!-- Solicitante -->
                <div class="col-md-6">
                    <label for="solicitante" class="form-label">Seu Nome (Solicitante)</label>
                    <input type="text" class="form-control" id="solicitante" name="solicitante" required>
                    <div class="invalid-feedback">Por favor, informe seu nome.</div>
                </div>

                <!-- Descrição do Problema -->
                <div class="col-12">
                    <label for="descricao_problema" class="form-label">Descrição do Problema/Necessidade</label>
                    <textarea class="form-control" id="descricao_problema" name="descricao_problema" rows="5" required placeholder="Descreva com o máximo de detalhes o que está acontecendo com o equipamento."></textarea>
                    <div class="invalid-feedback">Por favor, descreva o problema.</div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-solicitacao');
        const equipamentoSelect = document.getElementById('equipamento_id');
        const setorSelect = document.getElementById('setor_id');
        const messageDiv = document.getElementById('form-message');

        // Atualiza o setor automaticamente quando um equipamento é selecionado
        equipamentoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const setorId = selectedOption.dataset.setorId;
            if (setorId) {
                setorSelect.value = setorId;
                setorSelect.dispatchEvent(new Event('change'));
            } else {
                setorSelect.value = '';
            }
        });

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            form.classList.add('was-validated');

            const formData = new FormData(form);

            fetch('api/add_solicitacao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                messageDiv.style.display = 'block';
                if (result.success) {
                    messageDiv.className = 'alert alert-success';
                    messageDiv.textContent = result.message + ' Redirecionando...';
                    form.reset();
                    form.classList.remove('was-validated');
                    setTimeout(() => {
                        window.location.href = 'listar_solicitacoes.php';
                    }, 2500);
                } else {
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = 'Erro: ' + result.message;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                messageDiv.className = 'alert alert-danger';
                messageDiv.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
                messageDiv.style.display = 'block';
            });
        });
    });
    </script>
</body>
</html>
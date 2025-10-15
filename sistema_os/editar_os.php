<?php
require_once 'api/db_connect.php';

$os_id = (int)($_GET['id'] ?? 0);
$os = null;

if ($os_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM ordens_servico WHERE id = ?");
    $stmt->bind_param("i", $os_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $os = $result->fetch_assoc();
    $stmt->close();
}

if (!$os) {
    die("Ordem de Serviço não encontrada!");
}

// Buscar opções para os selects
$setores = $conn->query("SELECT * FROM setores ORDER BY nome");
$tipos_manutencao = $conn->query("SELECT * FROM tipos_manutencao ORDER BY nome");
$equipamentos = $conn->query("SELECT id, nome, tag FROM equipamentos ORDER BY tag");
$tecnicos = $conn->query("SELECT id, nome FROM tecnicos WHERE status = 'Ativo' ORDER BY nome");

// Função para formatar a data do banco (Y-m-d H:i:s) para o formato do input datetime-local (Y-m-d\TH:i)
function formatarDataParaInput($data) {
    if (empty($data)) {
        return '';
    }
    try {
        return (new DateTime($data))->format('Y-m-d\TH:i');
    } catch (Exception $e) {
        return '';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ordem de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <h2><i class="bi bi-pencil-square"></i> Editar Ordem de Serviço</h2>
        <hr>

        <div id="form-message" class="alert" role="alert" style="display: none;"></div>

        <form id="form-edit-os">
            <input type="hidden" name="id" value="<?= $os['id'] ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="numero_os" class="form-label">Número da O.S.</label>
                    <input type="text" class="form-control" id="numero_os" name="numero_os" value="<?= htmlspecialchars($os['numero_os']) ?>" readonly>
                </div>

                <div class="col-md-8">
                    <label for="equipamento_id" class="form-label">Equipamento</label>
                    <select class="form-select" id="equipamento_id" name="equipamento_id" required>
                        <?php while($equip = $equipamentos->fetch_assoc()): ?>
                            <option value="<?= $equip['id'] ?>" <?= ($equip['id'] == $os['equipamento_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($equip['tag']) ?> - <?= htmlspecialchars($equip['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="setor_id" class="form-label">Setor</label>
                    <select class="form-select" id="setor_id" name="setor_id" required>
                        <?php while($setor = $setores->fetch_assoc()): ?>
                            <option value="<?= $setor['id'] ?>" <?= ($setor['id'] == $os['setor_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($setor['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="tipo_manutencao_id" class="form-label">Tipo de Manutenção</label>
                    <select class="form-select" id="tipo_manutencao_id" name="tipo_manutencao_id" required>
                        <?php while($tipo = $tipos_manutencao->fetch_assoc()): ?>
                            <option value="<?= $tipo['id'] ?>" <?= ($tipo['id'] == $os['tipo_manutencao_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="area_manutencao" class="form-label">Área de Manutenção</label>
                    <select class="form-select" id="area_manutencao" name="area_manutencao" required>
                        <option value="Mecânica" <?= $os['area_manutencao'] == 'Mecânica' ? 'selected' : '' ?>>Mecânica</option>
                        <option value="Elétrica" <?= $os['area_manutencao'] == 'Elétrica' ? 'selected' : '' ?>>Elétrica</option>
                        <option value="Hidráulica" <?= $os['area_manutencao'] == 'Hidráulica' ? 'selected' : '' ?>>Hidráulica</option>
                        <option value="Civil" <?= $os['area_manutencao'] == 'Civil' ? 'selected' : '' ?>>Civil</option>
                        <option value="TI" <?= $os['area_manutencao'] == 'TI' ? 'selected' : '' ?>>TI</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="prioridade" class="form-label">Prioridade</label>
                    <select class="form-select" id="prioridade" name="prioridade" required>
                        <option value="Baixa" <?= $os['prioridade'] == 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                        <option value="Média" <?= $os['prioridade'] == 'Média' ? 'selected' : '' ?>>Média</option>
                        <option value="Alta" <?= $os['prioridade'] == 'Alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="Urgente" <?= $os['prioridade'] == 'Urgente' ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="solicitante" class="form-label">Solicitante</label>
                    <input type="text" class="form-control" id="solicitante" name="solicitante" value="<?= htmlspecialchars($os['solicitante']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="tecnico_id" class="form-label">Técnico Responsável</label>
                    <select class="form-select" id="tecnico_id" name="tecnico_id">
                        <option value="">Não atribuído</option>
                        <?php while($tecnico = $tecnicos->fetch_assoc()): ?>
                            <option value="<?= $tecnico['id'] ?>" <?= ($tecnico['id'] == $os['tecnico_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tecnico['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="horas_estimadas" class="form-label">Horas Estimadas</label>
                    <input type="number" class="form-control" id="horas_estimadas" name="horas_estimadas" step="0.5" min="0" value="<?= htmlspecialchars($os['horas_estimadas'] ?? '1.0') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Aberta" <?= $os['status'] == 'Aberta' ? 'selected' : '' ?>>Aberta</option>                        
                        <option value="Concluída" <?= $os['status'] == 'Concluída' ? 'selected' : '' ?>>Concluída</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="data_inicial" class="form-label">Data Inicial</label>
                    <input type="datetime-local" class="form-control" id="data_inicial" name="data_inicial" value="<?= formatarDataParaInput($os['data_inicial']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="data_final" class="form-label">Data Final</label>
                    <input type="datetime-local" class="form-control" id="data_final" name="data_final" value="<?= formatarDataParaInput($os['data_final'] ?? '') ?>" <?= $os['status'] !== 'Concluída' ? 'disabled' : '' ?>>
                </div>

                <div class="col-12">
                    <label for="descricao_problema" class="form-label">Descrição do Problema</label>
                    <textarea class="form-control" id="descricao_problema" name="descricao_problema" rows="4" required><?= htmlspecialchars($os['descricao_problema']) ?></textarea>
                </div>

                <!-- NOVOS CAMPOS DE CUSTO -->
                <div class="col-md-6">
                    <label for="custo_pecas" class="form-label">Custo de Peças (R$)</label>
                    <input type="number" class="form-control" id="custo_pecas" name="custo_pecas" step="0.01" min="0" value="<?= htmlspecialchars($os['custo_pecas'] ?? '0.00') ?>">
                </div>

                <div class="col-md-6">
                    <label for="custo_mao_de_obra" class="form-label">Custo de Mão de Obra (R$)</label>
                    <input type="number" class="form-control" id="custo_mao_de_obra" name="custo_mao_de_obra" step="0.01" min="0" value="<?= htmlspecialchars($os['custo_mao_de_obra'] ?? '0.00') ?>">
                </div>

            </div>

            <div class="mt-4 text-end">
                <a href="ordens_servico.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const dataFinalInput = document.getElementById('data_final');

        statusSelect.addEventListener('change', function() {
            const isConcluida = this.value === 'Concluída';
            dataFinalInput.disabled = !isConcluida;
            dataFinalInput.required = isConcluida;
            if (!isConcluida) {
                dataFinalInput.value = '';
            }
        });

        document.getElementById('form-edit-os').addEventListener('submit', async function(event) {
            event.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const messageDiv = document.getElementById('form-message');

            try {
                const response = await fetch('api/atualizar_os.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                messageDiv.textContent = result.message;
                messageDiv.style.display = 'block';

                if (result.success) {
                    messageDiv.className = 'alert alert-success';
                    setTimeout(() => {
                        window.location.href = 'ordens_servico.php';
                    }, 2000);
                } else {
                    messageDiv.className = 'alert alert-danger';
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                messageDiv.textContent = 'Ocorreu um erro de comunicação.';
                messageDiv.className = 'alert alert-danger';
                messageDiv.style.display = 'block';
            }
        });
    });
    </script>
</body>
</html>
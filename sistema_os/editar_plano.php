<?php
require_once 'api/db_connect.php';

$plano_id = (int)($_GET['id'] ?? 0);
$plano = null;

if ($plano_id > 0) {
    $sql = "
        SELECT 
            pm.*,
            eq.nome as equipamento_nome,
            eq.tag as equipamento_tag,
            s.nome as setor_nome
        FROM planos_manutencao pm
        JOIN equipamentos eq ON pm.equipamento_id = eq.id
        JOIN setores s ON eq.setor_id = s.id
        WHERE pm.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $plano_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $plano = $result->fetch_assoc();
    $stmt->close();
}

if (!$plano) {
    die("Plano de Manutenção não encontrado!");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plano de Manutenção</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-pencil-square"></i> Editar Plano de Manutenção</h2>
            <a href="listar_planos.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar para a Lista</a>
        </div>
        <hr>

        <!-- Mensagem de sucesso/erro -->
        <div id="form-message" class="alert" style="display: none;"></div>

        <form id="form-edit-plano" novalidate>
            <input type="hidden" name="id" value="<?= $plano['id'] ?>">
            <div class="row g-3">

                <!-- Equipamento (somente leitura) -->
                <div class="col-md-12">
                    <label class="form-label">Equipamento</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($plano['equipamento_tag'] . ' - ' . $plano['equipamento_nome']) ?>" readonly>
                </div>

                <!-- Setor (somente leitura) -->
                <div class="col-md-6">
                    <label class="form-label">Setor</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($plano['setor_nome']) ?>" readonly>
                </div>

                <!-- Periodicidade -->
                <div class="col-md-6">
                    <label for="periodicidade" class="form-label">Periodicidade da Preventiva</label>
                    <select class="form-select" id="periodicidade" name="periodicidade" required>
                        <?php
                        $periodicidades = ['Semanal', 'Quinzenal', 'Mensal', 'Bimestral', 'Trimestral', 'Semestral', 'Anual'];
                        foreach ($periodicidades as $p) {
                            $selected = ($plano['periodicidade'] == $p) ? 'selected' : '';
                            echo "<option value=\"$p\" $selected>$p</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Data da Última Preventiva -->
                <div class="col-md-6">
                    <label for="data_ultima_preventiva" class="form-label">Data da Última Preventiva</label>
                    <input type="date" class="form-control" id="data_ultima_preventiva" name="data_ultima_preventiva" value="<?= htmlspecialchars($plano['data_ultima_preventiva']) ?>" required>
                </div>

                <!-- Data da Próxima Preventiva (calculado) -->
                <div class="col-md-6">
                    <label for="data_proxima_preventiva" class="form-label">Próxima Preventiva</label>
                    <input type="text" class="form-control" id="data_proxima_preventiva" name="data_proxima_preventiva" readonly>
                </div>

                <!-- Instruções -->
                <div class="col-12">
                    <label for="instrucoes" class="form-label">Instruções de Manutenção (O que verificar?)</label>
                    <textarea class="form-control" id="instrucoes" name="instrucoes" rows="5"><?= htmlspecialchars($plano['instrucoes']) ?></textarea>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="listar_planos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-edit-plano');
        const periodicidadeSelect = document.getElementById('periodicidade');
        const ultimaPrevInput = document.getElementById('data_ultima_preventiva');
        const proximaPrevInput = document.getElementById('data_proxima_preventiva');
        const messageDiv = document.getElementById('form-message');

        const periodicidadeDias = {
            'Semanal': 7, 'Quinzenal': 15, 'Mensal': 30, 'Bimestral': 60,
            'Trimestral': 90, 'Semestral': 180, 'Anual': 365
        };

        function calcularProximaData() {
            const ultimaData = ultimaPrevInput.value;
            const periodicidade = periodicidadeSelect.value;

            if (ultimaData && periodicidade && periodicidadeDias[periodicidade]) {
                const data = new Date(ultimaData + 'T00:00:00');
                data.setDate(data.getDate() + periodicidadeDias[periodicidade]);
                
                const dia = String(data.getDate()).padStart(2, '0');
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const ano = data.getFullYear();

                proximaPrevInput.value = `${dia}/${mes}/${ano}`;
            } else {
                proximaPrevInput.value = '';
            }
        }

        // Calcula a data inicial ao carregar a página
        calcularProximaData();

        periodicidadeSelect.addEventListener('change', calcularProximaData);
        ultimaPrevInput.addEventListener('change', calcularProximaData);

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(form);

            fetch('api/atualizar_plano.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                messageDiv.textContent = result.message;
                messageDiv.style.display = 'block';

                if (result.success) {
                    messageDiv.className = 'alert alert-success';
                    setTimeout(() => {
                        window.location.href = 'listar_planos.php';
                    }, 2000);
                } else {
                    messageDiv.className = 'alert alert-danger';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                messageDiv.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
                messageDiv.className = 'alert alert-danger';
                messageDiv.style.display = 'block';
            });
        });
    });
    </script>
</body>
</html>
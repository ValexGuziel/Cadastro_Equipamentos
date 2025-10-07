<?php
$page_title = 'Novo Plano de Manutenção';
require_once 'api/db_connect.php';


// Buscar equipamentos que ainda NÃO possuem um plano de manutenção
$equipamentos = $conn->query("
    SELECT eq.id, eq.nome, eq.tag, s.nome as setor_nome 
    FROM equipamentos eq 
    JOIN setores s ON eq.setor_id = s.id 
    LEFT JOIN planos_manutencao pm ON eq.id = pm.equipamento_id
    WHERE pm.id IS NULL
    ORDER BY eq.tag
");

require_once __DIR__ . '/header.php';
?>
    <main class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-calendar-check"></i> Novo Plano de Manutenção</h1>
             <a href="listar_planos.php" class="btn btn-secondary"><i class="bi bi-list-ul"></i> Ver Lista de Planos</a>
        </div>
        <hr>

        <!-- Mensagem de erro -->
        <div id="error-message" class="alert alert-danger" style="display: none;"></div>

        <!-- Mensagem de sucesso com botão de impressão -->
        <div id="success-message" class="alert alert-success text-center" style="display: none;">
            <h4>Plano de Manutenção salvo com sucesso!</h4>
            <a href="#" id="btn-imprimir-plano" target="_blank" class="btn btn-info"><i class="bi bi-printer"></i> Imprimir Plano</a>
            <button type="button" id="btn-novo-plano" class="btn btn-secondary">Cadastrar Novo Plano</button>
        </div>

        <form id="form-plano" class="needs-validation" novalidate autocomplete="off">
            <div class="row g-3">

                <!-- Equipamento -->
                <div class="col-md-12">
                    <label for="equipamento_id" class="form-label">Equipamento</label>
                    <select class="form-select" id="equipamento_id" name="equipamento_id" required>
                        <option value="" selected disabled>Selecione o equipamento...</option>
                        <?php while($equip = $equipamentos->fetch_assoc()): ?>
                            <option value="<?= $equip['id'] ?>" data-setor="<?= htmlspecialchars($equip['setor_nome']) ?>">
                                <?= htmlspecialchars($equip['tag']) ?> - <?= htmlspecialchars($equip['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, selecione um equipamento.</div>
                </div>

                <!-- Setor (preenchido automaticamente) -->
                <div class="col-md-6">
                    <label for="setor" class="form-label">Setor</label>
                    <input type="text" class="form-control" id="setor" name="setor" readonly>
                </div>

                <!-- Periodicidade -->
                <div class="col-md-6">
                    <label for="periodicidade" class="form-label">Periodicidade da Preventiva</label>
                    <select class="form-select" id="periodicidade" name="periodicidade" required>
                        <option value="" selected disabled>Selecione...</option>
                        <option value="Semanal">Semanal (7 dias)</option>
                        <option value="Quinzenal">Quinzenal (15 dias)</option>
                        <option value="Mensal">Mensal (30 dias)</option>
                        <option value="Bimestral">Bimestral (60 dias)</option>
                        <option value="Trimestral">Trimestral (90 dias)</option>
                        <option value="Semestral">Semestral (180 dias)</option>
                        <option value="Anual">Anual (365 dias)</option>
                    </select>
                    <div class="invalid-feedback">Por favor, selecione a periodicidade.</div>
                </div>

                <!-- Data da Última Preventiva -->
                <div class="col-md-6">
                    <label for="data_ultima_preventiva" class="form-label">Data da Última Preventiva</label>
                    <input type="date" class="form-control" id="data_ultima_preventiva" name="data_ultima_preventiva" required>
                    <div class="invalid-feedback">Por favor, informe a data da última manutenção.</div>
                </div>

                <!-- Data da Próxima Preventiva (calculado) -->
                <div class="col-md-6">
                    <label for="data_proxima_preventiva" class="form-label">Próxima Preventiva</label>
                    <input type="text" class="form-control" id="data_proxima_preventiva" name="data_proxima_preventiva" readonly>
                </div>

                <!-- Instruções -->
                <div class="col-12">
                    <label for="instrucoes" class="form-label">Instruções de Manutenção (O que verificar?)</label>
                    <textarea class="form-control" id="instrucoes" name="instrucoes" rows="5" placeholder="Ex: Verificar nível de óleo, limpar filtros, reapertar parafusos..."></textarea>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">Salvar Plano de Manutenção</button>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-plano');
        const equipamentoSelect = document.getElementById('equipamento_id');
        const setorInput = document.getElementById('setor');
        const periodicidadeSelect = document.getElementById('periodicidade');
        const ultimaPrevInput = document.getElementById('data_ultima_preventiva');
        const proximaPrevInput = document.getElementById('data_proxima_preventiva');
        const successDiv = document.getElementById('success-message');
        const errorDiv = document.getElementById('error-message');

        const periodicidadeDias = {
            'Semanal': 7, 'Quinzenal': 15, 'Mensal': 30, 'Bimestral': 60,
            'Trimestral': 90, 'Semestral': 180, 'Anual': 365
        };

        function calcularProximaData() {
            const ultimaData = ultimaPrevInput.value;
            const periodicidade = periodicidadeSelect.value;

            if (ultimaData && periodicidade && periodicidadeDias[periodicidade]) {
                const data = new Date(ultimaData + 'T00:00:00'); // Adiciona T00:00:00 para evitar problemas de fuso
                data.setDate(data.getDate() + periodicidadeDias[periodicidade]);
                
                const dia = String(data.getDate()).padStart(2, '0');
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const ano = data.getFullYear();

                proximaPrevInput.value = `${dia}/${mes}/${ano}`;
            } else {
                proximaPrevInput.value = '';
            }
        }

        equipamentoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            setorInput.value = selectedOption.dataset.setor || '';
        });

        periodicidadeSelect.addEventListener('change', calcularProximaData);
        ultimaPrevInput.addEventListener('change', calcularProximaData);

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const formData = new FormData(form);

            fetch('api/add_plano.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    form.style.display = 'none'; // Esconde o formulário
                    successDiv.style.display = 'block'; // Mostra a mensagem de sucesso
                    document.getElementById('btn-imprimir-plano').href = `imprimir_plano.php?id=${result.plano_id}`;
                    
                    // Remove o equipamento da lista para não ser selecionado de novo
                    document.querySelector(`#equipamento_id option[value='${result.equipamento_id}']`).remove();

                    document.getElementById('btn-novo-plano').addEventListener('click', () => {
                        form.reset();
                        form.classList.remove('was-validated');
                        successDiv.style.display = 'none';
                        form.style.display = 'block';
                    });
                } else {
                    errorDiv.textContent = result.message;
                    errorDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                errorDiv.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
                errorDiv.style.display = 'block';
            });
        });
    });
    </script>
</body>
</html>
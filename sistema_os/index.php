<?php
// index.php
$page_title = 'Nova Ordem de Serviço';
require_once 'api/db_connect.php';

// Função para gerar o número da O.S.
function gerarNumeroOS($conn) {
    // 1. Pega o último ID inserido
    $result = $conn->query("SELECT MAX(id) AS last_id FROM ordens_servico");
    $row = $result->fetch_assoc();
    $next_id = ($row['last_id'] ?? 0) + 1;

    // 2. Formata o número
    $numero_formatado = str_pad($next_id, 5, '0', STR_PAD_LEFT);
    $data_atual = date('Y-m-d'); // Formato aaaa-mm-dd

    return "{$numero_formatado}-{$data_atual}";
}

$novo_numero_os = gerarNumeroOS($conn);

// Buscar opções para os selects
$setores = $conn->query("SELECT * FROM setores ORDER BY nome");
$tipos_manutencao = $conn->query("SELECT * FROM tipos_manutencao ORDER BY nome");
$equipamentos = $conn->query("SELECT eq.id, eq.nome, eq.tag, eq.setor_id, s.nome as setor_nome FROM equipamentos eq JOIN setores s ON eq.setor_id = s.id ORDER BY eq.tag");

require_once __DIR__ . '/header.php';
?>
    <main class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-journal-plus"></i> Nova Ordem de Serviço</h1>
        </div>

        <hr>

        <!-- Mensagem de sucesso -->
        <div id="success-message" class="alert alert-success text-center" style="display: none;">
            <h4>Ordem de Serviço salva com sucesso!</h4>
            <p>Número da O.S.: <strong id="success-os-number"></strong></p>
            <a href="#" id="btn-imprimir-os" target="_blank" class="btn btn-info">Imprimir O.S.</a>
            <button type="button" id="btn-nova-os" class="btn btn-secondary">Criar Nova O.S.</button>
        </div>

        <form id="form-os" class="needs-validation" novalidate>
            <div class="row g-3">
                <!-- Número da O.S. -->
                <div class="col-md-4">
                    <label for="numero_os" class="form-label">Número da O.S.</label>
                    <input type="text" class="form-control" id="numero_os" name="numero_os" value="<?= htmlspecialchars($novo_numero_os) ?>" readonly>
                </div>

                <!-- Equipamento -->
                <div class="col-md-8">
                    <label for="equipamento_id" class="form-label">Equipamento</label>
                    <div class="input-group">
                        <select class="form-select" id="equipamento_id" name="equipamento_id" required>
                            <option value="" selected disabled>Selecione...</option>
                            <?php while($equip = $equipamentos->fetch_assoc()): ?>
                                <option value="<?= $equip['id'] ?>" data-setor-id="<?= $equip['setor_id'] ?>"><?= htmlspecialchars($equip['tag']) ?> - <?= htmlspecialchars($equip['nome']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalEquipamento">+</button>
                    </div>
                </div>

                <!-- Setor -->
                <div class="col-md-6">
                    <label for="setor_id" class="form-label">Setor</label>
                    <div class="input-group">
                        <select class="form-select" id="setor_id" name="setor_id" required>
                            <option value="" selected disabled>Selecione...</option>
                            <?php while($setor = $setores->fetch_assoc()): ?>
                                <option value="<?= $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalSetor">+</button>
                    </div>
                </div>

                <!-- Tipo de Manutenção -->
                <div class="col-md-6">
                    <label for="tipo_manutencao_id" class="form-label">Tipo de Manutenção</label>
                    <div class="input-group">
                        <select class="form-select" id="tipo_manutencao_id" name="tipo_manutencao_id" required>
                            <option value="" selected disabled>Selecione...</option>
                             <?php while($tipo = $tipos_manutencao->fetch_assoc()): ?>
                                <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nome']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalTipoManutencao">+</button>
                    </div>
                </div>

                <!-- Área de Manutenção -->
                <div class="col-md-4">
                    <label for="area_manutencao" class="form-label">Área de Manutenção</label>
                    <select class="form-select" id="area_manutencao" name="area_manutencao" required>
                        <option value="" selected disabled>Selecione...</option>
                        <option value="Mecânica">Mecânica</option>
                        <option value="Elétrica">Elétrica</option>
                        <option value="Hidráulica">Hidráulica</option>
                        <option value="Civil">Civil</option>
                        <option value="TI">TI</option>
                    </select>
                </div>

                <!-- Prioridade -->
                <div class="col-md-4">
                    <label for="prioridade" class="form-label">Prioridade</label>
                    <select class="form-select" id="prioridade" name="prioridade" required>
                        <option value="" selected disabled>Selecione...</option>
                        <option value="Baixa">Baixa</option>
                        <option value="Média">Média</option>
                        <option value="Alta">Alta</option>
                        <option value="Urgente">Urgente</option>
                    </select>
                </div>

                <!-- Solicitante -->
                <div class="col-md-4">
                    <label for="solicitante" class="form-label">Solicitante</label>
                    <input type="text" class="form-control" id="solicitante" name="solicitante" required>
                </div>

                <!-- Horas Estimadas -->
                <div class="col-md-4">
                    <label for="horas_estimadas" class="form-label">Horas Estimadas</label>
                    <input type="number" class="form-control" id="horas_estimadas" name="horas_estimadas" step="0.5" min="0.5" value="1.0" required>
                </div>

                <!-- Status -->
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Aberta" selected>Aberta</option>
                        <option value="Em Andamento">Em Andamento</option>
                        <option value="Aguardando Peça">Aguardando Peça</option>
                        <option value="Concluída">Concluída</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>

                <!-- Data Inicial -->
                <div class="col-md-4">
                    <label for="data_inicial" class="form-label">Data Inicial</label>
                    <input type="datetime-local" class="form-control" id="data_inicial" name="data_inicial" required>
                </div>

                <!-- Data Final -->
                <div class="col-md-4">
                    <label for="data_final" class="form-label">Data Final</label>
                    <input type="datetime-local" class="form-control" id="data_final" name="data_final" disabled>
                </div>

                <!-- Descrição do Problema -->
                <div class="col-12">
                    <label for="descricao_problema" class="form-label">Descrição do Problema</label>
                    <textarea class="form-control" id="descricao_problema" name="descricao_problema" rows="4" required></textarea>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">Salvar Ordem de Serviço</button>
            </div>
        </form>
    </main>

    <!-- Modal para Adicionar Equipamento -->
    <div class="modal fade" id="modalEquipamento" tabindex="-1" aria-labelledby="modalEquipamentoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEquipamentoLabel">Adicionar Novo Equipamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Placeholder para mensagens -->
                    <div id="equipamento-modal-message" class="alert" role="alert" style="display: none;"></div>
                    <form id="form-add-equipamento">
                        <div class="mb-3">
                            <label for="novo_equip_tag" class="form-label">Tag</label>
                            <input type="text" class="form-control" id="novo_equip_tag" name="tag" required>
                        </div>
                        <div class="mb-3">
                            <label for="novo_equip_nome" class="form-label">Nome do Equipamento</label>
                            <input type="text" class="form-control" id="novo_equip_nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="novo_equip_setor" class="form-label">Setor</label>
                            <select class="form-select" id="novo_equip_setor" name="setor_id" required>
                                <option value="" selected disabled>Selecione o setor...</option>
                                <!-- As opções de setor serão carregadas aqui via JS -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btn-salvar-equipamento">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar Setor -->
    <div class="modal fade" id="modalSetor" tabindex="-1" aria-labelledby="modalSetorLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSetorLabel">Adicionar Novo Setor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-add-setor">
                        <div class="mb-3">
                            <label for="novo_setor" class="form-label">Nome do Setor</label>
                            <input type="text" class="form-control" id="novo_setor" name="nome" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btn-salvar-setor">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar Tipo de Manutenção -->
    <div class="modal fade" id="modalTipoManutencao" tabindex="-1" aria-labelledby="modalTipoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTipoLabel">Adicionar Novo Tipo de Manutenção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-add-tipo">
                        <div class="mb-3">
                            <label for="novo_tipo" class="form-label">Nome do Tipo</label>
                            <input type="text" class="form-control" id="novo_tipo" name="nome" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btn-salvar-tipo">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nosso JavaScript -->
    <script src="script.js"></script>
</body>
</html>

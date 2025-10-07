<?php 
$page_title = 'Planos de Manutenção';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <style>
        th[data-column] { cursor: pointer; user-select: none; position: relative; }
        th[data-column]::after {
            content: '';
            position: absolute;
            right: 10px;
            top: 50%;
            border: solid var(--cor-texto-claro);
            border-width: 0 2px 2px 0;
            display: inline-block;
            padding: 3px;
            opacity: 0.2;
            transition: all 0.2s;
        }
        th[data-column].sort-asc::after { opacity: 1; transform: translateY(-70%) rotate(-135deg); }
        th[data-column].sort-desc::after { opacity: 1; transform: translateY(-30%) rotate(45deg); }

        @media print {
            .no-print {
                display: none !important;
            }
            body, .table {
                background-color: #fff !important;
                color: #000 !important;
            }
            .container-xl { margin-top: 0 !important; }

            /* Estilos para impressão do modal de histórico */
            body.print-history-active > *:not(.print-container) {
                display: none !important;
            }
            body.print-history-active .print-container {
                display: block !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
            .print-container h5, .print-container h6 {
                color: #000 !important;
            }
            .print-container .table {
                font-size: 12px;
            }
            .print-container .table a {
                text-decoration: none;
                color: #000;
            }
        }
    </style>
    <?php require_once __DIR__ . '/header.php'; ?>
</head>
<body>
    <main class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h1><i class="bi bi-calendar-week"></i> Planos de Manutenção</h1>
            <a href="planos_manutencao.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Novo Plano</a>
        </div>

        <div class="mb-3 no-print">
            <input type="text" id="campo-busca" class="form-control" placeholder="Buscar por equipamento, setor, periodicidade...">
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead id="tabela-cabecalho">
                    <tr>
                        <th data-column="equipamento_nome">Equipamento</th>
                        <th data-column="setor_nome">Setor</th>
                        <th data-column="periodicidade">Periodicidade</th>
                        <th data-column="data_ultima_preventiva">Última Preventiva</th>
                        <th data-column="data_proxima_preventiva">Próxima Preventiva</th>
                        <th class="no-print">Ações</th>
                    </tr>
                </thead>
                <tbody id="corpo-tabela-planos">
                    <tr>
                        <td colspan="6" class="text-center">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Histórico de Preventivas -->
    <div class="modal fade" id="modalHistorico" tabindex="-1" aria-labelledby="modalHistoricoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHistoricoLabel">Histórico de Manutenções Preventivas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-historico-body">
                    <h6 id="historico-equipamento-titulo"></h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Data de Realização</th>
                                <th>Nº da O.S.</th>
                                <th>Observações</th>
                            </tr>
                        </thead>
                        <tbody id="corpo-tabela-historico">
                            <!-- O histórico será inserido aqui -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer no-print">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-info" onclick="imprimirHistorico()"><i class="bi bi-printer"></i> Imprimir Histórico</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes do Plano -->
    <div class="modal fade" id="modalDetalhesPlano" tabindex="-1" aria-labelledby="modalDetalhesPlanoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalhesPlanoLabel">Detalhes do Plano de Manutenção</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-primary">Equipamento</h6>
                    <p id="detalhes-equipamento"></p>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Setor</h6>
                            <p id="detalhes-setor"></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Periodicidade</h6>
                            <p id="detalhes-periodicidade"></p>
                        </div>
                    </div>

                    <h6 class="text-primary mt-3">Instruções de Manutenção (Checklist)</h6>
                    <div id="detalhes-instrucoes" style="white-space: pre-wrap; background-color: #495057; padding: 15px; border-radius: 5px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('corpo-tabela-planos');
        const campoBusca = document.getElementById('campo-busca');
        const thead = document.getElementById('tabela-cabecalho');

        let todosPlanos = [];
        let dadosFiltrados = [];
        let sortColumn = 'data_proxima_preventiva';
        let sortDirection = 'asc';

        function formatarData(data) {
            if (!data) return 'N/A';
            try {
                const [ano, mes, dia] = data.split('-');
                return `${dia}/${mes}/${ano}`;
            } catch (e) {
                return 'Data inválida';
            }
        }

        function renderizarTabela() {
            tbody.innerHTML = '';

            if (dadosFiltrados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center">Nenhum plano de manutenção encontrado.</td></tr>`;
                return;
            }

            dadosFiltrados.forEach(plano => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${plano.equipamento_tag} - ${plano.equipamento_nome}</td>
                    <td>${plano.setor_nome}</td>
                    <td>${plano.periodicidade}</td>
                    <td>${formatarData(plano.data_ultima_preventiva)}</td>
                    <td class="fw-bold ${new Date(plano.data_proxima_preventiva) <= new Date() ? 'text-danger' : ''}">${formatarData(plano.data_proxima_preventiva)}</td>
                    <td class="no-print">
                        <button class="btn btn-sm btn-primary btn-gerar-os" title="Gerar O.S. Preventiva" data-plano-id="${plano.id}"><i class="bi bi-journal-plus"></i></button>
                        <button class="btn btn-sm btn-secondary btn-historico" title="Ver Histórico"><i class="bi bi-clock-history"></i></button>
                        <button class="btn btn-sm btn-success btn-detalhes" title="Ver Detalhes"><i class="bi bi-eye"></i></button>
                        <a href="imprimir_plano.php?id=${plano.id}" target="_blank" class="btn btn-sm btn-info" title="Imprimir"><i class="bi bi-printer"></i></a>
                        <a href="editar_plano.php?id=${plano.id}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                    </td>
                `;
                tbody.appendChild(tr);

                // Adiciona o evento de clique para o botão de detalhes da linha atual
                tr.querySelector('.btn-detalhes').addEventListener('click', () => {
                    mostrarDetalhes(plano);
                });

                // Adiciona o evento de clique para o botão de histórico
                tr.querySelector('.btn-historico').addEventListener('click', () => {
                    mostrarHistorico(plano);
                });

                // Adiciona o evento de clique para o botão de gerar O.S.
                tr.querySelector('.btn-gerar-os').addEventListener('click', (e) => {
                    gerarOSPreventiva(e.currentTarget.dataset.planoId, plano);
                });
            });
        }

        function filtrarEAtualizar() {
            const termoBusca = campoBusca.value.toLowerCase();
            dadosFiltrados = todosPlanos.filter(plano => {
                return (
                    plano.equipamento_nome.toLowerCase().includes(termoBusca) ||
                    plano.equipamento_tag.toLowerCase().includes(termoBusca) ||
                    plano.setor_nome.toLowerCase().includes(termoBusca) ||
                    plano.periodicidade.toLowerCase().includes(termoBusca)
                );
            });
            ordenarDados(sortColumn, false);
        }

        function ordenarDados(coluna, inverterDirecao = true) {
            if (inverterDirecao) {
                sortDirection = (sortColumn === coluna && sortDirection === 'asc') ? 'desc' : 'asc';
            }
            sortColumn = coluna;

            dadosFiltrados.sort((a, b) => {
                const valA = a[sortColumn];
                const valB = b[sortColumn];
                const comparison = String(valA).localeCompare(String(valB), undefined, { numeric: true });
                return sortDirection === 'asc' ? comparison : -comparison;
            });

            thead.querySelectorAll('th[data-column]').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
            thead.querySelector(`th[data-column="${coluna}"]`).classList.add(`sort-${sortDirection}`);

            renderizarTabela();
        }

        async function carregarDados() {
            try {
                const response = await fetch('api/listar_planos.php');
                const result = await response.json();
                if (result.success) {
                    todosPlanos = result.data;
                    filtrarEAtualizar();
                } else {
                    throw new Error(result.message || 'Erro ao carregar dados.');
                }
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar os planos de manutenção.</td></tr>`;
                console.error(error);
            }
        }

        function mostrarDetalhes(plano) {
            document.getElementById('detalhes-equipamento').textContent = `${plano.equipamento_tag} - ${plano.equipamento_nome}`;
            document.getElementById('detalhes-setor').textContent = plano.setor_nome;
            document.getElementById('detalhes-periodicidade').textContent = plano.periodicidade;
            
            const instrucoesDiv = document.getElementById('detalhes-instrucoes');
            // Usamos textContent para evitar injeção de HTML
            instrucoesDiv.textContent = plano.instrucoes || 'Nenhuma instrução cadastrada.';

            // Exibe o modal
            const modal = new bootstrap.Modal(document.getElementById('modalDetalhesPlano'));
            modal.show();
        }

        async function mostrarHistorico(plano) {
            const modal = new bootstrap.Modal(document.getElementById('modalHistorico'));
            const corpoTabela = document.getElementById('corpo-tabela-historico');
            
            document.getElementById('historico-equipamento-titulo').textContent = `Equipamento: ${plano.equipamento_tag} - ${plano.equipamento_nome}`;
            corpoTabela.innerHTML = '<tr><td colspan="3" class="text-center">Carregando histórico...</td></tr>';
            modal.show();

            try {
                const response = await fetch(`api/listar_historico.php?plano_id=${plano.id}`);
                const result = await response.json();

                corpoTabela.innerHTML = '';
                if (result.success && result.data.length > 0) {
                    result.data.forEach(item => {
                        const tr = document.createElement('tr');
                        const linkOS = item.numero_os ? `<a href="editar_os.php?id=${item.ordem_servico_id}" target="_blank">${item.numero_os}</a>` : 'N/A';
                        tr.innerHTML = `
                            <td>${formatarData(item.data_realizacao)}</td>
                            <td>${item.numero_os || 'N/A'}</td>
                            <td>${item.observacoes || ''}</td>
                        `;
                        corpoTabela.appendChild(tr);
                    });
                } else {
                    corpoTabela.innerHTML = '<tr><td colspan="3" class="text-center">Nenhum histórico encontrado para este plano.</td></tr>';
                }

            } catch (error) {
                console.error('Erro ao buscar histórico:', error);
                corpoTabela.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Falha ao carregar o histórico.</td></tr>';
            }
        }

        function imprimirHistorico() {
            const modalBody = document.getElementById('modal-historico-body');
            // Cria um container temporário para impressão
            const printContainer = document.createElement('div');
            printContainer.classList.add('print-container');
            printContainer.innerHTML = modalBody.innerHTML;
            document.body.appendChild(printContainer);

            document.body.classList.add('print-history-active');
            window.print();
            document.body.classList.remove('print-history-active');
            document.body.removeChild(printContainer);
        }

        async function gerarOSPreventiva(planoId, plano) {
            if (!confirm(`Deseja realmente gerar uma Ordem de Serviço Preventiva para o equipamento "${plano.equipamento_tag} - ${plano.equipamento_nome}"?`)) {
                return;
            }

            try {
                const response = await fetch('api/gerar_os_plano.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `plano_id=${planoId}`
                });

                const result = await response.json();

                if (result.success) {
                    alert(`O.S. Preventiva Nº ${result.numero_os} gerada com sucesso! A página será recarregada.`);
                    // Recarrega os dados para refletir a nova data da próxima preventiva
                    carregarDados(); 
                } else {
                    throw new Error(result.message || 'Falha ao gerar a O.S.');
                }

            } catch (error) {
                console.error('Erro ao gerar O.S. Preventiva:', error);
                alert(`Erro: ${error.message}`);
            }
        }


        campoBusca.addEventListener('keyup', filtrarEAtualizar);
        thead.addEventListener('click', (e) => {
            const th = e.target.closest('th');
            if (th && th.dataset.column) {
                ordenarDados(th.dataset.column);
            }
        });

        carregarDados();
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
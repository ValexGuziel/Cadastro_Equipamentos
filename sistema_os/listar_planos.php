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

        <nav id="paginacao-container" class="d-flex justify-content-center no-print">
            <!-- Botões de paginação serão inseridos aqui -->
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
        let paginaAtual = 1;
        const linhasPorPagina = 15;
        let sortColumn = 'data_proxima_preventiva';
        let sortDirection = 'asc';

        function formatarData(data) {
            if (!data) return 'N/A';
            try {
                const dt = new Date(data);
                // Verifica se a data é válida
                if (isNaN(dt.getTime())) {
                    return 'Data inválida';
                }
                const dia = String(dt.getDate()).padStart(2, '0');
                const mes = String(dt.getMonth() + 1).padStart(2, '0'); // Mês é 0-indexado
                const ano = dt.getFullYear();
                const horas = String(dt.getHours()).padStart(2, '0');
                const minutos = String(dt.getMinutes()).padStart(2, '0');
                return `${dia}/${mes}/${ano} ${horas}:${minutos}`;
            } catch (e) {
                return 'Data inválida';
            }
        }

        function renderizarTabela(pagina) {
            tbody.innerHTML = '';
            paginaAtual = pagina;

            const inicio = (pagina - 1) * linhasPorPagina;
            const fim = inicio + linhasPorPagina;
            const itensPaginados = dadosFiltrados.slice(inicio, fim);


            if (itensPaginados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center">Nenhum plano de manutenção encontrado.</td></tr>`;
                return;
            }

            itensPaginados.forEach(plano => {
                const tr = document.createElement('tr');
                tr.dataset.planoId = plano.id; // Adiciona o ID do plano à linha
                tr.innerHTML = `
                    <td>${plano.equipamento_tag} - ${plano.equipamento_nome}</td>
                    <td>${plano.setor_nome}</td>
                    <td>${plano.periodicidade}</td>
                    <td>${formatarData(plano.data_ultima_preventiva)}</td>
                    <td class="fw-bold ${new Date(plano.data_proxima_preventiva) <= new Date() ? 'text-danger' : ''}">${formatarData(plano.data_proxima_preventiva)}</td>
                    <td class="no-print">
                        <button class="btn btn-sm btn-secondary btn-historico" title="Ver Histórico"><i class="bi bi-clock-history"></i></button>
                        <button class="btn btn-sm btn-success btn-detalhes" title="Ver Detalhes"><i class="bi bi-eye"></i></button>
                        <a href="imprimir_plano.php?id=${plano.id}" target="_blank" class="btn btn-sm btn-info" title="Imprimir"><i class="bi bi-printer"></i></a>
                        <a href="editar_plano.php?id=${plano.id}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-danger btn-excluir" title="Excluir Plano" data-plano-id="${plano.id}"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
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

        function renderizarPaginacao() {
            const paginacaoContainer = document.getElementById('paginacao-container');
            paginacaoContainer.innerHTML = '';
            const totalPaginas = Math.ceil(dadosFiltrados.length / linhasPorPagina);

            if (totalPaginas <= 1) return;

            const ul = document.createElement('ul');
            ul.className = 'pagination';

            for (let i = 1; i <= totalPaginas; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === paginaAtual ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                li.addEventListener('click', function (e) {
                    e.preventDefault();
                    renderizarTabela(i);
                    renderizarPaginacao();
                });
                ul.appendChild(li);
            }
            paginacaoContainer.appendChild(ul);
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

            renderizarTabela(1);
            renderizarPaginacao();
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

        async function excluirPlano(planoId, plano) {
            const confirmacao = `Tem certeza que deseja excluir o plano de manutenção para o equipamento "${plano.equipamento_tag} - ${plano.equipamento_nome}"? Esta ação não pode ser desfeita.`;
            if (!confirm(confirmacao)) {
                return;
            }

            try {
                const response = await fetch('api/excluir_plano.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: planoId })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    // Remove o plano da lista de dados e atualiza a tabela
                    todosPlanos = todosPlanos.filter(p => p.id != planoId);
                    filtrarEAtualizar();
                } else {
                    throw new Error(result.message || 'Falha ao excluir o plano.');
                }
            } catch (error) {
                console.error('Erro ao excluir plano:', error);
                alert(`Erro: ${error.message}`);
            }
        }

        // Delegação de eventos para os botões de ação na tabela
        tbody.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            if (!button) return;

            const tr = button.closest('tr');
            const planoId = tr.dataset.planoId;
            const plano = dadosFiltrados.find(p => p.id == planoId);

            if (button.classList.contains('btn-detalhes')) mostrarDetalhes(plano);
            if (button.classList.contains('btn-historico')) mostrarHistorico(plano);
            if (button.classList.contains('btn-excluir')) excluirPlano(planoId, plano);
        });

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
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Solicitações de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge { font-size: 0.9em; }
        .status-Pendente { background-color: #ffc107 !important; color: #000 !important; }
        .status-Aprovada { background-color: #198754 !important; }
        .status-Rejeitada { background-color: #dc3545 !important; }
    </style>
</head>
<body>
    <div class="container-xl mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-list-check"></i> Solicitações de Serviço</h2>
            <div>
                <a href="solicitacao_servico.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nova Solicitação</a>
                
            </div>
        </div>

        <div class="mb-3">
            <input type="text" id="campo-busca" class="form-control" placeholder="Buscar por equipamento, solicitante, status...">
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Equipamento</th>
                        <th>Setor</th>
                        <th>Solicitante</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpo-tabela-solicitacoes">
                    <tr><td colspan="7" class="text-center">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <nav id="paginacao-container" class="d-flex justify-content-center mt-4">
        <!-- Botões de paginação serão inseridos aqui -->
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('corpo-tabela-solicitacoes');
        const campoBusca = document.getElementById('campo-busca');
        let todasSolicitacoes = [];
        let dadosFiltrados = [];
        let paginaAtual = 1;
        const linhasPorPagina = 15;

        function formatarData(data) {
            if (!data) return 'N/A';
            const dt = new Date(data);
            return dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }

        function renderizarTabela(pagina) {
            tbody.innerHTML = '';
            paginaAtual = pagina;

            const inicio = (pagina - 1) * linhasPorPagina;
            const fim = inicio + linhasPorPagina;
            const itensPaginados = dadosFiltrados.slice(inicio, fim);

            if (itensPaginados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center">Nenhuma solicitação encontrada.</td></tr>`;
                return;
            }

            itensPaginados.forEach(item => {
                const tr = document.createElement('tr');
                
                // Botões de ação
                let acoes = `<a href="imprimir_solicitacao.php?id=${item.id}" target="_blank" class="btn btn-sm btn-info" title="Imprimir Solicitação"><i class="bi bi-printer"></i></a>`;

                if (item.status === 'Pendente') {
                    acoes += ` <button class="btn btn-sm btn-success btn-aprovar" data-id="${item.id}" title="Aprovar Solicitação"><i class="bi bi-check-lg"></i></button>`;
                }

                // Permite excluir se estiver Pendente ou Aprovada
                if (item.status === 'Pendente' || item.status === 'Aprovada') {
                     acoes += ` <button class="btn btn-sm btn-danger btn-excluir" data-id="${item.id}" data-status="${item.status}" title="Excluir Solicitação"><i class="bi bi-trash"></i></button>`;
                } else if (item.status === 'Aprovada' && item.ordem_servico_id) {
                    // O botão de excluir já cobre o caso 'Aprovada', mas mantemos o de ver a OS.
                    // A lógica acima já adicionou o botão de excluir, então aqui só adicionamos o de ver a OS.
                }

                tr.innerHTML = `
                    <td>${formatarData(item.data_solicitacao)}</td>
                    <td>${item.equipamento_tag} - ${item.equipamento_nome}</td>
                    <td>${item.setor_nome}</td>
                    <td>${item.solicitante}</td>
                    <td title="${item.descricao_problema}">${item.descricao_problema.substring(0, 40)}...</td>
                    <td><span class="badge status-badge status-${item.status}">${item.status}</span></td>
                    <td>${acoes}</td>
                `;
                tbody.appendChild(tr);
            });
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

        function filtrarEAtualizar() {
            const termoBusca = campoBusca.value.toLowerCase();
            dadosFiltrados = todasSolicitacoes.filter(item => {
                return (
                    item.equipamento_nome.toLowerCase().includes(termoBusca) ||
                    item.equipamento_tag.toLowerCase().includes(termoBusca) ||
                    item.setor_nome.toLowerCase().includes(termoBusca) ||
                    item.solicitante.toLowerCase().includes(termoBusca) ||
                    item.status.toLowerCase().includes(termoBusca)
                );
            });
            renderizarTabela(1);
            renderizarPaginacao();
        }

        async function carregarDados() {
            try {
                const response = await fetch('api/listar_solicitacoes.php');
                const result = await response.json();
                if (result.success) {
                    todasSolicitacoes = result.data;
                    filtrarEAtualizar();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Falha ao carregar dados. ${error.message}</td></tr>`;
            }
        }

        campoBusca.addEventListener('keyup', filtrarEAtualizar);

        // Delegação de eventos para o botão de rejeitar/excluir
        tbody.addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.btn-excluir');
            const approveButton = event.target.closest('.btn-aprovar');

            if (deleteButton) {
                const id = deleteButton.dataset.id;
                const status = deleteButton.dataset.status;
                const solicitante = deleteButton.closest('tr').querySelector('td:nth-child(4)').textContent;
                
                let confirmMessage = `Tem certeza que deseja EXCLUIR a solicitação de ${solicitante}? Esta ação não pode ser desfeita.`;
                
                if (status === 'Aprovada') {
                    confirmMessage += '\n\nAVISO: A Ordem de Serviço gerada a partir desta solicitação também será excluída.';
                }
                
                if (confirm(confirmMessage)) {
                    excluirSolicitacaoEos(id);
                }
            }

            if (approveButton) {
                const id = approveButton.dataset.id;
                const solicitante = approveButton.closest('tr').querySelector('td:nth-child(4)').textContent;
                
                if (confirm(`Tem certeza que deseja APROVAR a solicitação de ${solicitante}?`)) {
                    aprovarSolicitacao(id);
                }
            }
        });

        function aprovarSolicitacao(id) {
            fetch('api/aprovar_solicitacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id }),
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    carregarDados(); // Recarrega a lista para atualizar o status
                }
            });
        }

        function excluirSolicitacaoEos(id) {
            fetch('api/excluir_solicitacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id }),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    carregarDados(); // Recarrega a lista para remover o item
                } else {
                    alert('Erro ao excluir: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Erro na requisição de exclusão:', error);
                alert('Ocorreu um erro de comunicação ao tentar excluir a solicitação.');
            });
        }

        carregarDados();
    });
    </script>
</body>
</html>
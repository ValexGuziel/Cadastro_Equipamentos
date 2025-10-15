<?php 
$page_title = 'Lista de Ordens de Serviço';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <style>
        /* Estilos para indicadores de ordenação */
        th[data-column] { cursor: pointer; user-select: none; position: relative; }
        th[data-column]::after {
            content: '';
            position: absolute;
            right: 10px;
            top: 50%;
            border: solid #aaa;
            border-width: 0 2px 2px 0;
            display: inline-block;
            padding: 3px;
            opacity: 0;
            transition: all 0.2s;
        }
        th[data-column].sort-asc::after { opacity: 1; transform: translateY(-70%) rotate(-135deg); }
        th[data-column].sort-desc::after { opacity: 1; transform: translateY(-30%) rotate(45deg); }
    </style>
    <?php require_once __DIR__ . '/header.php'; ?>
</head>
<body>
    <main class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1><i class="bi bi-list-ul"></i> Ordens de Serviço</h1>
        </div>

        <div class="mb-3">
            <input type="text" id="campo-busca" class="form-control" placeholder="Buscar por número, equipamento, setor, status...">
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead id="tabela-cabecalho">
                    <tr>
                        <th data-column="numero_os">Nº O.S.</th>
                        <th data-column="equipamento_nome">Equipamento</th>
                        <th data-column="setor_nome">Setor</th>
                        <th data-column="data_inicial">Data Relevante</th>
                        <th data-column="tecnico_nome">Técnico</th>
                        <th data-column="prioridade">Prioridade</th>
                        <th data-column="status">Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpo-tabela-os">
                    <tr>
                        <td colspan="8" class="text-center">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav id="paginacao-container" class="d-flex justify-content-center">
            <!-- Paginação será gerada aqui -->
        </nav>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('corpo-tabela-os');
        const paginacaoContainer = document.getElementById('paginacao-container');
        const campoBusca = document.getElementById('campo-busca');
        const thead = document.getElementById('tabela-cabecalho');

        let todasOS = [];
        let dadosFiltrados = [];
        let paginaAtual = 1;
        const linhasPorPagina = 15;

        let sortColumn = 'data_inicial'; // Coluna de ordenação inicial
        let sortDirection = 'desc'; // Direção inicial

        function formatarData(data) {
            if (!data) return 'N/A';
            try {
                const dt = new Date(data);
                return dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return 'Data inválida';
            }
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'Aberta':
                    return 'bg-danger';
                case 'Concluída':
                    return 'bg-success';
                default:
                    return 'bg-dark';
            }
        }

        function renderizarTabela(pagina) {
            tbody.innerHTML = '';
            paginaAtual = pagina;

            const inicio = (pagina - 1) * linhasPorPagina;
            const fim = inicio + linhasPorPagina;
            const itensPaginados = dadosFiltrados.slice(inicio, fim);

            if (itensPaginados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center">Nenhuma Ordem de Serviço encontrada.</td></tr>`;
                return;
            }

            itensPaginados.forEach(os => {
                const statusClass = getStatusBadgeClass(os.status);
                
                // Determina qual data exibir e o título da célula
                const isConcluida = os.status === 'Concluída';
                const dataParaExibir = isConcluida ? os.data_final : os.data_inicial;
                const tituloData = isConcluida ? 'Data de Conclusão' : 'Data de Abertura';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${os.numero_os}</td>
                    <td>${os.equipamento_tag} - ${os.equipamento_nome}</td>
                    <td>${os.setor_nome}</td>
                    <td title="${tituloData}">${formatarData(dataParaExibir)}</td>
                    <td>${os.tecnico_nome || 'N/A'}</td>
                    <td>${os.prioridade}</td>
                    <td><span class="badge ${statusClass}">${os.status}</span></td>
                    <td>
                        <a href="editar_os.php?id=${os.id}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                        <a href="imprimir_os.php?id=${os.id}" target="_blank" class="btn btn-sm btn-info" title="Imprimir"><i class="bi bi-printer"></i></a>
                        <button class="btn btn-sm btn-danger btn-excluir" data-id="${os.id}" title="Excluir"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderizarPaginacao() {
            paginacaoContainer.innerHTML = '';
            const totalPaginas = Math.ceil(dadosFiltrados.length / linhasPorPagina);
            if (totalPaginas <= 1) return;

            const ul = document.createElement('ul');
            ul.className = 'pagination';

            for (let i = 1; i <= totalPaginas; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === paginaAtual ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                li.addEventListener('click', (e) => {
                    e.preventDefault();
                    paginaAtual = i;
                    renderizarTabela(i);
                    renderizarPaginacao();
                });
                ul.appendChild(li);
            }
            paginacaoContainer.appendChild(ul);
        }

        function filtrarEAtualizar() {
            const termoBusca = campoBusca.value.toLowerCase();
            dadosFiltrados = todasOS.filter(os => {
                return (
                    os.numero_os.toLowerCase().includes(termoBusca) ||
                    os.equipamento_nome.toLowerCase().includes(termoBusca) ||
                    os.equipamento_tag.toLowerCase().includes(termoBusca) ||
                    os.setor_nome.toLowerCase().includes(termoBusca) ||
                    (os.tecnico_nome && os.tecnico_nome.toLowerCase().includes(termoBusca)) ||
                    os.status.toLowerCase().includes(termoBusca) ||
                    os.prioridade.toLowerCase().includes(termoBusca)
                );
            });
            ordenarDados(sortColumn, false); // Re-ordena os dados filtrados
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

            // Atualiza feedback visual
            thead.querySelectorAll('th[data-column]').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
            thead.querySelector(`th[data-column="${coluna}"]`).classList.add(`sort-${sortDirection}`);

            renderizarTabela(1);
            renderizarPaginacao();
        }

        async function carregarDados() {
            try {
                const response = await fetch('api/listar_os.php');
                const result = await response.json();
                if (result.success) {
                    todasOS = result.data;
                    filtrarEAtualizar();
                } else {
                    throw new Error(result.message || 'Erro ao carregar dados.');
                }
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Falha ao carregar Ordens de Serviço.</td></tr>`;
                console.error(error);
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

        // Delegação de eventos para o botão de exclusão
        tbody.addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.btn-excluir');

            if (deleteButton) {
                const id = deleteButton.dataset.id;
                const osNumero = deleteButton.closest('tr').querySelector('td').textContent; // Pega o número da OS da primeira célula
                
                if (confirm(`Tem certeza que deseja excluir a Ordem de Serviço Nº ${osNumero}?`)) {
                    excluirOS(id);
                }
            }
        });

        function excluirOS(id) {
            fetch('api/excluir_os.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id }),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Remove o item do array de dados e re-renderiza a tabela
                    todasOS = todasOS.filter(os => os.id != id);
                    filtrarEAtualizar();
                    // Opcional: mostrar um alerta de sucesso mais sutil
                    alert(result.message);
                } else {
                    alert('Erro ao excluir: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Erro na requisição de exclusão:', error);
                alert('Ocorreu um erro de comunicação ao tentar excluir a O.S.');
            });
        }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
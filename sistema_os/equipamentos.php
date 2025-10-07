<?php 
$page_title = 'Lista de Equipamentos';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <style>
    th[data-column] {
      cursor: pointer;
      position: relative;
      user-select: none;
    }

    th[data-column]::after {
      content: '';
      position: absolute;
      right: 10px;
      top: 50%;
      border: solid var(--cor-texto-claro);
      border-width: 0 2px 2px 0;
      display: inline-block;
      padding: 3px;
      opacity: 0;
      transition: all 0.2s;
    }

    th[data-column].sort-asc::after {
      opacity: 1;
      transform: translateY(-70%) rotate(-135deg);
    }

    th[data-column].sort-desc::after {
      opacity: 1;
      transform: translateY(-30%) rotate(45deg);
    }
  </style>
  <?php require_once __DIR__ . '/header.php'; ?>
</head>

<body>
  <main class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1><i class="bi bi-wrench-adjustable-circle"></i> Lista de Equipamentos</h1>
    </div>

    <div class="mb-3">
      <input type="text" id="campo-busca" class="form-control"
        placeholder="Digite a tag, nome ou setor para filtrar...">
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-striped" id="tabela-equipamentos">
        <thead id="tabela-cabecalho">
          <tr>
            <th data-column="id">ID</th>
            <th data-column="tag">Tag</th>
            <th data-column="nome">Nome do Equipamento</th>
            <th data-column="setor_nome">Setor</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody id="corpo-tabela-equipamentos">
          <tr>
            <td colspan="5" class="text-center">Carregando equipamentos...</td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav id="paginacao-container" class="d-flex justify-content-center">
      <!-- Botões de paginação serão inseridos aqui -->
    </nav>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Executa o código quando o conteúdo da página estiver totalmente carregado.
    document.addEventListener('DOMContentLoaded', function () {
      const tbody = document.getElementById('corpo-tabela-equipamentos');
      const paginacaoContainer = document.getElementById('paginacao-container');
      const campoBusca = document.getElementById('campo-busca');
      const thead = document.getElementById('tabela-cabecalho');

      let todosEquipamentos = []; // Armazena todos os dados da API
      let dadosFiltrados = []; // Armazena os dados após a busca
      let paginaAtual = 1;
      const linhasPorPagina = 10;

      let sortColumn = 'nome'; // Coluna de ordenação inicial
      let sortDirection = 'asc'; // Direção inicial

      // Função para renderizar a tabela com os dados de uma página específica
      function renderizarTabela(pagina) {
        tbody.innerHTML = '';
        paginaAtual = pagina;

        const inicio = (pagina - 1) * linhasPorPagina;
        const fim = inicio + linhasPorPagina;
        const itensPaginados = dadosFiltrados.slice(inicio, fim);

        if (itensPaginados.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" class="text-center">Nenhum equipamento encontrado.</td></tr>`;
          return;
        }

        itensPaginados.forEach(equipamento => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${equipamento.id}</td>
            <td>${equipamento.tag}</td>
            <td>${equipamento.nome}</td>
            <td>${equipamento.setor_nome}</td>
            <td>
              <a href="editar_equipamento.php?id=${equipamento.id}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
              <button class="btn btn-sm btn-danger btn-excluir" data-id="${equipamento.id}" title="Excluir"><i class="bi bi-trash"></i></button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }

      // Função para criar os botões de paginação
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
          li.addEventListener('click', function (e) {
            e.preventDefault();
            renderizarTabela(i);
            renderizarPaginacao();
          });
          ul.appendChild(li);
        }
        paginacaoContainer.appendChild(ul);
      }

      // Função para filtrar e atualizar a exibição
      function filtrarEAtualizar() {
        const termoBusca = campoBusca.value.toLowerCase();
        dadosFiltrados = todosEquipamentos.filter(equip => {
          return (
            equip.tag.toLowerCase().includes(termoBusca) ||
            equip.nome.toLowerCase().includes(termoBusca) ||
            equip.setor_nome.toLowerCase().includes(termoBusca)
          );
        });
        renderizarTabela(1);
        renderizarPaginacao();
      }

      // Função para ordenar os dados
      function ordenarDados(coluna) {
        if (sortColumn === coluna) {
          sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          sortColumn = coluna;
          sortDirection = 'asc';
        }

        dadosFiltrados.sort((a, b) => {
          const valA = a[sortColumn];
          const valB = b[sortColumn];
          const comparison = String(valA).localeCompare(String(valB), undefined, {
            numeric: true
          });
          return sortDirection === 'asc' ? comparison : -comparison;
        });

        // Após ordenar, apenas renderizamos a tabela, não filtramos novamente
        renderizarTabela(1);
        renderizarPaginacao();
      }

      // Carrega os dados iniciais da API
      fetch('api/listar_equipamentos.php')
        .then(response => {
          if (!response.ok) {
            throw new Error('Erro na rede ou no servidor: ' + response.statusText);
          }
          return response.json();
        })
        .then(apiResponse => {
          if (apiResponse.success) {
            todosEquipamentos = apiResponse.data;
            ordenarDados(sortColumn); // Ordena os dados iniciais
            filtrarEAtualizar(); // Filtra (se houver algo no campo de busca) e renderiza
          } else {
            throw new Error(apiResponse.message || 'A API retornou um erro sem mensagem.');
          }
        })
        .catch(error => {
          console.error('Erro ao buscar equipamentos:', error);
          tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Falha ao carregar os dados.</td></tr>`;
        });

      // Lógica para o campo de busca
      campoBusca.addEventListener('keyup', filtrarEAtualizar);

      // Lógica para o clique no cabeçalho da tabela
      thead.addEventListener('click', function (event) {
        const th = event.target.closest('th');
        if (!th || !th.dataset.column) return; // Sai se não clicou em um TH com data-column

        const colunaClicada = th.dataset.column;
        ordenarDados(colunaClicada);

        // Atualiza o feedback visual nos cabeçalhos
        thead.querySelectorAll('th[data-column]').forEach(header => {
          header.classList.remove('sort-asc', 'sort-desc');
        });
        th.classList.add(`sort-${sortDirection}`);
      });

      // Delegação de eventos para os botões de ação
      tbody.addEventListener('click', function (event) {
        const target = event.target;
        const id = target.dataset.id;

        if (target.classList.contains('btn-excluir')) {
          if (confirm(`Tem certeza que deseja excluir o equipamento com ID ${id}?`)) {
            excluirEquipamento(id);
          }
        }
      });

      function excluirEquipamento(id) {
        fetch('api/excluir_equipamento.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id=${id}`,
        })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              // Remove o item do array de dados e re-renderiza
              todosEquipamentos = todosEquipamentos.filter(e => e.id != id);
              filtrarEAtualizar();
            } else {
              alert('Erro ao excluir: ' + result.message);
            }
          })
          .catch(error => console.error('Erro na requisição de exclusão:', error));
      }
    });
  </script>
</body>

</html>
<?php 
$page_title = 'Dashboard - Gestão de O.S.';
$extra_css = '
  <style>
    .card-link {
      color: var(--cor-texto-claro);
      text-decoration: none;
    }

    .card-link .card-title {
      color: var(--cor-primaria-amarelo);
    }

    .protected-link .card-title::after {
      content: " \f47a"; /* Bootstrap icon for lock */
      font-family: "bootstrap-icons";
      font-size: 0.8em;
      vertical-align: middle;
      margin-left: 5px;
      color: var(--cor-texto-secundario);
      transition: opacity 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .card {
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
      background-color: var(--cor-container-escuro);
      border: 1px solid var(--cor-borda-escura);
    }

    .kpi-chart-card .card-body {
      height: 300px;
    }

    .card-header {
      background-color: #3a3f44;
    }
  </style>';
require_once __DIR__ . '/header.php'; 
?>
  <main class="container mt-4">
    <div class="text-center mb-5">
      <h1 class="display-5">Bem-vindo ao Sistema de Gestão de O.S.</h1>
      <p class="lead">Selecione uma das opções abaixo para começar a gerenciar suas manutenções.</p>
    </div>

    <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-lg-5">
      <div class="col">
        <a href="listar_solicitacoes.php" class="card-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-bell-fill fs-2 mb-2"></i><br>Solicitações</h5>
              <p class="card-text">Aprove ou rejeite solicitações de serviço para gerar novas O.S.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col">
        <a href="index.php" class="card-link protected-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-plus-circle-fill fs-2 mb-2"></i><br>Nova O.S.</h5>
              <p class="card-text">Crie Ordens de Serviço para manutenções corretivas ou preventivas.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col">
        <a href="ordens_servico.php" class="card-link protected-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-list-task fs-2 mb-2"></i><br>Lista de Ordens</h5>
              <p class="card-text">Visualize, filtre e gerencie todas as Ordens de Serviço abertas e e concluídas.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col">
        <a href="equipamentos.php" class="card-link protected-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-wrench-adjustable-circle-fill fs-2 mb-2"></i><br>Equipamentos</h5>
              <p class="card-text">Gerencie o cadastro de todos os equipamentos, suas tags e setores.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col">
        <a href="listar_planos.php" class="card-link protected-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-calendar-week-fill fs-2 mb-2"></i><br>Planos de Manutenção</h5>
              <p class="card-text">Crie e acompanhe os planos de manutenção preventiva dos equipamentos.</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </main>

  <!-- Seção de Gráficos de KPI -->
  <section class="container mt-5">
    <h2 class="text-center mb-4">Indicadores de Manutenção (KPIs)</h2>
    <p class="text-center text-muted mb-4">Acompanhe a saúde da sua operação com os principais indicadores: MTBF (confiabilidade do equipamento), MTTR (eficiência no reparo), Cumprimento de Preventivas e o Backlog de serviços pendentes.</p>
    <p class="text-center text-muted small mb-4"><em><b>Valores de Referência:</b> Busque um <strong>MTBF</strong> cada vez maior, um <strong>MTTR</strong> menor, <strong>Preventivas</strong> próximo de 100%, e um <strong>Backlog</strong> estável e controlado.</em></p>

    <!-- Filtro de Período -->
    <div class="row justify-content-end mb-4">
        <div class="col-md-4 col-lg-3">
            <label for="kpi-period-filter" class="form-label">Filtrar Período:</label>
            <select class="form-select" id="kpi-period-filter">
                <option value="30">Últimos 30 dias</option>
                <option value="90" selected>Últimos 90 dias</option>
                <option value="180">Últimos 180 dias</option>
                <option value="360">Último Ano</option>
                <option value="all">Todo o Período</option>
            </select>
        </div>
    </div>

    <!-- Primeira linha de KPIs -->
    <div class="row g-4 row-cols-1 row-cols-md-3" id="kpi-charts-container-1">
      <!-- Gráfico MTBF -->
      <div class="col">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">Tempo médio entre falhas MTBF (Horas)</div>
          <div class="card-body d-flex justify-content-center align-items-center"><canvas id="grafico-mtbf"></canvas></div>
        </div>
      </div>
      <!-- Gráfico MTTR -->
      <div class="col">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">Tempo médio de reparos MTTR (Horas)</div>
          <div class="card-body d-flex justify-content-center align-items-center"><canvas id="grafico-mttr"></canvas></div>
        </div>
      </div>
      <!-- Gráfico Cumprimento de Preventivas -->
      <div class="col">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">Cumprimento de Preventivas (%)</div>
          <div class="card-body d-flex justify-content-center align-items-center"><canvas id="grafico-mp"></canvas></div>
        </div>
      </div>
    </div>

    <!-- Segunda linha de KPIs -->
    <div class="row g-4 row-cols-1 row-cols-md-3 mt-1" id="kpi-charts-container-2">
      <!-- Gráfico Backlog -->
      <div class="col">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">Trabalho a ser realizado Backlog (Horas)</div>
          <div class="card-body d-flex justify-content-center align-items-center"><canvas id="grafico-backlog"></canvas></div>
        </div>
      </div>
      <!-- Gráfico Produtividade -->
      <div class="col">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">Produtividade da mão de obra (%)</div>
          <div class="card-body d-flex justify-content-center align-items-center"><canvas id="grafico-produtividade"></canvas></div>
        </div>
      </div>
      <!-- Gráfico HH por Tipo -->
      <div class="col">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">O.S. Concluídas por Técnico</div>
          <div class="card-body d-flex justify-content-center align-items-center"><canvas id="grafico-os-tecnico"></canvas></div>
        </div>
      </div>
    </div>

  </section>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Plugin para exibir os valores nos gráficos -->
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const periodFilter = document.getElementById('kpi-period-filter');
      const charts = {}; // Objeto para armazenar instâncias dos gráficos

      // Registra o plugin globalmente para todos os gráficos
      Chart.register(ChartDataLabels);

      // Custom plugin para desenhar o texto no centro do gráfico de rosca
      const doughnutTextPlugin = {
        id: 'doughnutText',
        afterDraw(chart) {
          if (chart.config.type !== 'doughnut') return;

          const { ctx, data, chartArea: { top, bottom, left, right, width, height } } = chart;
          let value = data.datasets[0].data[0];
          const label = data.labels[0];

          // Arredonda o valor para no máximo 2 casas decimais
          const formattedValue = Number.isFinite(value) ? Math.round(value * 100) / 100 : value;

          ctx.save();
          const x = chart.getDatasetMeta(0).data[0].x;
          const y = chart.getDatasetMeta(0).data[0].y;
          ctx.font = 'bold 2rem sans-serif';
          ctx.fillStyle = '#f8f9fa';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText(label.includes('%') ? `${formattedValue}%` : formattedValue, x, y);
          ctx.restore();
        }
      };

      // Configurações globais para os gráficos no tema escuro
      Chart.defaults.color = '#f8f9fa'; // Cor do texto dos eixos e legendas
      Chart.defaults.borderColor = '#495057'; // Cor das linhas de grade

      // Função para criar um gráfico de Rosca/Gauge para KPIs de valor único
      const criarGraficoRosca = (ctx, label, valor, cor, max = null) => {
        // Para percentuais, o valor restante é 100 - valor. Para outros, usamos um valor de referência.
        // **CORREÇÃO APLICADA AQUI**
        const roundedValue = Number.isFinite(valor) ? Math.round(valor * 100) / 100 : valor;

        const isPercent = label.includes('%');
        const dataValue = roundedValue;
        let remainingValue = isPercent ? Math.max(0, 100 - roundedValue) : (max || roundedValue * 1.5) - roundedValue;
        remainingValue = Number.isFinite(remainingValue) ? Math.round(remainingValue * 100) / 100 : remainingValue;

        return new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: [label, ''],
            datasets: [{
              label: label,
              data: [dataValue, Math.max(0, remainingValue)], // Garante que o valor restante não seja negativo
              backgroundColor: [cor, '#495057'],
              borderColor: ['#343a40'],
              borderWidth: 2,
              circumference: 180, // Meia-rosca para efeito de gauge
              rotation: 270,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%', // Espessura da rosca
            plugins: {
              legend: { display: false },
              // Tooltip agora é habilitado, mas formatado para mostrar o valor correto
              tooltip: {
                callbacks: {
                  label: (context) => {
                    // Mostra o tooltip apenas para a fatia principal do gráfico
                    if (context.dataIndex === 0) {
                      return `${context.dataset.label}: ${context.parsed}%`;
                    }
                    return null; // Oculta o tooltip para a fatia "restante"
                  }
                }
              }
            }
          }
        });
      };

      // Função para criar um gráfico de barras horizontais para comparação
      const criarGraficoBarrasHorizontais = (ctx, labels, data, title) => {
        const colors = [
            'rgba(54, 162, 235, 0.7)',  // Azul
            'rgba(75, 192, 192, 0.7)',  // Verde
            'rgba(255, 206, 86, 0.7)',  // Amarelo
            'rgba(153, 102, 255, 0.7)', // Roxo
            'rgba(255, 159, 64, 0.7)',  // Laranja
            'rgba(255, 99, 132, 0.7)',   // Vermelho
            'rgba(99, 255, 132, 0.7)',  // Verde Claro
        ];

        const backgroundColors = labels.map((_, index) => colors[index % colors.length]);
        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Eixo Y para categorias (horizontal)
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        color: '#f8f9fa',
                        anchor: 'end',
                        align: 'end',
                        formatter: (value) => {
                            // Arredonda para no máximo 2 casas decimais
                            return Number.isFinite(value) ? Math.round(value * 100) / 100 : value;
                        },
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: { 
                            color: '#f8f9fa',
                        },
                    },
                    x: {
                        ticks: { color: '#f8f9fa' }
                    }
                }
            }
        });
      };

      // Função para buscar dados e renderizar/atualizar os gráficos
      function fetchAndRenderKPIs(period) {
        // Feedback visual de carregamento para ambas as linhas
        document.getElementById('kpi-charts-container-1').style.opacity = '0.5';
        document.getElementById('kpi-charts-container-2').style.opacity = '0.5';

        fetch(`api/kpi_data.php?period=${period}`)
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              const kpiData = result.data;
              const kpiConfigs = {
                'grafico-mtbf': { type: 'rosca', label: 'MTBF', value: kpiData.mtbf, color: 'rgba(54, 162, 235, 0.8)' },
                'grafico-mttr': { type: 'rosca', label: 'MTTR', value: kpiData.mttr, color: 'rgba(255, 99, 132, 0.8)' },
                'grafico-mp': { type: 'rosca', label: 'Preventivas (%)', value: kpiData.mp_compliance, color: 'rgba(75, 192, 192, 0.8)' },
                'grafico-backlog': { type: 'rosca', label: 'Backlog', value: kpiData.backlog, color: 'rgba(255, 159, 64, 0.8)' },
                'grafico-produtividade': { type: 'rosca', label: 'Produtividade', value: kpiData.produtividade, color: 'rgba(153, 102, 255, 0.8)', max: 2 }, // Max 2 para produtividade
              };

              for (const id in kpiConfigs) {
                if (charts[id]) charts[id].destroy(); // Destroi o gráfico antigo se existir
                const ctx = document.getElementById(id).getContext('2d');
                const config = kpiConfigs[id];
                charts[id] = criarGraficoRosca(ctx, config.label, config.value, config.color, config.max, [doughnutTextPlugin]);
              }

              // Renderiza o gráfico de O.S. por Técnico
              if (kpiData.os_por_tecnico && kpiData.os_por_tecnico.length > 0) {
                  const osTecnicoData = kpiData.os_por_tecnico;
                  const labels = osTecnicoData.map(item => item.tecnico_nome);
                  const data = osTecnicoData.map(item => item.total_os);
                  const ctxBarras = document.getElementById('grafico-os-tecnico').getContext('2d');
                  if (charts['grafico-os-tecnico']) charts['grafico-os-tecnico'].destroy();
                  charts['grafico-os-tecnico'] = criarGraficoBarrasHorizontais(ctxBarras, labels, data, 'O.S. por Técnico');
              }
            } else {
              throw new Error(result.message || 'Falha ao obter dados dos KPIs.');
            }
          })
          .catch(error => {
            console.error('Erro ao buscar dados para o dashboard:', error);
            const errorHtml = `<div class="col-12"><div class="alert alert-danger">Não foi possível carregar os indicadores de manutenção.</div></div>`;
            document.getElementById('kpi-charts-container-1').innerHTML = errorHtml;
            document.getElementById('kpi-charts-container-2').innerHTML = ''; // Limpa a segunda linha
          })
          .finally(() => {
            // Remove o feedback de carregamento
            document.getElementById('kpi-charts-container-1').style.opacity = '1';
            document.getElementById('kpi-charts-container-2').style.opacity = '1';
          });
      }

      // Event listener para o filtro
      periodFilter.addEventListener('change', (event) => {
        fetchAndRenderKPIs(event.target.value);
      });

      // Carga inicial dos dados
      fetchAndRenderKPIs(periodFilter.value);

      // --- Lógica de proteção de links ---
      const correctPassword = 'Mastpet123';
      const sessionKey = 'dashboard_authenticated';

      function unlockLinks() {
        sessionStorage.setItem(sessionKey, 'true');
        document.querySelectorAll('.protected-link').forEach(link => {
          link.classList.remove('protected-link');
        });
      }

      // Verifica se já está autenticado na sessão
      if (sessionStorage.getItem(sessionKey) === 'true') {
        unlockLinks();
      }

      document.body.addEventListener('click', function(event) {
        const protectedLink = event.target.closest('.protected-link');
        if (protectedLink) {
          event.preventDefault(); // Impede a navegação

          const enteredPassword = prompt('Para acessar esta área, por favor, insira a senha de administrador:');

          if (enteredPassword === null) { // Usuário clicou em "Cancelar"
            return;
          }

          if (enteredPassword === correctPassword) {
            unlockLinks();
            window.location.href = protectedLink.href; // Redireciona para o link original
          } else {
            alert('Senha incorreta. Acesso negado.');
          }
        }
      });
    });
  </script>
</body>

</html>
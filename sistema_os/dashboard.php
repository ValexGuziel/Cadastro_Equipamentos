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

    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <a href="index.php" class="card-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-plus-circle-fill"></i><br>Ordem de Serviço</h5>
              <p class="card-text">Crie novas Ordens de Serviço para manutenções corretivas ou preventivas, detalhando o
                problema e a prioridade.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="equipamentos.html" class="card-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-wrench-adjustable-circle-fill"></i><br>Equipamentos</h5>
              <p class="card-text">Gerencie o cadastro de todos os equipamentos, incluindo suas tags, nomes e setores
                correspondentes.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="ordens_servico.php" class="card-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-list-task"></i><br>Lista das Ordens</h5>
              <p class="card-text">Visualize, filtre e gerencie todas as Ordens de Serviço abertas e concluídas em um
                único lugar.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="listar_planos.php" class="card-link">
          <div class="card h-100 text-center">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-calendar-week-fill"></i><br>Plano de Manutenção</h5>
              <p class="card-text">Crie e acompanhe os planos de manutenção preventiva para garantir a longevidade e
                eficiência dos equipamentos.</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </main>

  <!-- Seção de Gráficos de KPI -->
  <section class="container mt-5">
    <h2 class="text-center mb-4">Indicadores de Manutenção (KPIs)</h2>
    <div class="row g-4">
      <!-- Gráfico MTBF -->
      <div class="col-lg-3 col-md-6">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">MTBF (Horas)</div>
          <div class="card-body d-flex justify-content-center align-items-center">
            <canvas id="grafico-mtbf"></canvas>
          </div>
        </div>
      </div>
      <!-- Gráfico MTTR -->
      <div class="col-lg-3 col-md-6">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">MTTR (Horas)</div>
          <div class="card-body d-flex justify-content-center align-items-center">
            <canvas id="grafico-mttr"></canvas>
          </div>
        </div>
      </div>
      <!-- Gráfico Cumprimento de Preventivas -->
      <div class="col-lg-3 col-md-6">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">Cumprimento de Preventivas (%)</div>
          <div class="card-body d-flex justify-content-center align-items-center">
            <canvas id="grafico-mp"></canvas>
          </div>
        </div>
      </div>
      <!-- Gráfico Backlog -->
      <div class="col-lg-3 col-md-6">
        <div class="card kpi-chart-card">
          <div class="card-header text-center">Backlog (Horas)</div>
          <div class="card-body d-flex justify-content-center align-items-center">
            <canvas id="grafico-backlog"></canvas>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Configurações globais para os gráficos no tema escuro
      Chart.defaults.color = '#f8f9fa'; // Cor do texto dos eixos e legendas
      Chart.defaults.borderColor = '#495057'; // Cor das linhas de grade

      // Função para criar um gráfico de barra simples
      const criarGraficoBarra = (ctx, label, valor, cor) => {
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: [label],
            datasets: [{
              label: label,
              data: [valor],
              backgroundColor: [cor],
              borderColor: [cor],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  color: '#f8f9fa'
                }
              },
              x: {
                ticks: {
                  color: '#f8f9fa'
                }
              }
            },
            plugins: {
              legend: {
                display: false // Oculta a legenda para gráficos simples
              }
            }
          }
        });
      };

      // Busca os dados reais da API
      fetch('api/kpi_data.php')
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            const kpiData = result.data;

            // Renderiza os gráficos com os dados da API
            const ctxMtbf = document.getElementById('grafico-mtbf').getContext('2d');
            criarGraficoBarra(ctxMtbf, 'MTBF', kpiData.mtbf, 'rgba(54, 162, 235, 0.6)'); // Azul

            const ctxMttr = document.getElementById('grafico-mttr').getContext('2d');
            criarGraficoBarra(ctxMttr, 'MTTR', kpiData.mttr, 'rgba(255, 99, 132, 0.6)'); // Vermelho

            const ctxMp = document.getElementById('grafico-mp').getContext('2d');
            criarGraficoBarra(ctxMp, 'Preventivas', kpiData.mp_compliance, 'rgba(75, 192, 192, 0.6)'); // Verde

            const ctxBacklog = document.getElementById('grafico-backlog').getContext('2d');
            criarGraficoBarra(ctxBacklog, 'Backlog', kpiData.backlog, 'rgba(255, 159, 64, 0.6)'); // Laranja

          } else {
            throw new Error(result.message || 'Falha ao obter dados dos KPIs.');
          }
        })
        .catch(error => {
          console.error('Erro ao buscar dados para o dashboard:', error);
          // Opcional: Mostrar uma mensagem de erro na área dos gráficos
          const kpiContainer = document.querySelector('.container.mt-5 .row.g-4');
          if (kpiContainer) {
            kpiContainer.innerHTML = `<div class="col-12"><div class="alert alert-danger">Não foi possível carregar os indicadores de manutenção.</div></div>`;
          }
        });
    });
  </script>
</body>

</html>
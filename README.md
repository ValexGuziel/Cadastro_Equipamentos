[README.md](https://github.com/user-attachments/files/22739259/README.md)
# Sistema de Gestão de Ordens de Serviço (O.S.)

Este é um sistema web desenvolvido em PHP para a gestão completa de ordens de serviço de manutenção, equipamentos e planos de manutenção preventiva. A interface é moderna, responsiva e utiliza um tema escuro para melhor visualização.

## ✨ Funcionalidades Principais

O sistema é dividido em vários módulos para facilitar a gestão:

### 1. Dashboard
- **Visão Geral:** Tela inicial com acesso rápido às principais seções do sistema.
- **KPIs de Manutenção:** Gráficos dinâmicos para indicadores chave de desempenho:
  - **MTBF** (Mean Time Between Failures - Tempo Médio Entre Falhas)
  - **MTTR** (Mean Time To Repair - Tempo Médio Para Reparo)
  - **Cumprimento de Preventivas** (%)
  - **Backlog** (Horas de trabalho pendentes)

### 2. Ordens de Serviço (O.S.)
- **Criação:** Formulário completo para criar novas O.S., com geração automática de número, seleção de equipamento, setor, tipo de manutenção, prioridade, etc.
- **Listagem:** Tabela com todas as O.S. cadastradas, com recursos de:
  - **Busca dinâmica** por qualquer campo (número, equipamento, status, etc.).
  - **Ordenação** por colunas (Nº O.S., Data, Prioridade, etc.).
  - **Paginação** para lidar com grandes volumes de dados.
  - Indicadores visuais de status (ex: "Aberta", "Concluída").
- **Edição:** Modificação de todos os campos de uma O.S. existente, incluindo a adição de custos de peças e mão de obra.
- **Impressão:** Geração de uma página otimizada para impressão com todos os detalhes da O.S.

### 3. Equipamentos
- **Cadastro:** Adição de novos equipamentos através de um modal na tela de criação de O.S. ou em uma tela dedicada.
- **Listagem:** Visualização de todos os equipamentos com busca, ordenação e paginação.
- **Edição e Exclusão:** Gerenciamento completo do ciclo de vida dos equipamentos.

### 4. Planos de Manutenção Preventiva
- **Criação:** Cadastro de planos de manutenção para equipamentos que ainda não possuem um. Define-se a periodicidade, a data da última manutenção e as instruções (checklist).
- **Listagem:** Tabela com todos os planos, destacando visualmente os planos com preventivas vencidas.
- **Geração de O.S. Preventiva:** Um botão permite gerar automaticamente uma nova O.S. com base nos dados do plano.
- **Histórico:** Visualização do histórico de manutenções preventivas realizadas para cada plano.
- **Detalhes e Impressão:** Acesso rápido às instruções do plano e opção de impressão.

## 🛠️ Tecnologias Utilizadas

- **Backend:**
  - PHP 8+
  - MySQL / MariaDB

- **Frontend:**
  - HTML5
  - CSS3
  - JavaScript (ES6+)
  - [Bootstrap 5.3](https://getbootstrap.com/) para a estrutura e componentes de UI.
  - [Bootstrap Icons](https://icons.getbootstrap.com/) para a iconografia.
  - [Chart.js](https://www.chartjs.org/) para a renderização dos gráficos de KPI.

- **Comunicação:**
  - As interações dinâmicas (listagens, buscas, etc.) são feitas via **AJAX (Fetch API)**, consumindo uma API RESTful interna em PHP que retorna JSON.

## 🚀 Como Executar o Projeto

1.  **Pré-requisitos:**
    - Um ambiente de servidor web local como XAMPP, WAMP ou MAMP.
    - PHP 8 ou superior.
    - MySQL ou MariaDB.

2.  **Configuração do Banco de Dados:**
    - Crie um novo banco de dados no seu servidor (ex: `sistema_os_db`).
    - Importe o arquivo `database.sql` (não fornecido, precisa ser criado) para criar as tabelas necessárias (`ordens_servico`, `equipamentos`, `setores`, `planos_manutencao`, etc.).

3.  **Configuração do Projeto:**
    - Clone ou copie a pasta do projeto para o diretório do seu servidor web (ex: `C:/xampp/htdocs/sistema_os`).
    - Edite o arquivo `api/db_connect.php` com as suas credenciais de acesso ao banco de dados:
      ```php
      <?php
      $servername = "localhost";
      $username = "root"; // Seu usuário do DB
      $password = ""; // Sua senha do DB
      $dbname = "sistema_os_db"; // O nome do banco de dados que você criou

      // Cria a conexão
      $conn = new mysqli($servername, $username, $password, $dbname);

      // Checa a conexão
      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }

      // Define o charset para UTF-8
      $conn->set_charset("utf8mb4");
      ?>
      ```
    - Se você colocou o projeto em um subdiretório diferente de `/Projeto Novo/sistema_os/`, ajuste a variável `$base_url` no arquivo `header.php`:
      ```php
      // Exemplo para um projeto na raiz do htdocs
      $base_url = '/';

      // Exemplo para um projeto em htdocs/meu_app/
      $base_url = '/meu_app/';
      ```

4.  **Acesso:**
    - Inicie seu servidor Apache e MySQL.
    - Abra o navegador e acesse `http://localhost/Projeto%20Novo/sistema_os/dashboard.php` (ou o caminho correspondente à sua configuração).

## 🏛️ Estrutura de Arquivos

```
/sistema_os/
├── api/                  # Lógica de backend e conexão com DB
│   ├── add_plano.php
│   ├── atualizar_os.php
│   ├── db_connect.php
│   └── ... (outros endpoints)
├── *.php                 # Arquivos de frontend (páginas principais)
├── style.css             # Estilos CSS personalizados
└── README.md             # Este arquivo
```

---
*Projeto desenvolvido como uma solução para gestão de manutenção industrial e predial.*

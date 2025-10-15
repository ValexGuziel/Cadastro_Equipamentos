# Sistema de Gestão de Ordens de Serviço (O.S.)

Este é um sistema web completo, desenvolvido em PHP e MySQL, para a gestão de ordens de serviço de manutenção. Ele inclui funcionalidades para gerenciar equipamentos, planos de manutenção preventiva, solicitações de serviço e acompanhar o desempenho da equipe através de um dashboard com KPIs. A interface é moderna, responsiva e utiliza um tema escuro para melhor visualização.

## ✨ Funcionalidades Principais

O sistema é dividido em vários módulos para facilitar a gestão:

### 1. Dashboard
- **Visão Geral:** Tela inicial com acesso rápido às principais seções do sistema.
- **KPIs de Manutenção:** Gráficos dinâmicos para indicadores chave de desempenho, com filtro por período:
  - **MTBF** (Mean Time Between Failures - Tempo Médio Entre Falhas)
  - **MTTR** (Mean Time To Repair - Tempo Médio Para Reparo)
  - **Cumprimento de Preventivas** (%)
  - **Backlog** (Horas de trabalho pendentes)
  - **Fator de Produtividade da Mão de Obra**
  - **HH Empregado por Tipo de Manutenção** (Gráfico de Barras)
- **Acesso Restrito:** Links para áreas administrativas protegidos por senha.

### 2. Solicitações de Serviço
- **Abertura Simplificada:** Um formulário simples para que qualquer usuário possa solicitar um serviço de manutenção para um equipamento.
- **Gerenciamento de Solicitações:** Uma tela central para administradores aprovarem ou excluírem solicitações.
- **Geração de O.S.:** Ao aprovar uma solicitação, o sistema redireciona para a tela de criação de O.S. com os dados já preenchidos.

### 2. Ordens de Serviço (O.S.)
- **Criação:** Formulário completo para criar novas O.S., com geração automática de número, seleção de equipamento, setor, tipo de manutenção, prioridade, etc.
- **Cadastro Rápido:** Modais para adicionar novos equipamentos, setores e tipos de manutenção diretamente da tela de criação de O.S.
- **Listagem:** Tabela com todas as O.S. cadastradas, com recursos de:
  - **Busca dinâmica** por qualquer campo.
  - **Ordenação** por colunas.
  - **Paginação**.
  - Indicadores visuais de status (ex: "Aberta", "Concluída").
- **Edição:** Modificação de todos os campos de uma O.S. existente, incluindo a adição de custos de peças e mão de obra.
- **Impressão:** Geração de uma página otimizada para impressão com todos os detalhes da O.S.

### 3. Equipamentos
- **Gerenciamento Completo:** Tela dedicada para listar, buscar, ordenar, editar e excluir equipamentos.
- **Listagem:** Visualização de todos os equipamentos com busca, ordenação e paginação.

### 4. Planos de Manutenção Preventiva
- **Criação:** Cadastro de planos de manutenção para equipamentos que ainda não possuem um. Define-se a periodicidade, a data da última manutenção e as instruções (checklist).
- **Listagem:** Tabela com todos os planos, destacando visualmente os planos com preventivas vencidas.
- **Geração de O.S. Preventiva:** Um botão permite gerar automaticamente uma nova O.S. com base nos dados do plano.
- **Histórico:** Visualização do histórico de manutenções preventivas realizadas para cada plano.
- **Detalhes e Impressão:** Acesso rápido às instruções do plano e opção de impressão.
- **Edição e Exclusão:** Gerenciamento completo do ciclo de vida dos planos.

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
    - Crie um novo banco de dados no seu servidor MySQL/MariaDB (ex: `gestao_os`).
    - Importe o arquivo `database.sql` para dentro do banco de dados criado. Ele contém a estrutura de todas as tabelas e alguns dados de exemplo.

3.  **Configuração do Projeto:**
    - Clone ou copie a pasta do projeto para o diretório do seu servidor web (ex: `C:/xampp/htdocs/`).
    - Edite o arquivo `api/db_connect.php` com as suas credenciais de acesso ao banco de dados:
      ```php
      <?php
      $servername = "localhost";
      $username = "root"; // Seu usuário do DB
      $password = ""; // Sua senha do DB
      $dbname = "gestao_os"; // O nome do banco de dados que você criou

      // Cria a conexão
      $conn = new mysqli($servername, $username, $password, $dbname);
      ?>
      ```
    - A variável `$base_url` no arquivo `header.php` já está configurada para `/Projeto Novo/sistema_os/`. Se você colocar o projeto em um local diferente, ajuste este caminho.
      ```php
      // Exemplo para um projeto em htdocs/gestao_os/
      $base_url = '/gestao_os/';
      ```

4.  **Acesso:**
    - Inicie seu servidor Apache e MySQL.
    - Abra o navegador e acesse `http://localhost/Projeto%20Novo/sistema_os/dashboard.php` (ou o caminho correspondente à sua configuração).
    - A senha padrão para acessar as áreas administrativas é: `Mastpet123` (pode ser alterada no arquivo `dashboard.php`).

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
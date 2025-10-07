-- Arquivo de criação do banco de dados para o Sistema de Gestão de O.S.
-- Banco de dados: gestao_os

--
-- Criação do banco de dados `gestao_os` (se não existir)
--
CREATE DATABASE IF NOT EXISTS `gestao_os` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gestao_os`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `setores`
-- Armazena os setores/departamentos da empresa.
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Inserindo dados de exemplo para `setores`
--
INSERT INTO `setores` (`id`, `nome`) VALUES
(1, 'Produção'),
(2, 'Manutenção Elétrica'),
(3, 'Manutenção Mecânica'),
(4, 'Utilidades'),
(5, 'Administrativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipamentos`
-- Armazena o cadastro de todos os equipamentos.
--

CREATE TABLE `equipamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `setor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`),
  KEY `setor_id` (`setor_id`),
  CONSTRAINT `fk_equipamentos_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Inserindo dados de exemplo para `equipamentos`
--
INSERT INTO `equipamentos` (`id`, `tag`, `nome`, `setor_id`) VALUES
(1, 'P-01', 'Prensa Hidráulica 10T', 1),
(2, 'T-01', 'Torno CNC', 3),
(3, 'AC-01', 'Ar Condicionado Central', 4),
(4, 'CMP-01', 'Compressor de Ar 1', 4);

-- --------------------------------------------------------

--
-- Estrutura da tabela `planos_manutencao`
-- Armazena os planos de manutenção preventiva para os equipamentos.
--

CREATE TABLE `planos_manutencao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipamento_id` int(11) NOT NULL,
  `periodicidade` varchar(50) NOT NULL COMMENT 'Ex: Mensal, Trimestral, 30 dias',
  `data_ultima_preventiva` date DEFAULT NULL,
  `data_proxima_preventiva` date NOT NULL,
  `instrucoes` text DEFAULT NULL COMMENT 'Checklist e instruções da manutenção',
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipamento_id` (`equipamento_id`) COMMENT 'Um plano por equipamento',
  CONSTRAINT `fk_planos_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ordens_servico`
-- Tabela principal que armazena todas as ordens de serviço.
--

CREATE TABLE `ordens_servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_os` varchar(20) NOT NULL,
  `equipamento_id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `solicitante` varchar(100) DEFAULT NULL,
  `tipo_manutencao` enum('Corretiva','Preventiva','Melhoria','Preditiva') NOT NULL DEFAULT 'Corretiva',
  `prioridade` enum('Baixa','Média','Alta','Urgente') NOT NULL DEFAULT 'Média',
  `status` enum('Aberta','Em Andamento','Aguardando Peças','Concluída','Cancelada') NOT NULL DEFAULT 'Aberta',
  `data_inicial` date NOT NULL,
  `data_final` date DEFAULT NULL,
  `descricao_problema` text DEFAULT NULL,
  `descricao_servico` text DEFAULT NULL,
  `custo_pecas` decimal(10,2) DEFAULT 0.00,
  `custo_mao_de_obra` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_os` (`numero_os`),
  KEY `equipamento_id` (`equipamento_id`),
  KEY `setor_id` (`setor_id`),
  CONSTRAINT `fk_os_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT `fk_os_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `historico_preventivas`
-- Registra a execução de cada O.S. preventiva vinculada a um plano.
--

CREATE TABLE `historico_preventivas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plano_manutencao_id` int(11) NOT NULL,
  `ordem_servico_id` int(11) DEFAULT NULL,
  `data_realizacao` date NOT NULL,
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plano_manutencao_id` (`plano_manutencao_id`),
  KEY `ordem_servico_id` (`ordem_servico_id`),
  CONSTRAINT `fk_historico_plano` FOREIGN KEY (`plano_manutencao_id`) REFERENCES `planos_manutencao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_os` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
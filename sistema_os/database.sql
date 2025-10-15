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
  `data_ultima_preventiva` datetime DEFAULT NULL,
  `data_proxima_preventiva` datetime NOT NULL,
  `instrucoes` text DEFAULT NULL COMMENT 'Checklist e instruções da manutenção',
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipamento_id` (`equipamento_id`) COMMENT 'Um plano por equipamento',
  CONSTRAINT `fk_planos_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipos_manutencao`
-- Armazena os tipos de manutenção disponíveis.
--

CREATE TABLE `tipos_manutencao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Inserindo dados de exemplo para `tipos_manutencao`
--
INSERT INTO `tipos_manutencao` (`id`, `nome`) VALUES
(1, 'Corretiva'),
(2, 'Preventiva'),
(3, 'Melhoria'),
(4, 'Preditiva');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tecnicos`
-- Armazena os técnicos responsáveis pela execução das manutenções.
--

CREATE TABLE `tecnicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula` (`matricula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserindo dados de exemplo para `tecnicos`
INSERT INTO `tecnicos` (`id`, `nome`, `matricula`, `status`) VALUES
(1, 'João da Silva', '1001', 'Ativo'),
(2, 'Maria Oliveira', '1002', 'Ativo'),
(3, 'Carlos Pereira', '1003', 'Ativo');

--
-- Estrutura da tabela `ordens_servico`
-- Tabela principal que armazena todas as ordens de serviço.
--

CREATE TABLE `ordens_servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_os` varchar(20) NOT NULL,
  `equipamento_id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `tipo_manutencao_id` int(11) NOT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `area_manutencao` varchar(50) DEFAULT NULL,
  `solicitante` varchar(100) DEFAULT NULL,
  `horas_estimadas` decimal(10,2) DEFAULT 1.00,
  `prioridade` enum('Baixa','Média','Alta','Urgente') NOT NULL DEFAULT 'Média',
  `status` enum('Aberta','Concluída') NOT NULL DEFAULT 'Aberta',
  `data_inicial` datetime NOT NULL,
  `data_final` datetime DEFAULT NULL,
  `descricao_problema` text DEFAULT NULL,
  `descricao_servico` text DEFAULT NULL,
  `custo_pecas` decimal(10,2) DEFAULT 0.00,
  `custo_mao_de_obra` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_os` (`numero_os`),
  KEY `equipamento_id` (`equipamento_id`),
  KEY `setor_id` (`setor_id`),
  KEY `tipo_manutencao_id` (`tipo_manutencao_id`),
  KEY `tecnico_id` (`tecnico_id`),
  CONSTRAINT `fk_os_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT `fk_os_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`),
  CONSTRAINT `fk_os_tipo_manutencao` FOREIGN KEY (`tipo_manutencao_id`) REFERENCES `tipos_manutencao` (`id`),
  CONSTRAINT `fk_os_tecnico` FOREIGN KEY (`tecnico_id`) REFERENCES `tecnicos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
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
  `data_realizacao` datetime NOT NULL,
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plano_manutencao_id` (`plano_manutencao_id`),
  KEY `ordem_servico_id` (`ordem_servico_id`),
  CONSTRAINT `fk_historico_plano` FOREIGN KEY (`plano_manutencao_id`) REFERENCES `planos_manutencao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_os` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `solicitacoes_servico`
-- Armazena as solicitações de serviço feitas pelos usuários antes de se tornarem O.S.
--

CREATE TABLE `solicitacoes_servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipamento_id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `solicitante` varchar(100) NOT NULL,
  `descricao_problema` text NOT NULL,
  `data_solicitacao` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pendente','Aprovada','Rejeitada') NOT NULL DEFAULT 'Pendente',
  `ordem_servico_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipamento_id` (`equipamento_id`),
  KEY `setor_id` (`setor_id`),
  KEY `ordem_servico_id` (`ordem_servico_id`),
  CONSTRAINT `fk_solicitacao_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_solicitacao_os` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_solicitacao_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
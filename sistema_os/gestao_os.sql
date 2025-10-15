-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/10/2025 às 10:28
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gestao_os`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipamentos`
--

CREATE TABLE `equipamentos` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `setor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `equipamentos`
--

INSERT INTO `equipamentos` (`id`, `tag`, `nome`, `setor_id`) VALUES
(58, '21', 'Seladora Automática 4', 6),
(59, '25', 'Setor Da Embalagem Primária', 6),
(61, '182', 'Máquina De Costura 2', 6),
(62, '183', 'Climatizadores', 6),
(63, '184', 'Flowpack 1', 6),
(64, '185', 'Flowpack 2', 6),
(65, '186', 'Túnel De Encolhimento', 6),
(66, '187', 'Seladora Semi Automática 1', 6),
(67, '188', 'Seladora Semi Automática 2', 6),
(68, '189', 'Detector De Metais', 6),
(69, '190', 'Motor Detector De Metais', 6),
(71, '192', 'Fechadora De Caixa 1', 6),
(72, '193', 'Fechadora De Caixas', 6),
(73, '194', 'Fechadora De Caixa 3', 6),
(74, '195', 'Seladora Cetro', 6),
(75, '196', 'Seladora Registron', 6),
(76, '197', 'Seladora Cetro', 6),
(77, '198', 'Seladora Em L', 6),
(78, '199', 'Máquina De Costura 1', 6),
(79, '200', 'Balança 50Kg', 6),
(91, '212', 'Seladora Pedal', 6),
(92, '215', 'Mesa Inox 1', 6),
(93, '216', 'Mesa Inox 2', 6),
(94, '217', 'Mesa Inox 3', 6),
(102, '424', 'Impressora Tesla', 6),
(103, '427', 'Seladora Registron', 6);

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_preventivas`
--

CREATE TABLE `historico_preventivas` (
  `id` int(11) NOT NULL,
  `plano_manutencao_id` int(11) NOT NULL,
  `ordem_servico_id` int(11) DEFAULT NULL,
  `data_realizacao` datetime NOT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_preventivas`
--

INSERT INTO `historico_preventivas` (`id`, `plano_manutencao_id`, `ordem_servico_id`, `data_realizacao`, `observacoes`) VALUES
(6, 4, 30, '2025-10-11 16:57:00', NULL),
(7, 3, 31, '2025-10-11 17:00:00', NULL),
(8, 3, 32, '2025-10-11 17:00:00', NULL),
(9, 3, 34, '2025-10-11 17:01:00', NULL),
(10, 4, 40, '2025-10-11 17:27:00', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ordens_servico`
--

CREATE TABLE `ordens_servico` (
  `id` int(11) NOT NULL,
  `numero_os` varchar(20) NOT NULL,
  `equipamento_id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `tipo_manutencao_id` int(11) NOT NULL,
  `area_manutencao` varchar(50) DEFAULT NULL,
  `solicitante` varchar(100) DEFAULT NULL,
  `horas_estimadas` decimal(10,2) DEFAULT 1.00,
  `prioridade` enum('Baixa','Média','Alta','Urgente') NOT NULL DEFAULT 'Média',
  `status` enum('Aberta','Em Andamento','Aguardando Peças','Concluída','Cancelada') NOT NULL DEFAULT 'Aberta',
  `data_inicial` datetime DEFAULT NULL,
  `data_final` datetime DEFAULT NULL,
  `descricao_problema` text DEFAULT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `descricao_servico` text DEFAULT NULL,
  `custo_pecas` decimal(10,2) DEFAULT 0.00,
  `custo_mao_de_obra` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ordens_servico`
--

INSERT INTO `ordens_servico` (`id`, `numero_os`, `equipamento_id`, `setor_id`, `tipo_manutencao_id`, `area_manutencao`, `solicitante`, `horas_estimadas`, `prioridade`, `status`, `data_inicial`, `data_final`, `descricao_problema`, `tecnico_id`, `descricao_servico`, `custo_pecas`, `custo_mao_de_obra`) VALUES
(1, '00001-2025-10-08', 61, 6, 1, 'Mecânica', 'Linea', 1.00, 'Baixa', 'Concluída', '2025-10-01 20:20:00', '2025-10-01 21:21:00', 'nao funciona', NULL, NULL, 0.00, 0.00),
(2, '00002-2025-10-08', 63, 6, 1, 'Elétrica', 'Linea', 1.00, 'Média', 'Concluída', '2025-10-02 20:23:00', '2025-10-02 22:23:00', 'nao sela', NULL, NULL, 0.00, 0.00),
(3, '00003-2025-10-08', 64, 6, 1, 'Mecânica', 'Linea', 1.50, 'Média', 'Concluída', '2025-10-02 20:24:00', '2025-10-02 21:24:00', 'esta vazando', NULL, NULL, 0.00, 0.00),
(4, '00004-2025-10-08', 63, 6, 1, 'Mecânica', 'Linea', 2.00, 'Baixa', 'Concluída', '2025-10-03 20:25:00', '2025-10-03 23:25:00', 'asdfg', NULL, NULL, 0.00, 0.00),
(5, '00005-2025-10-08', 64, 6, 1, 'Elétrica', 'Linea', 0.50, 'Média', 'Concluída', '2025-10-05 20:26:00', '2025-10-05 21:26:00', 'aqswdefr', NULL, NULL, 0.00, 0.00),
(6, '00006-2025-10-08', 66, 6, 1, 'Mecânica', 'Fábio', 1.00, 'Média', 'Concluída', '2025-10-06 20:27:00', '2025-10-06 23:27:00', 'sderfgt', NULL, NULL, 0.00, 0.00),
(8, '00008-2025-10-08', 75, 6, 1, 'Mecânica', 'Alexandre', 1.00, 'Média', 'Concluída', '2025-09-28 20:30:00', '2025-09-28 21:30:00', 'vfgrtyhh', NULL, NULL, 0.00, 0.00),
(9, '00009-2025-10-08', 64, 6, 1, 'Mecânica', 'Robson', 1.00, 'Média', 'Concluída', '0000-00-00 00:00:00', '2025-10-10 16:36:00', 'rtfgyhuj', 2, NULL, 0.00, 0.00),
(13, '00013-2025-10-08', 61, 6, 1, 'Elétrica', 'Alexandre', 0.50, 'Baixa', 'Concluída', '0000-00-00 00:00:00', '2025-10-08 16:37:00', 'vfgtyhuju', NULL, NULL, 0.00, 0.00),
(16, '00016-2025-10-08', 72, 6, 1, 'Mecânica', 'Linea', 1.00, 'Baixa', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 16:35:00', 'cdghjkl', NULL, NULL, 0.00, 0.00),
(18, '00018-2025-10-08', 103, 6, 1, 'Elétrica', 'Fábio', 1.00, 'Baixa', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 13:50:00', 'nbjhuio', 3, NULL, 0.00, 0.00),
(19, '00019-2025-10-08', 79, 6, 1, 'Elétrica', 'Claudio', 1.00, 'Baixa', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 13:46:00', 'fgtyuio', NULL, NULL, 0.00, 0.00),
(29, '00020-2025-10-11', 62, 6, 1, 'Mecânica', 'Linea', 1.00, 'Baixa', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 16:50:00', 'nanananaanan', 1, NULL, 0.00, 0.00),
(30, '00030-2025-10-11', 71, 6, 2, 'Mecânica', 'Claudio', 1.00, 'Média', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 16:57:00', 'lubrificacao', 2, NULL, 0.00, 0.00),
(31, '00031-2025-10-11', 61, 6, 2, 'Mecânica', 'Linea', 1.00, 'Baixa', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 17:00:00', 'qqqqqqqqqqq', 3, NULL, 0.00, 0.00),
(32, '00032-2025-10-11', 61, 6, 2, 'Elétrica', 'Fábio', 1.00, 'Média', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 17:00:00', 'qqqqqqqqqqqq', 3, NULL, 0.00, 0.00),
(33, '00033-2025-10-11', 61, 2, 2, 'Elétrica', 'Robson', 1.00, 'Média', 'Aberta', '2025-10-11 16:58:00', NULL, 'qqqqqqqqqq', 2, NULL, 0.00, 0.00),
(34, '00034-2025-10-11', 61, 6, 2, 'Elétrica', 'Linea', 1.00, 'Alta', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 17:01:00', 'sssssssss', 1, NULL, 0.00, 0.00),
(35, '00035-2025-10-11', 61, 6, 1, 'Hidráulica', 'Linea', 2.00, 'Média', 'Aberta', '2025-10-02 17:11:00', NULL, 'aaaaaaaaaaaaaaaaaa', 3, NULL, 0.00, 0.00),
(36, '00036-2025-10-11', 63, 6, 3, 'Elétrica', 'Alexandre', 0.50, 'Média', 'Aberta', '2025-10-03 17:12:00', NULL, 'fffffff', 2, NULL, 0.00, 0.00),
(37, '00037-2025-10-11', 64, 6, 2, 'Mecânica', 'Claudio', 2.00, 'Alta', 'Aberta', '2025-10-05 17:12:00', NULL, 'ggggggggggggggggggg', 3, NULL, 0.00, 0.00),
(38, '00038-2025-10-11', 63, 6, 2, 'Elétrica', 'Claudio', 1.00, 'Baixa', 'Aberta', '2025-10-07 17:13:00', NULL, 'ffffffffffff', 1, NULL, 0.00, 0.00),
(39, '00039-2025-10-11', 62, 6, 2, 'Hidráulica', 'Alexandre', 1.00, 'Baixa', 'Aberta', '2025-10-09 17:16:00', NULL, 'ffffff', 3, NULL, 0.00, 0.00),
(40, '00040-2025-10-11', 71, 6, 2, 'TI', 'Linea', 1.00, 'Média', 'Concluída', '0000-00-00 00:00:00', '2025-10-11 17:27:00', 'zzzzzzzz', 1, NULL, 0.00, 0.00),
(41, '00041-2025-10-11', 73, 6, 2, 'Mecânica', 'Linea', 1.00, 'Baixa', 'Aberta', '2025-10-05 17:32:00', NULL, 'fffffffffff', 1, NULL, 0.00, 0.00),
(42, '00042-2025-10-11', 68, 6, 2, 'Elétrica', 'Fábio', 1.00, 'Média', 'Aberta', '2025-10-07 17:32:00', NULL, 'tttttt', 1, NULL, 0.00, 0.00),
(43, '00043-2025-10-11', 102, 6, 2, 'Hidráulica', 'Fábio', 1.00, 'Média', 'Aberta', '2025-10-09 17:33:00', NULL, 'gggggggggg', 1, NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_manutencao`
--

CREATE TABLE `planos_manutencao` (
  `id` int(11) NOT NULL,
  `equipamento_id` int(11) NOT NULL,
  `periodicidade` varchar(50) NOT NULL COMMENT 'Ex: Mensal, Trimestral, 30 dias',
  `data_ultima_preventiva` datetime DEFAULT NULL,
  `data_proxima_preventiva` datetime DEFAULT NULL,
  `instrucoes` text DEFAULT NULL COMMENT 'Checklist e instruções da manutenção'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `planos_manutencao`
--

INSERT INTO `planos_manutencao` (`id`, `equipamento_id`, `periodicidade`, `data_ultima_preventiva`, `data_proxima_preventiva`, `instrucoes`) VALUES
(3, 61, 'Semanal', '2025-10-11 17:01:00', '2025-10-18 17:01:00', 'Lubrificar'),
(4, 71, 'Mensal', '2025-10-11 17:27:00', '2025-11-10 17:27:00', 'lubrificar'),
(5, 64, 'Mensal', '2025-09-01 17:29:00', '2025-10-01 17:29:00', 'limpeza'),
(6, 75, 'Semanal', '2025-09-30 17:30:00', '2025-10-07 17:30:00', 'limpeza');

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `setores`
--

INSERT INTO `setores` (`id`, `nome`) VALUES
(5, 'Administrativo'),
(6, 'Embalagem'),
(2, 'Manutenção Elétrica'),
(3, 'Manutenção Mecânica'),
(1, 'Produção'),
(4, 'Utilidades');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacoes_servico`
--

CREATE TABLE `solicitacoes_servico` (
  `id` int(11) NOT NULL,
  `equipamento_id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `solicitante` varchar(255) NOT NULL,
  `descricao_problema` text NOT NULL,
  `data_solicitacao` datetime NOT NULL,
  `status` enum('Pendente','Aprovada','Rejeitada') NOT NULL DEFAULT 'Pendente',
  `observacao_gestor` text DEFAULT NULL,
  `ordem_servico_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tecnicos`
--

CREATE TABLE `tecnicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tecnicos`
--

INSERT INTO `tecnicos` (`id`, `nome`, `matricula`, `status`) VALUES
(1, 'Claudio Ramos', '1001', 'Ativo'),
(2, 'Fabio Junior', '1002', 'Ativo'),
(3, 'Alexandro', '1003', 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos_manutencao`
--

CREATE TABLE `tipos_manutencao` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipos_manutencao`
--

INSERT INTO `tipos_manutencao` (`id`, `nome`) VALUES
(1, 'Corretiva'),
(3, 'Melhoria'),
(4, 'Preditiva'),
(2, 'Preventiva');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_manutencao`
--

CREATE TABLE `tipo_manutencao` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipo_manutencao`
--

INSERT INTO `tipo_manutencao` (`id`, `nome`) VALUES
(1, 'Corretiva'),
(4, 'Melhoria'),
(3, 'Preditiva'),
(2, 'Preventiva');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`),
  ADD KEY `setor_id` (`setor_id`);

--
-- Índices de tabela `historico_preventivas`
--
ALTER TABLE `historico_preventivas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plano_manutencao_id` (`plano_manutencao_id`),
  ADD KEY `ordem_servico_id` (`ordem_servico_id`);

--
-- Índices de tabela `ordens_servico`
--
ALTER TABLE `ordens_servico`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_os` (`numero_os`),
  ADD KEY `equipamento_id` (`equipamento_id`),
  ADD KEY `setor_id` (`setor_id`),
  ADD KEY `tipo_manutencao_id` (`tipo_manutencao_id`);

--
-- Índices de tabela `planos_manutencao`
--
ALTER TABLE `planos_manutencao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipamento_id` (`equipamento_id`) COMMENT 'Um plano por equipamento';

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `solicitacoes_servico`
--
ALTER TABLE `solicitacoes_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipamento_id` (`equipamento_id`),
  ADD KEY `setor_id` (`setor_id`),
  ADD KEY `ordem_servico_id` (`ordem_servico_id`);

--
-- Índices de tabela `tecnicos`
--
ALTER TABLE `tecnicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula` (`matricula`);

--
-- Índices de tabela `tipos_manutencao`
--
ALTER TABLE `tipos_manutencao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `tipo_manutencao`
--
ALTER TABLE `tipo_manutencao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_UNIQUE` (`nome`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `equipamentos`
--
ALTER TABLE `equipamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de tabela `historico_preventivas`
--
ALTER TABLE `historico_preventivas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `ordens_servico`
--
ALTER TABLE `ordens_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de tabela `planos_manutencao`
--
ALTER TABLE `planos_manutencao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `solicitacoes_servico`
--
ALTER TABLE `solicitacoes_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `tecnicos`
--
ALTER TABLE `tecnicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tipos_manutencao`
--
ALTER TABLE `tipos_manutencao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tipo_manutencao`
--
ALTER TABLE `tipo_manutencao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `equipamentos`
--
ALTER TABLE `equipamentos`
  ADD CONSTRAINT `fk_equipamentos_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `historico_preventivas`
--
ALTER TABLE `historico_preventivas`
  ADD CONSTRAINT `fk_historico_os` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historico_plano` FOREIGN KEY (`plano_manutencao_id`) REFERENCES `planos_manutencao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `ordens_servico`
--
ALTER TABLE `ordens_servico`
  ADD CONSTRAINT `fk_os_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`),
  ADD CONSTRAINT `fk_os_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`),
  ADD CONSTRAINT `fk_os_tipo_manutencao` FOREIGN KEY (`tipo_manutencao_id`) REFERENCES `tipos_manutencao` (`id`);

--
-- Restrições para tabelas `planos_manutencao`
--
ALTER TABLE `planos_manutencao`
  ADD CONSTRAINT `fk_planos_equipamento` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `solicitacoes_servico`
--
ALTER TABLE `solicitacoes_servico`
  ADD CONSTRAINT `solicitacoes_servico_ibfk_1` FOREIGN KEY (`equipamento_id`) REFERENCES `equipamentos` (`id`),
  ADD CONSTRAINT `solicitacoes_servico_ibfk_2` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`),
  ADD CONSTRAINT `solicitacoes_servico_ibfk_3` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

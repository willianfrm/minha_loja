-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28-Maio-2026 às 10:00
-- Versão do servidor: 10.4.21-MariaDB
-- versão do PHP: 7.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_loja2`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categoria`
--

CREATE TABLE `categoria` (
  `codigo` int(11) NOT NULL,
  `descricao` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_cat_pai` int(11) DEFAULT NULL,
  `nivel` int(11) NOT NULL COMMENT '1=categoria 2=subcategoria 3=grupo 4=subgrupo',
  `margem` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `documento_movimentacao`
--

CREATE TABLE `documento_movimentacao` (
  `codigo` int(11) NOT NULL,
  `codigo_motivo` int(11) NOT NULL,
  `data_hora` datetime NOT NULL,
  `valor_total` decimal(12,2) DEFAULT 0.00,
  `codigo_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `documento_pagamentos`
--

CREATE TABLE `documento_pagamentos` (
  `codigo_documento` int(11) NOT NULL,
  `codigo_metodo_pagamento` int(11) NOT NULL,
  `valor` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `estoque`
--

CREATE TABLE `estoque` (
  `codigo_produto` int(11) NOT NULL,
  `estoque` decimal(15,3) DEFAULT 0.000,
  `custo_ult_entrada` decimal(10,4) DEFAULT 0.0000,
  `custo_medio` decimal(10,4) DEFAULT 0.0000,
  `preco_vigente` decimal(10,2) DEFAULT 0.00,
  `preco_oferta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `familia`
--

CREATE TABLE `familia` (
  `codigo` int(11) NOT NULL,
  `codigo_categoria` int(11) NOT NULL,
  `descricao` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `embalagem` enum('UN','KG') COLLATE utf8mb4_unicode_ci NOT NULL,
  `margem` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `metodo_pagamento`
--

CREATE TABLE `metodo_pagamento` (
  `codigo` int(11) NOT NULL,
  `descricao` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `motivo_movimentacoes`
--

CREATE TABLE `motivo_movimentacoes` (
  `codigo` int(11) NOT NULL,
  `nome_motivo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao_motivo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('E','S') COLLATE utf8mb4_unicode_ci NOT NULL,
  `atualiza_custo` tinyint(1) DEFAULT NULL COMMENT 'Usado apenas para tipo E',
  `tipo_valor` enum('PRECO_VENDA','CUSTO','MANUAL') COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `movimentacao_produto`
--

CREATE TABLE `movimentacao_produto` (
  `id` int(11) NOT NULL,
  `codigo_documento` int(11) NOT NULL,
  `codigo_produto` int(11) NOT NULL,
  `quantidade` decimal(15,3) NOT NULL,
  `valor_unitario` decimal(10,4) NOT NULL,
  `valor_total` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `oferta`
--

CREATE TABLE `oferta` (
  `codigo` int(11) NOT NULL,
  `descricao` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `codigo_usuario` int(11) NOT NULL,
  `atualizado` tinyint(1) DEFAULT 0,
  `codigo_usuario_carga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `oferta_precos`
--

CREATE TABLE `oferta_precos` (
  `codigo_oferta` int(11) NOT NULL,
  `codigo_produto` int(11) NOT NULL,
  `preco_oferta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `parametros_sistema`
--

CREATE TABLE `parametros_sistema` (
  `id` int(11) NOT NULL,
  `tipo_custo_precificacao` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_custo_relatorio` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto`
--

CREATE TABLE `produto` (
  `codigo` int(11) NOT NULL,
  `codigo_familia` int(11) NOT NULL,
  `descricao_complemento` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linha` tinyint(1) DEFAULT 1 COMMENT 'true=em linha false=fora de linha'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto_eans`
--

CREATE TABLE `produto_eans` (
  `codigo_produto` int(11) NOT NULL,
  `codigo_ean` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `programacao_preco`
--

CREATE TABLE `programacao_preco` (
  `id` int(11) NOT NULL,
  `codigo_produto` int(11) NOT NULL,
  `preco_programado` decimal(10,2) NOT NULL,
  `data_entra_vigencia` date NOT NULL,
  `atualizado` tinyint(1) DEFAULT 0,
  `data_hora_atualizacao` datetime DEFAULT NULL,
  `codigo_usuario_programacao` int(11) DEFAULT NULL,
  `codigo_usuario_carga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE `usuario` (
  `codigo` int(11) NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opera_pdv` tinyint(1) DEFAULT 0,
  `admin_pdv` tinyint(1) DEFAULT 0,
  `admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `fk_categoria_pai` (`codigo_cat_pai`);

--
-- Índices para tabela `documento_movimentacao`
--
ALTER TABLE `documento_movimentacao`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `codigo_motivo` (`codigo_motivo`),
  ADD KEY `codigo_usuario` (`codigo_usuario`);

--
-- Índices para tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`codigo_produto`);

--
-- Índices para tabela `familia`
--
ALTER TABLE `familia`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `fk_familia_categoria` (`codigo_categoria`);

--
-- Índices para tabela `metodo_pagamento`
--
ALTER TABLE `metodo_pagamento`
  ADD PRIMARY KEY (`codigo`);

--
-- Índices para tabela `motivo_movimentacoes`
--
ALTER TABLE `motivo_movimentacoes`
  ADD PRIMARY KEY (`codigo`);

--
-- Índices para tabela `movimentacao_produto`
--
ALTER TABLE `movimentacao_produto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `codigo_documento` (`codigo_documento`),
  ADD KEY `codigo_produto` (`codigo_produto`);

--
-- Índices para tabela `oferta`
--
ALTER TABLE `oferta`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `codigo_usuario` (`codigo_usuario`),
  ADD KEY `codigo_usuario_carga` (`codigo_usuario_carga`);

--
-- Índices para tabela `oferta_precos`
--
ALTER TABLE `oferta_precos`
  ADD PRIMARY KEY (`codigo_oferta`,`codigo_produto`),
  ADD KEY `codigo_produto` (`codigo_produto`);

--
-- Índices para tabela `parametros_sistema`
--
ALTER TABLE `parametros_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `fk_produto_familia` (`codigo_familia`);

--
-- Índices para tabela `produto_eans`
--
ALTER TABLE `produto_eans`
  ADD PRIMARY KEY (`codigo_produto`,`codigo_ean`);

--
-- Índices para tabela `programacao_preco`
--
ALTER TABLE `programacao_preco`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prog_preco_produto` (`codigo_produto`),
  ADD KEY `codigo_usuario_programacao` (`codigo_usuario_programacao`),
  ADD KEY `codigo_usuario_carga` (`codigo_usuario_carga`);

--
-- Índices para tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`codigo`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `documento_movimentacao`
--
ALTER TABLE `documento_movimentacao`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `familia`
--
ALTER TABLE `familia`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `metodo_pagamento`
--
ALTER TABLE `metodo_pagamento`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `motivo_movimentacoes`
--
ALTER TABLE `motivo_movimentacoes`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movimentacao_produto`
--
ALTER TABLE `movimentacao_produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `oferta`
--
ALTER TABLE `oferta`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `programacao_preco`
--
ALTER TABLE `programacao_preco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `codigo` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `fk_categoria_pai` FOREIGN KEY (`codigo_cat_pai`) REFERENCES `categoria` (`codigo`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `documento_movimentacao`
--
ALTER TABLE `documento_movimentacao`
  ADD CONSTRAINT `documento_movimentacao_ibfk_1` FOREIGN KEY (`codigo_motivo`) REFERENCES `motivo_movimentacoes` (`codigo`),
  ADD CONSTRAINT `documento_movimentacao_ibfk_3` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo`);

--
-- Limitadores para a tabela `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`codigo_produto`) REFERENCES `produto` (`codigo`);

--
-- Limitadores para a tabela `familia`
--
ALTER TABLE `familia`
  ADD CONSTRAINT `fk_familia_categoria` FOREIGN KEY (`codigo_categoria`) REFERENCES `categoria` (`codigo`);

--
-- Limitadores para a tabela `movimentacao_produto`
--
ALTER TABLE `movimentacao_produto`
  ADD CONSTRAINT `movimentacao_produto_ibfk_1` FOREIGN KEY (`codigo_documento`) REFERENCES `documento_movimentacao` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacao_produto_ibfk_2` FOREIGN KEY (`codigo_produto`) REFERENCES `produto` (`codigo`);

--
-- Limitadores para a tabela `oferta`
--
ALTER TABLE `oferta`
  ADD CONSTRAINT `oferta_ibfk_1` FOREIGN KEY (`codigo_usuario`) REFERENCES `usuario` (`codigo`),
  ADD CONSTRAINT `oferta_ibfk_2` FOREIGN KEY (`codigo_usuario_carga`) REFERENCES `usuario` (`codigo`);

--
-- Limitadores para a tabela `oferta_precos`
--
ALTER TABLE `oferta_precos`
  ADD CONSTRAINT `oferta_precos_ibfk_1` FOREIGN KEY (`codigo_oferta`) REFERENCES `oferta` (`codigo`) ON DELETE CASCADE,
  ADD CONSTRAINT `oferta_precos_ibfk_2` FOREIGN KEY (`codigo_produto`) REFERENCES `produto` (`codigo`);

--
-- Limitadores para a tabela `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `fk_produto_familia` FOREIGN KEY (`codigo_familia`) REFERENCES `familia` (`codigo`);

--
-- Limitadores para a tabela `produto_eans`
--
ALTER TABLE `produto_eans`
  ADD CONSTRAINT `fk_ean_produto` FOREIGN KEY (`codigo_produto`) REFERENCES `produto` (`codigo`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `programacao_preco`
--
ALTER TABLE `programacao_preco`
  ADD CONSTRAINT `fk_prog_preco_produto` FOREIGN KEY (`codigo_produto`) REFERENCES `produto` (`codigo`),
  ADD CONSTRAINT `programacao_preco_ibfk_1` FOREIGN KEY (`codigo_usuario_programacao`) REFERENCES `usuario` (`codigo`),
  ADD CONSTRAINT `programacao_preco_ibfk_2` FOREIGN KEY (`codigo_usuario_carga`) REFERENCES `usuario` (`codigo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

---
--- Inserts Padrão do sistema
---

-- Metodos de pagamentos
INSERT INTO `metodo_pagamento` (`codigo`, `descricao`, `ativo`) VALUES
(1, 'Dinheiro', 1),
(2, 'Pix', 1),
(3, 'Cartão de crédito', 1),
(4, 'Cartão de débito', 1);

-- Motivos de movimentações
INSERT INTO `motivo_movimentacoes` (`codigo`, `nome_motivo`, `descricao_motivo`, `tipo`, `atualiza_custo`, `tipo_valor`) VALUES
(1, 'Venda', 'Venda de mercadoria via PDV.', 'S', NULL, 'PRECO_VENDA'),
(2, 'Compra para revenda', 'Compra de mercadoria para revenda.', 'E', 1, 'MANUAL');

-- Parametros do sistemaINSERT INTO `parametros_sistema` (`id`, `tipo_custo_precificacao`, `tipo_custo_relatorio`) VALUES
(1, 'ULTIMA_ENTRADA', 'MEDIO');

-- Usuário padrão admin (senha: admin)
INSERT INTO `usuario` (`codigo`, `login`, `senha`, `nome`, `opera_pdv`, `admin_pdv`, `admin`) VALUES
(1, 'admin', '$2y$10$oca2n3p66B1OiTktQEnxeONTWfjDT7N3n/kZXkdLpFjrLl7N7SAqa', 'Administrador', 1, 1, 1);
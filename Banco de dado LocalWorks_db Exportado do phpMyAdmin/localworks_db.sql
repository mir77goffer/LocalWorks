-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/11/2026 às 11:21
-- Versão do servidor: 10.11.16-MariaDB
-- Versão do PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `localworks_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--

CREATE TABLE `administrador` (
  `id_admin` int(10) UNSIGNED NOT NULL,
  `nivel_acesso` varchar(50) NOT NULL DEFAULT 'total'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `candidato`
--

CREATE TABLE `candidato` (
  `id_candidato` int(10) UNSIGNED NOT NULL,
  `area_interesse` varchar(100) NOT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `resumo` text DEFAULT NULL,
  `pretensao_salarial` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `candidato`
--

INSERT INTO `candidato` (`id_candidato`, `area_interesse`, `cidade`, `resumo`, `pretensao_salarial`) VALUES
(7, 'Técnico em Desenvolvimento de Sistemas/Tecnologia da Informação', 'Campinas SP', 'Sou Técnico em Desenvolvimento de Sistemas formado pelo Etec - Centro Paula Souza e possuo experiência em análise, desenvolvimento e manutenção de aplicações. \r\nTenho uma sólida base em programação orientada a objetos, bancos de dados relacionais e metodologias ágeis, além de interesse constante em aprender novas tecnologias e aprimorar minhas habilidades.', 3700.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `candidatura`
--

CREATE TABLE `candidatura` (
  `id_candidatura` int(10) UNSIGNED NOT NULL,
  `id_oportunidade` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `mensagem` text DEFAULT NULL,
  `status` enum('enviada','aceita','recusada') NOT NULL DEFAULT 'enviada',
  `data_candidatura` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `candidatura`
--

INSERT INTO `candidatura` (`id_candidatura`, `id_oportunidade`, `id_usuario`, `mensagem`, `status`, `data_candidatura`) VALUES
(1, 1, 7, 'Meu nome é Ademir Santos Neves Sou Técnico em Desenvolvimento de Sistemas formado pelo Etec - Centro Paula Souza e possuo experiência em análise, desenvolvimento e manutenção de aplicações. \r\nTenho uma sólida base em programação orientada a objetos, bancos de dados relacionais e metodologias ágeis, além de interesse constante em aprender novas tecnologias e aprimorar minhas habilidades.', 'enviada', '2026-11-01 05:21:48'),
(2, 2, 7, 'Meu nome é Armindo Joaquim Neves sou Eletricista de Manutenção Eletroeletrônica .\r\nMinha Formação: \r\n• Curso Técnico em Eletrotécnica – SENAI Campinas (2012) \r\n• Certificação NR-10 (Segurança em Instalações e Serviços com Eletricidade) \r\n• Certificação NR-35 (Trabalho em Altura)', 'enviada', '2026-11-02 05:27:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `aprovada` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nome`, `descricao`, `aprovada`) VALUES
(1, 'Eletricista', 'Serviços elétricos', 1),
(2, 'Diarista', 'Limpeza e serviços domésticos', 1),
(3, 'Encanador', 'Serviços hidráulicos', 1),
(4, 'Pedreiro', 'Construção e reformas', 1),
(5, 'Pintor', 'Pintura de ambientes', 1),
(6, 'Jardineiro', 'Jardinagem e paisagismo', 1),
(7, 'Desenvolvedor', 'Aplicativos, Sites e Banco de Dados', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratante`
--

CREATE TABLE `contratante` (
  `id_contratante` int(10) UNSIGNED NOT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `contratante`
--

INSERT INTO `contratante` (`id_contratante`, `cnpj`, `empresa`) VALUES
(1, 'CNPJ: 45.040.186/0', 'DevSpark & desenvolvimento de software.'),
(2, '12.345.678/0001-99', 'Luz & Força Energia'),
(5, '12.345.678/0001-90', 'NovaEstrutura Engenharia e Construções Ltda.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `oportunidade`
--

CREATE TABLE `oportunidade` (
  `id_oportunidade` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `localizacao` varchar(100) DEFAULT NULL,
  `valor_proposto` decimal(10,2) DEFAULT NULL,
  `publicada` tinyint(1) NOT NULL DEFAULT 1,
  `data_publicacao` datetime NOT NULL DEFAULT current_timestamp(),
  `id_contratante` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `oportunidade`
--

INSERT INTO `oportunidade` (`id_oportunidade`, `titulo`, `descricao`, `localizacao`, `valor_proposto`, `publicada`, `data_publicacao`, `id_contratante`, `id_categoria`) VALUES
(1, 'Desenvolvedor de sistemas', 'O profissional será responsável por criar, testar, atualizar, manter e melhorar programas, aplicativos e softwares utilizados em computadores, celulares, sites e sistemas empresariais, atendendo às necessidades de empresas e usuários finais.', 'Campinas SP', 3500.00, 1, '2026-10-30 21:32:31', 1, 7),
(2, 'Eletricista de Manutenção', 'Realizar instalações elétricas prediais/industriais (ajuste conforme sua necessidade).\r\nExecutar manutenção preventiva e corretiva em sistemas elétricos.\r\nLer e interpretar projetos elétricos. Garantir o cumprimento das normas de segurança.', 'Campinas SP', 3202.00, 1, '2026-10-30 23:08:08', 2, 1),
(3, 'Profissional da construção civil', 'Principais Atividades\r\n•	Executar serviços de alvenaria, reboco e assentamento de pisos e revestimentos.\r\n•	Construir e reparar estruturas de concreto.\r\n•	Interpretar projetos e orientações técnicas.\r\n•	Garantir a qualidade e segurança na execução das atividades.\r\n•	Trabalhar em equipe para cumprimento dos prazos da obra.', 'Campinas SP', 2800.00, 1, '2026-11-01 03:48:10', 5, 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `servico`
--

CREATE TABLE `servico` (
  `id_servico` int(10) UNSIGNED NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_servico` date NOT NULL,
  `id_trabalhador` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `servico`
--

INSERT INTO `servico` (`id_servico`, `descricao`, `valor`, `data_servico`, `id_trabalhador`, `id_categoria`) VALUES
(1, 'Criação do Site LocalWorks. Plataforma Digital de Apoio ao Trabalho Local.', 15000.00, '2026-10-31', 3, 7),
(2, 'Foi realizada a manutenção preventiva e corretiva em sistemas elétricos industriais e Instalação de painéis de distribuição e sistemas de automação na empresa do cliente.', 5600.00, '2026-10-31', 4, 1),
(3, 'Foi realizada a construção completa de um muro residencial, com acabamento em reboco e instalação de portão metálico. O serviço foi executado com atenção aos detalhes, utilizando materiais de qualidade e seguindo todas as especificações do cliente.  A obra foi entregue dentro do prazo combinado, garantindo segurança, resistência e excelente acabamento estético. Mais um trabalho realizado com responsabilidade, compromisso e satisfação garantida.', 6700.00, '2026-11-01', 6, 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `trabalhador`
--

CREATE TABLE `trabalhador` (
  `id_trabalhador` int(10) UNSIGNED NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `especialidade` varchar(100) DEFAULT NULL,
  `area_atendimento` varchar(100) DEFAULT NULL,
  `descricao_perfil` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `trabalhador`
--

INSERT INTO `trabalhador` (`id_trabalhador`, `cpf`, `especialidade`, `area_atendimento`, `descricao_perfil`) VALUES
(3, '067.825.973.75', 'Técnico em Desenvolvimento de Sistemas', 'Tecnologia da Informação', 'Meu nome é Ademir Santos Neves . Sou Técnico em Desenvolvimento de Sistemas formado pelo Etec - Centro Paula Souza e possuo experiência em análise, desenvolvimento e manutenção de aplicações. \r\nTenho uma sólida base em programação orientada a objetos, bancos de dados relacionais e metodologias ágeis, além de interesse constante em aprender novas tecnologias e aprimorar minhas habilidades.'),
(4, '028.529.732-81', 'Eletricista de Manutenção Eletroeletrônica', 'Na Cidade de Campinas e a Região Metropolitana', 'Meu nome é Carlos Henrique da Silva sou Eletricista de Manutenção Eletroeletrônica .\r\nMinha Formação: \r\n• Curso Técnico em Eletrotécnica – SENAI Campinas (2012) \r\n• Certificação NR-10 (Segurança em Instalações e Serviços com Eletricidade) \r\n• Certificação NR-35 (Trabalho em Altura)'),
(6, '123.456.789-09', 'Construção e reforma de alvenaria residencial e comercial', 'Na Cidade de Campinas e a Região Metropolitana.', 'Sou Pedreiro Profissional - Qualidade e Confiança em Cada Obra:\r\nCom experiência em construção civil, ofereço serviços de alvenaria, reformas e acabamentos com compromisso, responsabilidade e atenção aos detalhes. Trabalho com foco na qualidade, cumprindo prazos e garantindo a satisfação de cada cliente.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `telefone`, `senha`) VALUES
(1, 'Consultoria e Transformação Digital.', 'mir.sn@hotmail.com', '19 9961-94205', '$2y$10$2gRpunxdk6guHIv65lgGoO2jGIbblJD0UVNhY0qiSmCYRil54dKIS'),
(2, 'Agência Energia total S.A.', 'contato@luzforca.com.br', '(19) 3456-7890', '$2y$10$RQQhr0JTh/t5twxTPrw.DugzPcERWo2xe4cDA0evsiwjpyY3HKODi'),
(3, 'Ademir Santos Neves', 'mir.sn.jales@gmail.com', '19 9814-88070', '$2y$10$idLs9MG4JOqAM6/mQNHQie4fjVsExfqpaABNbzWa.32k4oh3BypE2'),
(4, 'Carlos Henrique da Silva', 'carlos.silva@yahoo.com', '(19) 91234-5678', '$2y$10$ThylMS29iJTC9CxYfvFD7OXBOT3aXzTMyLUHlp9zJcsEemx1yQuYS'),
(5, 'Construindo hoje os espaços do futuro', 'contato@novaestrutura.com.br', '(19) 3333-4567', '$2y$10$nhjF3X6uWEvlIQcJ3Q21keZ5zan4ocTBGurj7wCLUd/I10eMIBdrC'),
(6, 'João da Silva', 'joaosilva.pedreiro@email.com', '(19) 99999-1234', '$2y$10$Q/oZdszoAYtriCu2sRPhXuW33d4lj8iQdLIlXcJ4j9yFLIeJQUKCO'),
(7, 'Armindo Joaquim Neves', 'armindo.jn@gmail.com', '(19) 9814-28078', '$2y$10$z7Qvpn52/hYjEugjTzYcEOl8S9nU9Gjj105Z80A6wMel6k3XvpqrO');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_admin`);

--
-- Índices de tabela `candidato`
--
ALTER TABLE `candidato`
  ADD PRIMARY KEY (`id_candidato`);

--
-- Índices de tabela `candidatura`
--
ALTER TABLE `candidatura`
  ADD PRIMARY KEY (`id_candidatura`),
  ADD UNIQUE KEY `uq_candidatura` (`id_oportunidade`,`id_usuario`),
  ADD KEY `fk_candidatura_usuario` (`id_usuario`);

--
-- Índices de tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `contratante`
--
ALTER TABLE `contratante`
  ADD PRIMARY KEY (`id_contratante`);

--
-- Índices de tabela `oportunidade`
--
ALTER TABLE `oportunidade`
  ADD PRIMARY KEY (`id_oportunidade`),
  ADD KEY `fk_oportunidade_contratante` (`id_contratante`),
  ADD KEY `fk_oportunidade_categoria` (`id_categoria`);

--
-- Índices de tabela `servico`
--
ALTER TABLE `servico`
  ADD PRIMARY KEY (`id_servico`),
  ADD KEY `fk_servico_trabalhador` (`id_trabalhador`),
  ADD KEY `fk_servico_categoria` (`id_categoria`);

--
-- Índices de tabela `trabalhador`
--
ALTER TABLE `trabalhador`
  ADD PRIMARY KEY (`id_trabalhador`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `candidatura`
--
ALTER TABLE `candidatura`
  MODIFY `id_candidatura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `oportunidade`
--
ALTER TABLE `oportunidade`
  MODIFY `id_oportunidade` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `servico`
--
ALTER TABLE `servico`
  MODIFY `id_servico` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `fk_administrador_usuario` FOREIGN KEY (`id_admin`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `candidato`
--
ALTER TABLE `candidato`
  ADD CONSTRAINT `fk_candidato_usuario` FOREIGN KEY (`id_candidato`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `candidatura`
--
ALTER TABLE `candidatura`
  ADD CONSTRAINT `fk_candidatura_oportunidade` FOREIGN KEY (`id_oportunidade`) REFERENCES `oportunidade` (`id_oportunidade`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_candidatura_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `contratante`
--
ALTER TABLE `contratante`
  ADD CONSTRAINT `fk_contratante_usuario` FOREIGN KEY (`id_contratante`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `oportunidade`
--
ALTER TABLE `oportunidade`
  ADD CONSTRAINT `fk_oportunidade_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oportunidade_contratante` FOREIGN KEY (`id_contratante`) REFERENCES `contratante` (`id_contratante`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `servico`
--
ALTER TABLE `servico`
  ADD CONSTRAINT `fk_servico_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_servico_trabalhador` FOREIGN KEY (`id_trabalhador`) REFERENCES `trabalhador` (`id_trabalhador`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `trabalhador`
--
ALTER TABLE `trabalhador`
  ADD CONSTRAINT `fk_trabalhador_usuario` FOREIGN KEY (`id_trabalhador`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

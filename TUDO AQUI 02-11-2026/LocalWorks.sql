-- Banco do módulo LocalWorks (baseado no LocalWorks_DB do TCC)
-- Versão com perfil Candidato e candidaturas.
CREATE DATABASE IF NOT EXISTS LocalWorks_DB
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE LocalWorks_DB;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    telefone   VARCHAR(20)  NULL,
    senha      VARCHAR(255) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS trabalhador (
    id_trabalhador   INT UNSIGNED PRIMARY KEY,
    cpf              VARCHAR(14)  NULL,
    especialidade    VARCHAR(100) NULL,
    area_atendimento VARCHAR(100) NULL,
    descricao_perfil TEXT NULL,
    CONSTRAINT fk_trabalhador_usuario
        FOREIGN KEY (id_trabalhador)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS contratante (
    id_contratante INT UNSIGNED PRIMARY KEY,
    cnpj           VARCHAR(18)  NULL,
    empresa        VARCHAR(100) NULL,
    CONSTRAINT fk_contratante_usuario
        FOREIGN KEY (id_contratante)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

-- Perfil de quem procura emprego
CREATE TABLE IF NOT EXISTS candidato (
    id_candidato       INT UNSIGNED PRIMARY KEY,
    area_interesse     VARCHAR(100) NOT NULL,
    cidade             VARCHAR(100) NULL,
    resumo             TEXT NULL,
    pretensao_salarial DECIMAL(10,2) NULL,
    CONSTRAINT fk_candidato_usuario
        FOREIGN KEY (id_candidato)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(100) NOT NULL UNIQUE,
    descricao    TEXT NULL,
    aprovada     TINYINT(1) NOT NULL DEFAULT 0
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS oportunidade (
    id_oportunidade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(100) NOT NULL,
    descricao       TEXT NULL,
    localizacao     VARCHAR(100) NULL,
    valor_proposto  DECIMAL(10,2) NULL,
    publicada       TINYINT(1) NOT NULL DEFAULT 1,
    data_publicacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_contratante  INT UNSIGNED NOT NULL,
    id_categoria    INT UNSIGNED NOT NULL,
    CONSTRAINT fk_oportunidade_contratante
        FOREIGN KEY (id_contratante)
        REFERENCES contratante (id_contratante)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_oportunidade_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categoria (id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS servico (
    id_servico     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descricao      TEXT NULL,
    valor          DECIMAL(10,2) NOT NULL,
    data_servico   DATE NOT NULL,
    id_trabalhador INT UNSIGNED NOT NULL,
    id_categoria   INT UNSIGNED NOT NULL,
    CONSTRAINT fk_servico_trabalhador
        FOREIGN KEY (id_trabalhador)
        REFERENCES trabalhador (id_trabalhador)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_servico_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categoria (id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS administrador (
    id_admin     INT UNSIGNED PRIMARY KEY,
    nivel_acesso VARCHAR(50) NOT NULL DEFAULT 'total',
    CONSTRAINT fk_administrador_usuario
        FOREIGN KEY (id_admin)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

-- Candidatura de um usuário (candidato ou trabalhador) a uma oportunidade
CREATE TABLE IF NOT EXISTS candidatura (
    id_candidatura   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_oportunidade  INT UNSIGNED NOT NULL,
    id_usuario       INT UNSIGNED NOT NULL,
    mensagem         TEXT NULL,
    status           ENUM('enviada', 'aceita', 'recusada') NOT NULL DEFAULT 'enviada',
    data_candidatura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_candidatura (id_oportunidade, id_usuario),
    CONSTRAINT fk_candidatura_oportunidade
        FOREIGN KEY (id_oportunidade)
        REFERENCES oportunidade (id_oportunidade)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_candidatura_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

INSERT IGNORE INTO categoria
    (id_categoria, nome, descricao, aprovada)
VALUES
    (1, 'Eletricista',   'Serviços elétricos', 1),
    (2, 'Diarista',      'Limpeza e serviços domésticos', 1),
    (3, 'Encanador',     'Serviços hidráulicos', 1),
    (4, 'Pedreiro',      'Construção e reformas', 1),
    (5, 'Pintor',        'Pintura de ambientes', 1),
    (6, 'Jardineiro',    'Jardinagem e paisagismo', 1),
    (7, 'Desenvolvedor', 'Aplicativos, Sites e Banco de Dados', 1);

-- Se o banco JÁ existia antes desta versão, NÃO rode este arquivo de novo.
-- Rode apenas o database/migracao_candidato.sql e, se faltarem as colunas
-- abaixo na tabela oportunidade, rode UMA vez:
--
-- ALTER TABLE categoria ADD UNIQUE (nome);
-- ALTER TABLE oportunidade
--     ADD COLUMN valor_proposto DECIMAL(10,2) NULL AFTER localizacao,
--     ADD COLUMN data_publicacao DATETIME NOT NULL
--         DEFAULT CURRENT_TIMESTAMP AFTER publicada;

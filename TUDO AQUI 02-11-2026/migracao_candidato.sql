-- Migração: perfil Candidato + candidaturas às oportunidades
-- Rode UMA vez no phpMyAdmin (aba SQL) com o banco LocalWorks_DB já existente.
-- É seguro rodar de novo: usa CREATE TABLE IF NOT EXISTS.

USE LocalWorks_DB;

-- Perfil de quem procura emprego (herda de usuario, igual trabalhador e contratante)
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

-- Para instalação NOVA, copie estes dois CREATE TABLE para o final do LocalWorks.sql
-- e COMENTE o ALTER TABLE oportunidade ... que já está no fim desse arquivo
-- (as colunas valor_proposto e data_publicacao já existem no CREATE TABLE).

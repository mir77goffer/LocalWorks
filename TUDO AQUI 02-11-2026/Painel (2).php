<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexao.php';

final class Painel
{
    private static function rodar(string $sql, array $params = []): PDOStatement
    {
        $stmt = Conexao::obter()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function publicarOportunidade(
        int $idContratante,
        string $titulo,
        string $descricao,
        string $local,
        int $idCategoria,
        ?float $valor = null
    ): void {
        self::rodar(
            'INSERT INTO oportunidade
                (titulo, descricao, localizacao, valor_proposto, publicada, id_contratante, id_categoria)
             VALUES (?, ?, ?, ?, 1, ?, ?)',
            [$titulo, $descricao, $local, $valor, $idContratante, $idCategoria]
        );
    }

    public static function minhasOportunidades(int $idContratante): array
    {
        return self::rodar(
            'SELECT
                o.id_oportunidade,
                o.titulo,
                o.localizacao,
                o.publicada,
                c.nome AS categoria
             FROM oportunidade o
             INNER JOIN categoria c
                ON c.id_categoria = o.id_categoria
             WHERE o.id_contratante = ?
             ORDER BY o.id_oportunidade DESC',
            [$idContratante]
        )->fetchAll();
    }

    public static function removerOportunidade(
        int $idOportunidade,
        int $idContratante
    ): void {
        self::rodar(
            'UPDATE oportunidade
             SET publicada = 0
             WHERE id_oportunidade = ?
               AND id_contratante = ?',
            [$idOportunidade, $idContratante]
        );
    }

    public static function registrarServico(
        int $idTrabalhador,
        string $descricao,
        float $valor,
        string $data,
        int $idCategoria
    ): void {
        self::rodar(
            'INSERT INTO servico
                (descricao, valor, data_servico, id_trabalhador, id_categoria)
             VALUES (?, ?, ?, ?, ?)',
            [$descricao, $valor, $data, $idTrabalhador, $idCategoria]
        );
    }

    public static function removerServico(
        int $idServico,
        int $idTrabalhador
    ): bool {
        $stmt = self::rodar(
            'DELETE FROM servico
             WHERE id_servico = ?
               AND id_trabalhador = ?',
            [$idServico, $idTrabalhador]
        );
        return $stmt->rowCount() > 0;
    }

    public static function meusDados(int $id): ?array
    {
        return self::rodar(
            'SELECT
                u.nome,
                u.telefone,
                c.empresa,
                t.area_atendimento,
                t.descricao_perfil
             FROM usuario u
             LEFT JOIN contratante c
                ON c.id_contratante = u.id_usuario
             LEFT JOIN trabalhador t
                ON t.id_trabalhador = u.id_usuario
             WHERE u.id_usuario = ?',
            [$id]
        )->fetch() ?: null;
    }

    public static function atualizarDados(
        int $id,
        string $tipo,
        array $dados
    ): void {
        $pdo = Conexao::obter();
        $pdo->beginTransaction();

        try {
            self::rodar(
                'UPDATE usuario
                 SET nome = ?, telefone = ?
                 WHERE id_usuario = ?',
                [$dados['nome'], $dados['telefone'], $id]
            );

            if ($tipo === 'trabalhador') {
                self::rodar(
                    'UPDATE trabalhador
                     SET area_atendimento = ?,
                         descricao_perfil = ?
                     WHERE id_trabalhador = ?',
                    [$dados['area'], $dados['descricao'], $id]
                );
            } else {
                self::rodar(
                    'UPDATE contratante
                     SET empresa = ?
                     WHERE id_contratante = ?',
                    [$dados['empresa'], $id]
                );
            }

            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    public static function relatorio(): array
    {
        return self::rodar(
            'SELECT
                (SELECT COUNT(*) FROM trabalhador) AS trabalhadores,
                (SELECT COUNT(*) FROM contratante) AS contratantes,
                (SELECT COUNT(*) FROM oportunidade WHERE publicada = 1) AS oportunidades,
                (SELECT COUNT(*) FROM servico) AS servicos,
                (SELECT COALESCE(SUM(valor), 0) FROM servico) AS valor_total,
                (SELECT COUNT(*) FROM categoria WHERE aprovada = 1) AS categorias'
        )->fetch() ?: [];
    }

    public static function categoriasPendentes(): array
    {
        return self::rodar(
            'SELECT id_categoria, nome
             FROM categoria
             WHERE aprovada = 0
             ORDER BY nome'
        )->fetchAll();
    }

    public static function aprovarCategoria(int $idCategoria): void
    {
        self::rodar(
            'UPDATE categoria
             SET aprovada = 1
             WHERE id_categoria = ?',
            [$idCategoria]
        );
    }

    /**
     * Sugestão de categoria (fica pendente até o administrador aprovar).
     * Retorna false se o nome já existe.
     */
    public static function sugerirCategoria(string $nome, string $descricao): bool
    {
        $stmt = self::rodar(
            'INSERT IGNORE INTO categoria (nome, descricao, aprovada)
             VALUES (?, ?, 0)',
            [$nome, $descricao !== '' ? $descricao : null]
        );
        return $stmt->rowCount() > 0;
    }

    public static function existeAdmin(): bool
    {
        return (int) self::rodar(
            'SELECT COUNT(*) FROM administrador'
        )->fetchColumn() > 0;
    }

    public static function criarAdmin(
        string $nome,
        string $email,
        string $senhaHash
    ): void {
        $pdo = Conexao::obter();
        $pdo->beginTransaction();

        try {
            self::rodar(
                'INSERT INTO usuario
                    (nome, email, telefone, senha)
                 VALUES (?, ?, ?, ?)',
                [$nome, $email, '', $senhaHash]
            );

            self::rodar(
                'INSERT INTO administrador (id_admin) VALUES (?)',
                [(int) $pdo->lastInsertId()]
            );

            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }
}
